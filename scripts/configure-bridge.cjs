// Configures only the new portal bridge. Secrets travel over stdin, never logs or source.
const cp=require('node:child_process'),crypto=require('node:crypto');
const python='C:/Program Files (x86)/Google/Cloud SDK/google-cloud-sdk/platform/bundledpython/python.exe';
const gcloud='C:/Program Files (x86)/Google/Cloud SDK/google-cloud-sdk/lib/gcloud.py';
const cli='C:/Users/habib/AppData/Local/npm-cache/_npx/79fa66f96c8fdacf/node_modules/@railway/cli/bin/railway.exe';
const project='repostit-91b0e',secretName='PARTNER_PORTAL_BRIDGE_KEY';
const run=(args,input)=>cp.execFileSync(python,[gcloud,...args,'--project='+project],{input,encoding:'utf8',timeout:120000,stdio:['pipe','pipe','pipe']});
async function main(){
 let key;
 try{key=run(['secrets','versions','access','latest','--secret='+secretName]).trim();}
 catch{
  run(['secrets','create',secretName,'--replication-policy=automatic','--quiet']);
  key=crypto.randomBytes(32).toString('hex');run(['secrets','versions','add',secretName,'--data-file=-'],key);
 }
 if(!/^[a-f0-9]{64}$/.test(key))throw Error('Unexpected bridge key format');
 cp.execFileSync(cli,['variable','set','PARTNER_PORTAL_BRIDGE_KEY','--stdin','--skip-deploys','--project','dc28001a-13a7-4a2f-9f54-247cb05ed4fb','--environment','a8a321f2-24ac-459d-bd18-65a8ebf40d31','--service','86583c49-8e1d-4ef4-a565-ab764ae8a576'],{input:key,encoding:'utf8',timeout:120000,stdio:['pipe','pipe','pipe']});
 const token=run(['auth','print-access-token']).trim(),url='https://identitytoolkit.googleapis.com/admin/v2/projects/'+project+'/config';
 const headers={Authorization:'Bearer '+token,'Content-Type':'application/json','X-Goog-User-Project':project};
 const current=await fetch(url,{headers});if(!current.ok)throw Error('Auth authorized-domain read failed: '+current.status);
 const config=await current.json();
 if(!config.authorizedDomains?.includes('partners.repostit.io')){
  const change=await fetch(url+'?updateMask=authorizedDomains',{method:'PATCH',headers,body:JSON.stringify({authorizedDomains:[...(config.authorizedDomains||[]),'partners.repostit.io']})});
  if(!change.ok)throw Error('Auth authorized-domain update failed: '+change.status);
 }
 console.log('Bridge key configured in Secret Manager and Railway. Existing Firebase domains preserved, partners.repostit.io authorized. No key printed.');
}
main().catch(e=>{console.error(e.message);process.exitCode=1;});
