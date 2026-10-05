<?php
use Numok\Database\Database;
use Numok\Services\{CreatorHub,CreatorCampaigns};
$db=Database::getInstance();
foreach(['creator_campaigns','creator_action_queue','creator_audit_log','creator_profiles','creator_content','creator_referrals','partner_programs','conversions','clicks'] as $table){
 $definition=$db->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM)[1];
 $definition=preg_replace('/^CREATE TABLE/','CREATE TEMPORARY TABLE',$definition);
 $definition=implode("\n",array_filter(explode("\n",$definition),fn($l)=>!str_contains($l,'CONSTRAINT')));
 $db->exec(preg_replace('/,\n\)/',"\n)",$definition));
}
$db->exec("INSERT INTO partner_programs (id,partner_id,program_id,tracking_code,status) VALUES (900001,900001,1,'isolated_flow','active')");
$db->exec("INSERT INTO creator_profiles (partner_id) VALUES (900001)");
$d=json_encode([['platform'=>'instagram','kind'=>'video','count'=>1]]);
foreach([1,2,3] as $id)Database::insert('creator_campaigns',['id'=>$id,'partner_id'=>900001,'partner_program_id'=>900001,'title'=>'Isolated fixture','brief'=>'Not a real creator campaign','deliverables'=>$d,'due_at'=>gmdate('Y-m-d H:i:s',time()-86400),'created_by'=>1]);
$checks=0;$check=function($c,$name)use(&$checks){if(!$c)throw new RuntimeException($name);$checks++;};
CreatorCampaigns::respond(900001,1,'accepted');
$check($db->query('SELECT status FROM creator_campaigns WHERE id=1')->fetchColumn()==='accepted','Accept proposal');
try{CreatorCampaigns::respond(900002,2,'accepted');$ok=false;}catch(RuntimeException $e){$ok=true;}$check($ok,'Cross-creator acceptance denied');
try{CreatorCampaigns::respond(900001,1,'accepted');$ok=false;}catch(RuntimeException $e){$ok=true;}$check($ok,'Replay denied');
CreatorCampaigns::respond(900001,2,'declined');
$check($db->query('SELECT status FROM creator_campaigns WHERE id=2')->fetchColumn()==='declined','Decline proposal');
$token=str_repeat('b',24);Database::insert('creator_content',['partner_id'=>900001,'partner_program_id'=>900001,'campaign_id'=>1,'title'=>'Isolated fixture','platform'=>'instagram','kind'=>'video','token'=>$token,'status'=>'submitted']);
CreatorCampaigns::reconcile(900001);$check($db->query('SELECT status FROM creator_campaigns WHERE id=1')->fetchColumn()==='accepted','Submitted content not verified');
Database::query("UPDATE creator_content SET status='verified'");CreatorCampaigns::reconcile(900001);
$check($db->query('SELECT status FROM creator_campaigns WHERE id=1')->fetchColumn()==='completed','Verified deliverable completes campaign');
CreatorCampaigns::refreshActions(900001);$first=count(CreatorCampaigns::actions(900001));CreatorCampaigns::refreshActions(900001);
$check($first===count(CreatorCampaigns::actions(900001)),'Reminders deduplicated');
$check(count(CreatorCampaigns::actions(900002))===0,'Action queue creator isolation');
Database::query("UPDATE creator_profiles SET firebase_uid='fixture',consent_at=UTC_TIMESTAMP(),account_snapshot=? WHERE partner_id=900001",[json_encode(['influencerExpiresAt'=>'2099-01-01'])]);
CreatorCampaigns::refreshActions(900001);
$check(!array_filter(CreatorCampaigns::actions(900001),fn($a)=>$a['action_key']==='link'),'Resolved onboarding action removed');
$check((int)$db->query("SELECT COUNT(*) FROM creator_audit_log WHERE action='campaign_accepted'")->fetchColumn()===1,'Acceptance audited exactly once');
$check((int)$db->query('SELECT COUNT(*) FROM conversions')->fetchColumn()===0,'No synthetic payment created');
echo "Isolated campaign MySQL: $checks checks passed. No persistent fixtures, messages or payments.\n";
