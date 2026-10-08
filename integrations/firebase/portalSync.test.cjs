const {test}=require('node:test');
const assert=require('node:assert/strict');
const {createServer}=require('node:http');
const {once}=require('node:events');
const {syncPortal}=require('./portalSync');
const key='disposable-local-fixture-key-only';
const ok=body=>({ok:true,status:200,json:async()=>body});
const options={key,waitImpl:async()=>{},logger:{warn(){}}};
const failure=code=>Object.assign(new TypeError('fetch failed '+key),{cause:{code}});
test('complete sync exports only verified counters',async()=>{
 let request;
 const result=await syncPortal({...options,fetchImpl:async(url,opts)=>{request={url,opts};return ok({synced:6,failed:0,analyticsFailed:0,secret:key});}});
 assert.deepEqual(result,{synced:6,failed:0,analyticsFailed:0});
 assert.equal(request.url,'https://partners.repostit.io/internal/creator-sync');
 assert.equal(request.opts.redirect,'error');assert.equal(request.opts.body,'{}');
});
for(const code of ['EAI_AGAIN','ENOTFOUND','ECONNREFUSED','UND_ERR_CONNECT_TIMEOUT'])test('bounded connection retry: '+code,async()=>{
 let calls=0;const delays=[];
 const result=await syncPortal({...options,waitImpl:async ms=>delays.push(ms),fetchImpl:async()=>{if(++calls<3)throw failure(code);return ok({synced:6,failed:0});}});
 assert.equal(calls,3);assert.deepEqual(delays,[500,1000]);assert.equal(result.synced,6);
});
for(const code of ['UND_ERR_SOCKET','ECONNRESET','UND_ERR_HEADERS_TIMEOUT','CERT_HAS_EXPIRED'])test('no replay of ambiguous/security failure: '+code,async()=>{
 let calls=0;await assert.rejects(()=>syncPortal({...options,fetchImpl:async()=>{calls++;throw failure(code);}}),/NETWORK_ERROR/);assert.equal(calls,1);
});
test('terminal transport failure is bounded and redacts original message',async()=>{
 let calls=0;const logs=[];
 await assert.rejects(()=>syncPortal({...options,logger:{warn:(...args)=>logs.push(args)},fetchImpl:async()=>{calls++;throw failure('EAI_AGAIN');}}),error=>error.message.includes('EAI_AGAIN')&&!error.message.includes(key)&&!error.cause);
 assert.equal(calls,3);assert.equal(logs.at(-1)[1].retry,false);assert(!JSON.stringify(logs).includes(key));
});
for(const status of [401,403,429,503])test('HTTP '+status+' does not replay',async()=>{
 let calls=0;await assert.rejects(()=>syncPortal({...options,fetchImpl:async()=>{calls++;return {ok:false,status,json:async()=>{throw Error(key);}};}}),new RegExp('HTTP '+status));assert.equal(calls,1);
});
for(const body of [null,{synced:6},{synced:6,failed:-1},{synced:6,failed:0,analyticsFailed:'0'},{synced:6,failed:1},{synced:6,failed:0,analyticsFailed:1}])test('invalid/partial sync is never success: '+JSON.stringify(body),async()=>{
 let calls=0;await assert.rejects(()=>syncPortal({...options,fetchImpl:async()=>{calls++;return ok(body);}}),/invalid counts|incomplete/);assert.equal(calls,1);
});
test('invalid JSON stays a safe terminal error',async()=>{
 await assert.rejects(()=>syncPortal({...options,fetchImpl:async()=>({ok:true,status:200,json:async()=>{throw Error(key);}})}),error=>error.message==='Partner portal sync returned invalid JSON');
});
test('total deadline prevents retry delay extending the invocation',async()=>{
 let time=0,calls=0;
 await assert.rejects(()=>syncPortal({...options,now:()=>time,timeoutMs:1000,fetchImpl:async()=>{calls++;time=750;throw failure('EAI_AGAIN');}}),/EAI_AGAIN/);assert.equal(calls,1);
});
test('missing key does not attempt a request',async()=>{
 await assert.rejects(()=>syncPortal({...options,key:'',fetchImpl:()=>{throw Error('Unexpected');}}),/key unavailable/);
});
test('actual loopback HTTP request succeeds after injected connection failure; redirect never forwards key',async()=>{
 let requests=0;let redirected=0;
 const server=createServer((req,res)=>{
  if(req.url==='/forwarded'){redirected++;res.end('{}');return;}
  requests++;assert.equal(req.headers['x-partner-bridge-key'],key);
  if(req.url==='/redirect'){res.writeHead(302,{Location:'/forwarded'});res.end();return;}
  res.setHeader('Content-Type','application/json');res.end(JSON.stringify({synced:6,failed:0}));
 });
 server.listen(0,'127.0.0.1');await once(server,'listening');
 const url='http://127.0.0.1:'+server.address().port;
 try {
  let calls=0;
  const result=await syncPortal({...options,fetchImpl:async(_url,opts)=>{if(++calls===1)throw failure('EAI_AGAIN');return fetch(url+'/sync',opts);}});
  assert.equal(result.synced,6);assert.equal(calls,2);assert.equal(requests,1);
  await assert.rejects(()=>syncPortal({...options,fetchImpl:(_url,opts)=>fetch(url+'/redirect',opts)}),/NETWORK_ERROR/);
  assert.equal(redirected,0);assert.equal(requests,2);
 } finally { server.closeAllConnections();await new Promise(resolve=>server.close(resolve)); }
});
