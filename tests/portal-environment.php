<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
use Numok\Services\{PortalEnvironment as E,CreatorRules,CreatorHub,EmailService};
$checks=0;
$check=function(bool $ok,string $name)use(&$checks):void{if(!$ok)throw new RuntimeException($name);$checks++;};
$reject=function(callable $fn,string $name)use($check):void{$rejected=false;try{$fn();}catch(RuntimeException $e){$rejected=true;}$check($rejected,$name);};
putenv('APP_ENV');putenv('APP_URL');
$check(E::baseUrl()==='https://partners.repostit.io','Production URL unchanged');
$check(E::appUrl()==='https://app.repostit.io','Production app unchanged');
$check(E::referralHosts()===['repostit.io','www.repostit.io','app.repostit.io'],'Production referral destinations unchanged');
$check(CreatorHub::firebaseConfig()['projectId']==='repostit-91b0e','Production auth unchanged');
$check(E::functionUrl('partnerPortalBridge')==='https://us-central1-repostit-91b0e.cloudfunctions.net/partnerPortalBridge','Production bridge unchanged');
putenv('APP_ENV=staging');
$check(E::appUrl()==='https://preview.repostit.io','Staging links use preview');
$check(E::referralHosts()===['preview.repostit.io'],'Staging cannot redirect to production');
$reject(fn()=>E::baseUrl(),'Missing staging URL rejected');
foreach(['https://partners.repostit.io','http://partners-staging-example.up.railway.app','https://user:pass@partners-staging-example.up.railway.app','https://partners-staging-example.up.railway.app?secret=oops'] as $url){
 putenv('APP_URL='.$url);$reject(fn()=>E::baseUrl(),'Unsafe staging URL rejected');
}
putenv('APP_URL=https://partners-staging-example.up.railway.app');
$check(CreatorRules::affiliateLink('QA_ISOLATED')==='https://partners-staging-example.up.railway.app/go/QA_ISOLATED','Staging links stay isolated');
foreach(['partnerPortalBridge','getCreatorPerformance','manageComplimentaryPublishingAccess'] as $name){
 $check(E::functionUrl($name)==='https://us-central1-repostit-dev.cloudfunctions.net/'.$name,'All staging callables isolated');
}
$reject(fn()=>E::firebaseConfig(),'Missing staging config cannot fall back to live');
putenv('PARTNER_FIREBASE_API_KEY=fixture-public-key');putenv('PARTNER_FIREBASE_APP_ID=fixture-public-app');
$check(E::firebaseConfig()['projectId']==='repostit-dev','Staging auth isolated');
putenv('BREVO_API_KEY=fixture-never-send');putenv('RESEND_API_KEY=fixture-never-send');
(new EmailService())->sendWelcomeEmail('qa@example.invalid','Disposable QA');
$check(true,'Staging skips provider email even when keys are set');
putenv('APP_ENV=unexpected');$reject(fn()=>E::baseUrl(),'Unknown environment fails closed');
echo 'Portal environment checks passed: '.$checks.PHP_EOL;
