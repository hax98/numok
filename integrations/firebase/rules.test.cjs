const test=require('node:test'),assert=require('node:assert/strict');
const {safeEqual,referral,accountSnapshot,hash,mapConcurrent}=require('./rules');
test('bridge authentication fails closed',()=>{assert.equal(safeEqual('', ''),false);assert.equal(safeEqual('a'.repeat(40),'a'.repeat(40)),true);assert.equal(safeEqual('b'.repeat(40),'a'.repeat(40)),false);});
test('first-touch creator code takes precedence',()=>{assert.equal(referral({referralData:{via:'alice',ref:'bob'}},['bob']),null);assert.equal(referral({referralData:{via:'alice',utm_content:'clip1'}},['alice']).contentToken,'clip1');});
test('test accounts never become creator referrals',()=>assert.equal(referral({isTestAccount:true,referralData:{via:'alice'}},['alice']),null));
test('free access is not a paid subscription and expires',()=>{const now=Date.now(),data={complimentaryPublishingAccess:{plan:'influencer',startsAt:new Date(now-1000),endsAt:new Date(now+1000),grantedBy:'admin',requestId:'approved'},subscription:{plan:'free'}};assert.equal(accountSnapshot(data,now).grantState,'active');assert.equal(accountSnapshot(data,now).billingPlan,'free');assert.equal(accountSnapshot(data,now+2000).influencerExpiresAt,null);});
test('privacy hash is stable and opaque',()=>{assert.equal(hash('uid').length,64);assert.equal(hash('uid'),hash('uid'));});
test('Unicode secret mismatch fails closed without throwing',()=>assert.equal(safeEqual('é'.repeat(40),'a'.repeat(40)),false));
test('billing sync concurrency is bounded',async()=>{let active=0,max=0;const result=await mapConcurrent([1,2,3,4,5],2,async v=>{active++;max=Math.max(max,active);await Promise.resolve();active--;return v*2;});assert.ok(max<=2);assert.deepEqual(result,[2,4,6,8,10]);});
