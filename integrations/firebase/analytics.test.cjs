const {test}=require('node:test');const assert=require('node:assert/strict');
const {collectAnalytics,validateContents,canonical,number}=require('./analytics');
const post={token:'a'.repeat(24),platform:'tiktok',kind:'video',url:'https://www.tiktok.com/@creator/video/123456789'};
const connection={id:'own',platform:'TikTok',accessToken:'TEST_TOKEN',userId:'fixture'};
const response=body=>({ok:true,status:200,json:async()=>body});
test('TikTok endpoint verifies owned video and only exports metrics',async()=>{
 let req;const r=await collectAnalytics({contents:[post],connections:[connection],fetchImpl:async(url,options)=>{req={url,options};return response({data:{videos:[{id:'123456789',view_count:10,like_count:0}]}});}});
 assert.equal(r.posts[0].metrics.views,10);assert.equal(r.posts[0].metrics.likes,0);assert.equal(r.posts[0].metrics.shares,null);assert.equal(r.posts[0].status,'available');assert.equal(req.options.redirect,'error');assert(!JSON.stringify(r).includes('TEST_TOKEN'));
});
test('Another creator video is not accepted',async()=>{const r=await collectAnalytics({contents:[post],connections:[connection],fetchImpl:async()=>response({data:{videos:[]}})});assert.equal(r.posts[0].status,'not_owned_or_missing');assert.equal(r.posts[0].metrics,null);});
test('Missing scope and expired token require reconnection',async()=>{const r=await collectAnalytics({contents:[post],connections:[connection],fetchImpl:async()=>({ok:false,status:403,json:async()=>({error:{code:'scope_not_authorized'}})})});assert.equal(r.posts[0].status,'reconnect_required');assert(r.connections[0].authorizationRequired);});
test('Provider network failure remains unavailable, not zero',async()=>{const r=await collectAnalytics({contents:[post],connections:[connection],fetchImpl:async()=>{throw new Error('network');}});assert.equal(r.posts[0].status,'retry');assert.equal(r.posts[0].metrics,null);});
test('Missing account does not fetch',async()=>{const r=await collectAnalytics({contents:[post],connections:[],fetchImpl:()=>{throw new Error('unexpected');}});assert.equal(r.posts[0].status,'reconnect_required');});
test('Fresh exact cache avoids provider calls',async()=>{const now=Date.now();const r=await collectAnalytics({contents:[post],connections:[connection],cached:[{publishedUrl:post.url,metrics:{views:12},insightsStatus:'available',fetchedAtMs:now}],now,fetchImpl:()=>{throw new Error('unexpected');}});assert.equal(r.posts[0].metrics.views,12);});
test('Instagram shortcode case is preserved',()=>{assert.notEqual(canonical('https://instagram.com/reel/AbC/'),canonical('https://instagram.com/reel/abc/'));});
test('Native Story explicitly unsupported',async()=>{const r=await collectAnalytics({contents:[{...post,platform:'instagram',kind:'story',url:'https://www.instagram.com/stories/me/123/'}],connections:[{id:'ig',platform:'Instagram',openId:'123',accessToken:'TEST'}],fetchImpl:()=>{throw new Error('unexpected');}});assert.equal(r.posts[0].status,'unsupported');});
test('Instagram owned media resolution, fixed hosts, partial metrics',async()=>{
 const requests=[];const p={...post,platform:'instagram',url:'https://www.instagram.com/reel/AbC/'};
 const r=await collectAnalytics({contents:[p],connections:[{id:'ig',platform:'Instagram',openId:'123',accessToken:'TEST'}],fetchImpl:async url=>{requests.push(url);return response(url.includes('/media?')?{data:[{id:'456',permalink:p.url,like_count:2,comments_count:1}]}:{data:[{values:[{value:50}]}]});}});
 assert.equal(r.posts[0].metrics.views,50);assert.equal(r.posts[0].metrics.likes,2);assert(requests.every(u=>u.startsWith('https://graph.instagram.com/v23.0/')));
});
test('Reject SSRF and malformed content batches',()=>{
 for(const url of ['https://evil.example/post','https://instagram.com.evil.example/post','https://user:pass@www.tiktok.com/post','http://www.tiktok.com/post','https://www.tiktok.com:4433/post'])assert.throws(()=>validateContents([{...post,url}]));
 assert.throws(()=>validateContents(Array(21).fill(post)));assert.throws(()=>validateContents([{...post,token:'bad'}]));
});
test('No fabricated numbers',()=>{assert.equal(number(undefined),null);assert.equal(number(-1),null);assert.equal(number('abc'),null);assert.equal(number(0),0);});
