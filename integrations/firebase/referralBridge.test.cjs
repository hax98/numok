const test=require('node:test'),assert=require('node:assert/strict'),fs=require('node:fs'),vm=require('node:vm');
function bridgeHarness(rows,{overflow=false}={}){
 const queries=[];let authReads=0,stripeReads=0;
 const db={collection(name){
  return {where(path,operator,codes){
   assert.equal(operator,name==='users'?'in':'==');
   return {limit(bound){return {async get(){
    if(name==='publishAttempts')return {docs:[],size:0};
    queries.push({path,codes,bound});
    const matches=rows.filter(r=>codes.includes(path.split('.').reduce((v,k)=>v?.[k],r.data)));
    return {docs:matches.map(r=>({id:r.id,data:()=>r.data})),size:overflow?1001:matches.length};
   }}}};
  }};
 }};
 const auth={async getUser(uid){authReads++;const r=rows.find(r=>r.id===uid);return {uid,email:r.data.email,disabled:r.data.disabled||false,metadata:{creationTime:'2026-10-10T10:00:00Z'}};}};
 const modules={
  'firebase-functions/v2/https':{onRequest:(_,handler)=>handler},
  'firebase-functions/v2/scheduler':{onSchedule:()=>()=>{}},
  'firebase-functions/params':{defineSecret:()=>({value:()=> 'a'.repeat(40)})},
  'firebase-admin/app':{initializeApp:()=>{}},
  'firebase-admin/auth':{getAuth:()=>auth},
  'firebase-admin/firestore':{getFirestore:()=>db},
  stripe:function(){stripeReads++;throw Error('Unexpected billing request for free fixture');},
  './rules':require('./rules'),'./analytics':{},'./portalSync':{},'./environment':{}
 };
 const context={exports:{},require:name=>{if(!Object.hasOwn(modules,name))throw Error('Unexpected dependency '+name);return modules[name];},process:{env:{}},console:{error:()=>{},log:()=>{}},Date};
 vm.runInNewContext(fs.readFileSync(require.resolve('./index.js'),'utf8'),context,{timeout:1000});
 return {queries,counts:()=>({authReads,stripeReads}),async invoke(body,secret='a'.repeat(40)){
  let status=200,payload;const response={set:()=>response,status:n=>{status=n;return response;},json:p=>{payload=p;return response;}};
  await context.exports.partnerPortalBridge({method:'POST',body,get:()=>secret},response);
  return {status,payload};
 }};
}
const user=(id,data)=>({id,data:{email:id+'@example.test',...data}});
test('bridge discovers explicit-only signup without optional analytics and deduplicates overlapping records',async()=>{
 const h=bridgeHarness([user('explicit',{signupReferralCode:{type:'ref',code:'alice'}}),user('both',{referralData:{via:'alice'},signupReferralCode:{type:'ref',code:'alice'}})]);
 const r=await h.invoke({action:'referrals',codes:['alice'],excludeEmail:'creator@example.test'});
 assert.equal(r.status,200);assert.equal(r.payload.referrals.length,2);assert.equal(new Set(r.payload.referrals.map(x=>x.customerHash)).size,2);assert.equal(r.payload.payments.length,0);
 assert.equal(h.counts().authReads,2);assert.equal(h.counts().stripeReads,0);
 assert.ok(h.queries.every(q=>q.bound===1001&&q.codes.length===1&&q.codes[0]==='alice'));
 assert.ok(h.queries.some(q=>q.path==='signupReferralCode.code'));
});
test('bridge keeps self, disabled, test and conflicting first-touch users outside the cohort',async()=>{
 const explicit={type:'ref',code:'alice'};
 const h=bridgeHarness([user('self',{signupReferralCode:explicit}),user('disabled',{signupReferralCode:explicit,disabled:true}),user('test',{signupReferralCode:explicit,isTest:true}),user('conflict',{signupReferralCode:explicit,referralData:{via:'other'}})]);
 const r=await h.invoke({action:'referrals',codes:['alice'],excludeUid:'self'});
 assert.equal(r.status,200);assert.equal(r.payload.referrals.length,0);assert.equal(r.payload.payments.length,0);
});
test('explicit lookup retains bounded fail-closed behavior and bridge authentication',async()=>{
 const h=bridgeHarness([],{overflow:true});
 const denied=await h.invoke({action:'referrals',codes:['alice']},'bad');assert.equal(denied.status,401);assert.equal(h.queries.length,0);
 const r=await h.invoke({action:'referrals',codes:['alice']});assert.equal(r.status,413);assert.equal(h.counts().authReads,0);assert.equal(h.counts().stripeReads,0);
});
