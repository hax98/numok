const {createHash,timingSafeEqual}=require('node:crypto');
const hash=value=>createHash('sha256').update(value).digest('hex');
const safeEqual=(a,b)=>typeof a==='string'&&typeof b==='string'&&a.length>=32&&Buffer.byteLength(a)===Buffer.byteLength(b)&&timingSafeEqual(Buffer.from(a),Buffer.from(b));
function referral(data,codes){
 const r=data.referralData||{},legacy=r.via||r.ref||r.referral||r.affiliate||r.referral_via||r.referral_ref;
 const explicit=data.signupReferralCode;
 const requested=explicit&&['ref','affiliate'].includes(explicit.type)&&typeof explicit.code==='string'&&/^[A-Za-z0-9_-]{3,50}$/.test(explicit.code)?explicit.code:null;
 const code=legacy||requested;
 if(!codes.includes(code)||data.isTestAccount||data.isTest||data.testAccount)return null;
 // Do not attach unrelated optional analytics to a separately supplied signup code.
 return {code,contentToken:legacy?(r.utm_content||r.referral_utm_content||null):null};
}
function iso(value){const d=value?.toDate?value.toDate():value?.seconds?new Date(value.seconds*1000):new Date(value);return value&&Number.isFinite(d.getTime())?d.toISOString():null;}
function accountSnapshot(data,now=Date.now()){
 const g=data.complimentaryPublishingAccess,s=data.subscription||{};
 const start=iso(g?.startsAt),end=iso(g?.endsAt);
 const active=g?.plan==='influencer'&&g.grantedBy&&g.requestId&&!g.revokedAt&&start&&end&&Date.parse(start)<=now&&Date.parse(end)>now;
 return {influencerExpiresAt:active?end:null,grantState:active?'active':g?.revokedAt?'revoked':g?'expired_or_invalid':'none',billingPlan:s.plan||'free',billingStatus:s.status||'unknown'};
}
async function mapConcurrent(items,limit,work){
 const results=new Array(items.length);let next=0;
 await Promise.all(Array.from({length:Math.min(limit,items.length)},async()=>{for(;;){const index=next++;if(index>=items.length)return;results[index]=await work(items[index],index);}}));
 return results;
}
module.exports={hash,safeEqual,referral,iso,accountSnapshot,mapConcurrent};
