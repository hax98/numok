import {initializeApp} from 'https://www.gstatic.com/firebasejs/11.10.0/firebase-app.js';
import {getAuth,GoogleAuthProvider,signInWithPopup,browserSessionPersistence,setPersistence} from 'https://www.gstatic.com/firebasejs/11.10.0/firebase-auth.js';
const app=initializeApp(JSON.parse(document.querySelector('#firebase-public-config').textContent),'partner-admin'),auth=getAuth(app);
const status=document.querySelector('#admin-grant-status'),csrf=document.querySelector('#admin-access-controls').dataset.csrf;
for(const button of document.querySelectorAll('[data-grant-partner]'))button.addEventListener('click',async()=>{
 const id=button.dataset.grantPartner;
 if(!document.querySelector('#grant-consent-'+id)?.checked){status.textContent='Confirm the creator and annual offer before activation.';return;}
 button.disabled=true;status.textContent='Sign in with your Repostit administrator Google account.';
 try{
  await setPersistence(auth,browserSessionPersistence);const provider=new GoogleAuthProvider();provider.setCustomParameters({prompt:'select_account'});
  const {user}=await signInWithPopup(auth,provider),idToken=await user.getIdToken(true);
  const response=await fetch('/admin/creators/grant-access',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-CSRF-Token':csrf},body:JSON.stringify({partnerId:Number(id),idToken})});
  const result=await response.json();if(!response.ok||!result.ok)throw Error(result.error||'Annual access was not verified.');
  status.textContent='Annual Influencer access verified until '+result.endsAt+'.';location.reload();
 }catch(error){status.textContent=error.message;}finally{button.disabled=false;}
});
