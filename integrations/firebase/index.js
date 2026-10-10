const {onRequest}=require('firebase-functions/v2/https');
const {onSchedule}=require('firebase-functions/v2/scheduler');
const {defineSecret}=require('firebase-functions/params');
const {initializeApp}=require('firebase-admin/app');
const {getAuth}=require('firebase-admin/auth');
const {getFirestore}=require('firebase-admin/firestore');
const Stripe=require('stripe');
const {hash,safeEqual,referral,iso,accountSnapshot,mapConcurrent}=require('./rules');
const {validateContents,collectAnalytics}=require('./analytics');
const {syncPortal}=require('./portalSync');
const {portalUrl,stripeKey,STAGING_PROJECT}=require('./environment');
initializeApp();
const portalSecret=defineSecret(process.env.GCLOUD_PROJECT===STAGING_PROJECT?'PARTNER_STAGING_PORTAL_BRIDGE_KEY':'PARTNER_PORTAL_BRIDGE_KEY'),stripeSecret=defineSecret(process.env.GCLOUD_PROJECT===STAGING_PROJECT?'PARTNER_PORTAL_TEST_STRIPE_KEY':'STRIPE_LIVE_SECRET');
exports.partnerPortalBridge=onRequest({region:'us-central1',timeoutSeconds:300,memory:'512MiB',maxInstances:2,concurrency:10,secrets:[portalSecret,stripeSecret]},async(req,res)=>{
 res.set('Cache-Control','no-store');
 if(req.method!=='POST')return res.status(405).json({error:'POST required'});
 if(!safeEqual(req.get('X-Partner-Bridge-Key'),portalSecret.value()))return res.status(401).json({error:'Unauthorized'});
 const body=req.body||{},db=getFirestore();
 try{
  if(body.action==='account'){
   if(typeof body.idToken!=='string'||body.idToken.length>10000)return res.status(400).json({error:'Invalid identity'});
   const decoded=await getAuth().verifyIdToken(body.idToken,true),user=await getAuth().getUser(decoded.uid);
   if(user.disabled||!user.emailVerified)return res.status(403).json({error:'Verify account email'});
   const profile=await db.collection('users').doc(decoded.uid).get();
   if(!profile.exists)return res.status(404).json({error:'Repostit profile not found'});
   return res.json({account:accountSnapshot(profile.data()),identity:{uid:user.uid,email:user.email}});
  }
  if(body.action==='analytics'){
   // Only the secret-authenticated portal may request a previously consented linked UID.
   if(typeof body.uid!=='string'||!/^[-A-Za-z0-9_]{1,128}$/.test(body.uid)||typeof body.email!=='string')return res.status(400).json({error:'Invalid linked account'});
   validateContents(body.contents);
   const user=await getAuth().getUser(body.uid);
   if(user.disabled||!user.emailVerified||user.email?.toLowerCase()!==body.email.toLowerCase())return res.status(403).json({error:'Linked identity no longer valid'});
   const profile=await db.collection('users').doc(body.uid).get();if(!profile.exists)return res.status(404).json({error:'Repostit profile not found'});
   const connectionDocs=await db.collection('connections').where('userId','==',body.uid).limit(101).get();
   if(connectionDocs.size>100)return res.status(413).json({error:'Connection cohort requires paginated sync'});
   const cacheDocs=await db.collection('users').doc(body.uid).collection('creatorPerformance').limit(1001).get();
   if(cacheDocs.size>1000)return res.status(413).json({error:'Analytics history requires paginated sync'});
   const result=await collectAnalytics({contents:body.contents,connections:connectionDocs.docs.map(d=>({id:d.id,...d.data()})),cached:cacheDocs.docs.map(d=>d.data())});
   return res.json({...result,account:accountSnapshot(profile.data()),syncedAt:new Date().toISOString()});
  }
  if(body.action!=='referrals'||!Array.isArray(body.codes)||body.codes.length<1||body.codes.length>30||body.codes.some(c=>typeof c!=='string'||!/^[A-Za-z0-9_-]{3,50}$/.test(c)))return res.status(400).json({error:'Invalid referral request'});
  const codes=[...new Set(body.codes)],users=new Map();
  // Queries are exact-code constrained, never return a global user export to the portal.
  for(const path of ['via','ref','referral','affiliate','referral_via','referral_ref'].map(field=>'referralData.'+field).concat('signupReferralCode.code')){
   const snap=await db.collection('users').where(path,'in',codes).limit(1001).get();
   if(snap.size>1000)return res.status(413).json({error:'Referral cohort requires paginated sync'});
   for(const d of snap.docs)users.set(d.id,d.data());
  }
  let stripe;const referrals=[],payments=[];
  await mapConcurrent([...users],4,async([uid,data])=>{
   const attr=referral(data,codes);if(!attr||uid===body.excludeUid||String(data.email||'').toLowerCase()===String(body.excludeEmail||'').toLowerCase())return;
   const auth=await getAuth().getUser(uid).catch(()=>null);
   if(!auth||auth.disabled||auth.email?.toLowerCase()===String(body.excludeEmail||'').toLowerCase())return;
   const signup=iso(data.createdAt)||iso(data.created)||iso(auth.metadata.creationTime);if(!signup)return;
   const customer=data.subscription?.customerId||data.stripeCustomerId||null;
   const publishes=await db.collection('publishAttempts').where('userId','==',uid).limit(500).get();
   const real=publishes.docs.map(d=>d.data()).filter(p=>p.status==='confirmed'&&!p.qaFixture&&!p.isTest);
   const dates=real.map(p=>iso(p.confirmedAt)||iso(p.createdAt)).filter(Boolean).sort();
   referrals.push({...attr,customerHash:hash(uid),stripeCustomerId:customer,signedUpAt:signup,firstPublishAt:dates[0]||null});
   if(!customer||!/^cus_[A-Za-z0-9]+$/.test(customer))return;
   stripe ||= new Stripe(stripeKey(process.env.GCLOUD_PROJECT,stripeSecret.value()));
   let after,scanned=0;
   do{
    const page=await stripe.invoices.list({customer,status:'paid',limit:100,...(after?{starting_after:after}:{})});
    for(const invoice of page.data){
     if(!invoice.livemode||invoice.amount_paid<=0)continue;
     const subscription=typeof invoice.subscription==='string'?invoice.subscription:invoice.subscription?.id||invoice.parent?.subscription_details?.subscription;
     if(!subscription)continue; // Bonuses count subscribers, not one-off buyers.
     const metadata=invoice.subscription_details?.metadata||invoice.parent?.subscription_details?.metadata||invoice.metadata||{};
     const paidCode=metadata.via||metadata.referral_via||metadata.numok_tracking_code||attr.code;
     if(paidCode!==attr.code)continue;
     const intent=typeof invoice.payment_intent==='string'?invoice.payment_intent:invoice.payment_intent?.id;
     let refunded=0,disputed=false;
     if(intent){
      const pi=await stripe.paymentIntents.retrieve(intent,{expand:['latest_charge']});
      const charge=pi.latest_charge;if(charge&&typeof charge==='object'){refunded=charge.amount_refunded||0;disputed=charge.disputed===true;}
     }
     payments.push({code:attr.code,invoiceId:invoice.id,customerKey:customer,contentToken:metadata.utm_content||metadata.referral_utm_content||attr.contentToken,
      subscriptionId:subscription,paymentIntent:intent||null,amountCents:Math.max(0,invoice.amount_paid-refunded),originalCents:invoice.amount_paid,currency:invoice.currency,disputed,
      paidAt:iso(new Date((invoice.status_transitions?.paid_at||invoice.created)*1000))});
    }
    scanned+=page.data.length;if(scanned>=1000&&page.has_more)throw new Error('Invoice history requires paginated sync');
    after=page.has_more?page.data.at(-1)?.id:null;
   }while(after);
  });
  return res.json({referrals,payments,syncedAt:new Date().toISOString()});
 }catch(error){
  console.error('Partner bridge failed',{code:error.code||'unknown',kind:error.name});
  return res.status(503).json({error:'Repostit synchronization temporarily unavailable'});
 }
});

exports.syncPartnerPortal=onSchedule({schedule:'every 60 minutes',timeZone:'Europe/Rome',region:'us-central1',timeoutSeconds:540,memory:'256MiB',maxInstances:1,secrets:[portalSecret]},async()=>{
 const result=await syncPortal({key:portalSecret.value(),url:portalUrl(process.env.GCLOUD_PROJECT,process.env.PARTNER_PORTAL_SYNC_URL)});
 console.log('Partner portal verified sync',result);
});
