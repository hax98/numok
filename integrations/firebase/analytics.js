// Read-only provider requests. Credentials stay in Firebase and never leave this module.
const canonical=url=>{try{const u=new URL(url);return u.hostname.replace(/^www\./,'').toLowerCase()+u.pathname.replace(/\/$/,'');}catch{return null;}};
const number=v=>v!==null&&v!==undefined&&v!==''&&Number.isFinite(Number(v))&&Number(v)>=0?Number(v):null;
function validateContents(contents){
 if(!Array.isArray(contents)||contents.length>20)throw new Error('Invalid content batch');
 const hosts={instagram:['instagram.com','www.instagram.com'],tiktok:['tiktok.com','www.tiktok.com'],youtube:['youtube.com','www.youtube.com','youtu.be'],facebook:['facebook.com','www.facebook.com','m.facebook.com'],linkedin:['linkedin.com','www.linkedin.com'],x:['x.com','www.x.com','twitter.com','www.twitter.com'],threads:['threads.net','www.threads.net','threads.com','www.threads.com'],pinterest:['pinterest.com','www.pinterest.com','pin.it']};
 for(const c of contents){
  const u=new URL(c.url);
  if(!/^[a-f0-9]{24}$/.test(c.token)||c.url.length>500||u.protocol!=='https:'||u.username||u.password||u.port||!hosts[c.platform]?.includes(u.hostname))throw new Error('Invalid content URL');
 }
 return contents;
}
async function json(fetchImpl,url,options={}){
 const response=await fetchImpl(url,{...options,redirect:'error',signal:AbortSignal.timeout(12000)});
 const body=await response.json();const code=String(body.error?.code||'');
 if(!response.ok||(code&&code!=='ok'))throw Object.assign(new Error('Provider rejected analytics request'),{authorization:[401,403].includes(response.status)||['10','190','200','access_token_invalid','scope_not_authorized'].includes(code)});
 return body;
}
async function tiktok(c,token,fetchImpl){
 const id=new URL(c.url).pathname.match(/\/video\/(\d+)/)?.[1];
 if(!id)return {status:'unsupported',metrics:null};
 const p=await json(fetchImpl,'https://open.tiktokapis.com/v2/video/query/?fields=id,view_count,like_count,comment_count,share_count',{
  method:'POST',headers:{Authorization:'Bearer '+token,'Content-Type':'application/json'},body:JSON.stringify({filters:{video_ids:[id]}})});
 // Official query endpoint only returns the authorized user's own videos.
 const v=p.data?.videos?.find(v=>v.id===id);
 if(!v)return {status:'not_owned_or_missing',metrics:null};
 return {status:'available',metrics:{views:number(v.view_count),likes:number(v.like_count),comments:number(v.comment_count),shares:number(v.share_count)}};
}
async function instagram(c,connection,fetchImpl,mediaCache){
 if(c.kind==='story')return {status:'unsupported',metrics:null}; // Expiring native Story URLs cannot reliably resolve owned media.
 const host=connection.instagramApiHost==='graph.facebook.com'?'graph.facebook.com':'graph.instagram.com';
 const version=/^v\d+\.\d+$/.test(process.env.META_GRAPH_API_VERSION||'')?process.env.META_GRAPH_API_VERSION:'v23.0';
 const root=`https://${host}/${version}/`;const token=connection.accessToken;
 const account=String(connection.openId||connection.accountId||'');
 if(!/^\d+$/.test(account))return {status:'reconnect_required',metrics:null};
 const headers={Authorization:'Bearer '+token};
 if(!mediaCache.has(connection.id))mediaCache.set(connection.id,(async()=>{
  let cursor=null;const media=[];
  for(let page=0;page<4;page++){
   const u=new URL(root+account+'/media');u.searchParams.set('fields','id,permalink,like_count,comments_count');u.searchParams.set('limit','50');if(cursor)u.searchParams.set('after',cursor);
   const p=await json(fetchImpl,u.toString(),{headers});media.push(...p.data||[]);cursor=p.paging?.cursors?.after;
   if(!p.paging?.next||!cursor)break; // Never follow arbitrary provider-supplied URLs.
  }return media;
 })());
 const media=(await mediaCache.get(connection.id)).find(m=>canonical(m.permalink)===canonical(c.url));
 if(!media||!/^\d+$/.test(media.id))return {status:'not_owned_or_missing',metrics:null};
 const metrics={views:null,reach:null,likes:number(media.like_count),comments:number(media.comments_count),shares:null,saved:null};
 let denied=false;
 for(const metric of ['views','reach','shares','saved']){
  try{const u=new URL(root+media.id+'/insights');u.searchParams.set('metric',metric);const p=await json(fetchImpl,u.toString(),{headers});metrics[metric]=number(p.data?.[0]?.values?.[0]?.value??p.data?.[0]?.total_value?.value);}
  catch(e){if(e.authorization)denied=true;}
 }
 return {status:denied?'authorization_required':'available',metrics};
}
async function collectAnalytics({contents,connections,cached=[],fetchImpl=fetch,now=Date.now()}){
 validateContents(contents);const mediaCache=new Map(),posts=[],started=Date.now();
 const statuses=new Map();
 for(const c of contents){
  const base={token:c.token,publishedUrl:c.url,platform:c.platform,fetchedAtMs:now};
  if(Date.now()-started>60000){posts.push({...base,status:'retry',metrics:null});continue;}
  const cache=cached.find(p=>!p.qaFixture&&!p.isTest&&canonical(p.publishedUrl)===canonical(c.url)&&p.insightsStatus==='available'&&now>=Number(p.fetchedAtMs)&&now-Number(p.fetchedAtMs)<21600000);
  if(cache){posts.push({...base,metrics:cache.metrics,status:'available',fetchedAtMs:cache.fetchedAtMs});continue;}
  const matches=connections.filter(x=>String(x.platform).toLowerCase()===c.platform);
  if(!matches.length){posts.push({...base,status:'reconnect_required',metrics:null});continue;}
  if(!['instagram','tiktok'].includes(c.platform)){
   const old=cached.find(p=>!p.qaFixture&&canonical(p.publishedUrl)===canonical(c.url));
   posts.push({...base,status:old?.insightsStatus||'unsupported',metrics:old?.metrics||null,fetchedAtMs:old?.fetchedAtMs||null});continue;
  }
  let result={status:'not_owned_or_missing',metrics:null};
  for(const connection of matches){
   if(!connection.accessToken||['needs_reconnection','invalid_token','expired'].includes(connection.status))result={status:'reconnect_required',metrics:null};
   else try{result=c.platform==='tiktok'?await tiktok(c,connection.accessToken,fetchImpl):await instagram(c,connection,fetchImpl,mediaCache);}
   catch(e){result={status:e.authorization?'reconnect_required':'retry',metrics:null};}
   statuses.set(connection.id,result.status);
   if(result.status==='available'||result.status==='authorization_required')break;
  }
  posts.push({...base,...result});
 }
 return {posts,connections:connections.map(c=>({id:c.id,platform:String(c.platform).toLowerCase(),name:String(c.displayName||c.username||c.connectionName||'Connected account').slice(0,100),
  insightsStatus:statuses.get(c.id)||c.capabilities?.creatorAnalytics?.status||'not_checked',authorizationRequired:['reconnect_required','authorization_required'].includes(statuses.get(c.id))}))};
}
module.exports={canonical,number,validateContents,collectAnalytics};
