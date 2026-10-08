const {test}=require('node:test');
const assert=require('node:assert/strict');
const {portalUrl,stripeKey}=require('./environment');
const production='https://partners.repostit.io/internal/creator-sync';
const stage='https://partners-staging-example.up.railway.app/internal/creator-sync';
test('production endpoint remains unchanged',()=>{
 assert.equal(portalUrl('repostit-91b0e'),production);
 assert.equal(portalUrl('repostit-91b0e',production),production);
 assert.throws(()=>portalUrl('repostit-91b0e',stage),/mismatch/);
});
test('staging endpoint cannot fall back to production',()=>{
 assert.equal(portalUrl('repostit-dev',stage),stage);
 for(const value of [undefined,production,'http://partners-staging-example.up.railway.app/internal/creator-sync',
  stage+'?secret=oops',stage+'#fragment',stage.replace('https://','https://user:pass@'),
  stage.replace('/internal/creator-sync','/other'),stage.replace('.up.railway.app','.evil.test')]) {
  assert.throws(()=>portalUrl('repostit-dev',value));
 }
 assert.throws(()=>portalUrl('unknown',production));
});
test('staging cannot use a live Stripe credential',()=>{
 assert.equal(stripeKey('repostit-dev','sk_test_fixture'),'sk_test_fixture');
 assert.equal(stripeKey('repostit-91b0e','sk_live_fixture'),'sk_live_fixture');
 assert.throws(()=>stripeKey('repostit-dev','sk_live_fixture'));
 assert.equal(stripeKey('repostit-91b0e','rk_live_existing'),'rk_live_existing');
 assert.throws(()=>stripeKey('other','sk_live_fixture'));
});
