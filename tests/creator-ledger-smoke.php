<?php
declare(strict_types=1);
use Numok\Database\Database;
use Numok\Services\{CreatorHub,CreatorRules};
// MySQL TEMPORARY tables shadow real names ONLY on this one connection.
// They never create users, Stripe payments, or persistent production fixtures.
$db=Database::getInstance();
foreach(['conversions','partner_programs','creator_bonus_payouts','creator_referrals','clicks','creator_content'] as $table) {
 $definition=$db->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM)[1];
 $definition=preg_replace('/^CREATE TABLE/','CREATE TEMPORARY TABLE',$definition);
 $definition=implode("\n",array_filter(explode("\n",$definition),static fn($line)=>!str_contains($line,'CONSTRAINT')));
 $definition=preg_replace('/,\n\)/',"\n)",$definition);
 $db->exec($definition);
}
$db->exec("INSERT INTO partner_programs (id,partner_id,program_id,tracking_code,status) VALUES (900001,900001,1,'isolated_qa','active')");
$checks=0;
$check=static function(bool $ok,string $name) use (&$checks): void { if(!$ok)throw new RuntimeException($name);$checks++; };
$payment=static fn($i)=>['code'=>'isolated_qa','invoiceId'=>'in_QA'.$i,'customerKey'=>'cus_QA'.$i,'contentToken'=>null,'subscriptionId'=>'sub_QA'.$i,'paymentIntent'=>null,'amountCents'=>1400,'currency'=>'usd','disputed'=>false,'paidAt'=>'2026-10-04T10:00:00Z'];
$method=new ReflectionMethod(CreatorHub::class,'syncPayment');
for($i=1;$i<=20;$i++)$method->invoke(null,900001,['isolated_qa'=>900001],$payment($i));
$check(CreatorHub::payingCount(900001)===20,'20 distinct subscribers');
$method->invoke(null,900001,['isolated_qa'=>900001],$payment(1));
$check((int)$db->query('SELECT COUNT(*) FROM conversions')->fetchColumn()===20,'Repeated sync idempotent');
$renewal=$payment(1);$renewal['invoiceId']='in_QArenewal';$method->invoke(null,900001,['isolated_qa'=>900001],$renewal);
$check(CreatorHub::payingCount(900001)===20,'Renewal is not a new subscriber');
$s=CreatorHub::summary(900001);
$check(abs((float)$s['commission']-58.80)<.01,'Recurring commission earned on renewal');
$check($s['milestones'][0]['status']==='earned'&&$s['milestones'][1]['status']==='locked','Bonus milestones separate');
$refund=$payment(2);$refund['amountCents']=0;$method->invoke(null,900001,['isolated_qa'=>900001],$refund);
$check(CreatorHub::payingCount(900001)===19,'Fully refunded subscriber excluded');
$disputed=$payment(3);$disputed['disputed']=true;$method->invoke(null,900001,['isolated_qa'=>900001],$disputed);
$check(CreatorHub::payingCount(900001)===18,'Disputed payment excluded');
$currency=$payment(30);$currency['currency']='eur';$method->invoke(null,900001,['isolated_qa'=>900001],$currency);
$check(CreatorHub::payingCount(900001)===19,'Real subscriber counts regardless of invoice currency');
$check(CreatorHub::payingCount(900002)===0,'Partner isolation');
$mature=$payment(1);$mature['paidAt']=gmdate('c',time()-90*86400);$method->invoke(null,900001,['isolated_qa'=>900001],$mature);
$check($db->query("SELECT status FROM conversions WHERE stripe_payment_id='in_QA1'")->fetchColumn()==='payable','Validated commission matures after reward delay');
for($i=31;$i<=511;$i++)$method->invoke(null,900001,['isolated_qa'=>900001],$payment($i));
$check(CreatorHub::payingCount(900001)===500,'500 distinct genuine subscribers');
$milestones=CreatorHub::summary(900001)['milestones'];
$check($milestones[0]['status']==='earned'&&$milestones[1]['status']==='earned'&&array_sum(array_column($milestones,'amount'))===5200,'Additional milestone stacks to 5200');
$check((int)$db->query("SELECT COUNT(*) FROM creator_bonus_payouts")->fetchColumn()===0,'Reaching both milestones never creates a payout');
$check((int)$db->query("SELECT COUNT(*) FROM conversions WHERE status='paid'")->fetchColumn()===0,'Commission maturity never marks money paid');
echo 'Isolated MySQL ledger checks passed: '.$checks.'. No persistent fixture writes.'.PHP_EOL;
