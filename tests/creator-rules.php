<?php
declare(strict_types=1);
use Numok\Services\CreatorRules;
$n=0;
$check=static function(bool $ok,string $name) use (&$n): void { if(!$ok)throw new RuntimeException($name);$n++; };
foreach([0,19,20,499,500,501] as $count){
 $m=CreatorRules::milestones($count,[]);
 $check($m[0]['status']===($count>=20?'earned':'locked'),'First milestone');
 $check($m[1]['status']===($count>=500?'earned':'locked'),'Second milestone');
 $check(array_sum(array_column($m,'amount'))===5200,'Additional bonus, not cumulative replacement');
}
$m=CreatorRules::milestones(10,[20=>['status'=>'paid']]);$check($m[0]['status']==='paid','Refunds never erase a paid bonus receipt');
$check(CreatorRules::attribution((object)['via'=>'creator','utm_content'=>'clip'])['tracking_code']==='creator','Canonical via');
$check(CreatorRules::attribution((object)['numok_tracking_code'=>'legacy'])['tracking_code']==='legacy','Legacy metadata');
$check(CreatorRules::customerKey((object)['customer'=>'cus_123'])==='cus_123','Renewals same unique customer');
$check(CreatorRules::customerKey((object)['metadata'=>(object)['userId'=>'uid']])==='uid:'.hash('sha256','uid'),'Missing Stripe ID has stable identity');
foreach(['https://evil.test/','https://instagram.com.evil.test/','http://www.instagram.com/user','https://user:pw@instagram.com/user','https://instagram.com:443/user'] as $url){try{CreatorRules::socialUrl($url,'instagram');throw new RuntimeException('Unsafe social URL accepted');}catch(InvalidArgumentException $e){$n++;}}
$check(CreatorRules::socialUrl('https://www.instagram.com/creator/','instagram')==='https://www.instagram.com/creator/','Valid social URL');
$check(CreatorRules::link(str_repeat('1',24))==='https://partners.repostit.io/r/'.str_repeat('1',24),'All-digit content tokens remain strings');
$check(CreatorRules::affiliateLink('hax')==='https://partners.repostit.io/go/hax','Canonical shareable affiliate link');
$check(CreatorRules::affiliateLink('Creator_123-abc')==='https://partners.repostit.io/go/Creator_123-abc','Creator code casing and supported characters preserved');
$check(CreatorRules::affiliateLink('a/b?c')==='https://partners.repostit.io/go/a%2Fb%3Fc','Affiliate code is safely encoded as one path segment');
echo 'Creator rules checks passed: '.$n.PHP_EOL;
