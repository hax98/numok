<?php
declare(strict_types=1);
namespace Numok\Services;
use Numok\Database\Database;

final class CreatorCampaigns {
    public static function respond(int $partnerId,int $id,string $decision): void {
        if(!in_array($decision,['accepted','declined'],true))throw new \InvalidArgumentException('Choose accept or decline.');
        Database::transaction(function()use($partnerId,$id,$decision){
            $row=Database::query('SELECT * FROM creator_campaigns WHERE id=? AND partner_id=? FOR UPDATE',[$id,$partnerId])->fetch();
            if(!$row || $row['status']!=='proposed')throw new \RuntimeException('This campaign is no longer awaiting your response.');
            Database::update('creator_campaigns',['status'=>$decision,'accepted_at'=>$decision==='accepted'?gmdate('Y-m-d H:i:s'):null],'id=?',[$id]);
            Database::insert('creator_audit_log',['partner_id'=>$partnerId,'action'=>'campaign_'.$decision,'details'=>json_encode(['campaignId'=>$id])]);
        });
    }
    public static function all(int $partnerId): array {
        $contents=CreatorHub::contents($partnerId);
        $rows=Database::query('SELECT * FROM creator_campaigns WHERE partner_id=? ORDER BY created_at DESC',[$partnerId])->fetchAll();
        foreach($rows as &$row){
            $row['deliverables']=json_decode($row['deliverables'],true)?:[];
            $row['progress']=CreatorFlowRules::campaignProgress($row['deliverables'],array_values(array_filter($contents,fn($c)=>(int)($c['campaign_id']??0)===(int)$row['id'])));
            $row['overdue']=in_array($row['status'],['proposed','accepted'],true)&&strtotime($row['due_at'].' UTC')<time()&&!$row['progress']['complete'];
        }
        unset($row);return $rows;
    }
    public static function reconcile(int $partnerId): void {
        foreach(self::all($partnerId) as $campaign){
            if($campaign['status']==='accepted'&&$campaign['progress']['complete']) Database::update('creator_campaigns',['status'=>'completed'],'id=? AND partner_id=? AND status=?',[$campaign['id'],$partnerId,'accepted']);
        }
    }
    public static function actions(int $partnerId): array {
        return Database::query('SELECT * FROM creator_action_queue WHERE partner_id=? AND resolved_at IS NULL ORDER BY created_at',[$partnerId])->fetchAll();
    }
    public static function refreshActions(int $partnerId): void {
        $profile=CreatorHub::profile($partnerId);$contents=CreatorHub::contents($partnerId);$active=[];
        $add=static function($key,$message,$destination,$audience)use(&$active){$active[$key]=[$message,$destination,$audience];};
        if(!CreatorHub::programs($partnerId))$add('join','Join the program to get your referral link.','/programs','creator');
        if(empty($profile['firebase_uid']))$add('link','Link your Repostit account so we can verify access and import metrics.','/dashboard#socials','creator');
        elseif(empty($profile['account_snapshot']['influencerExpiresAt']))$add('grant','Review this creator and the promised annual account before granting access.','/admin/creators','admin');
        foreach(self::all($partnerId) as $c){
            if($c['status']==='proposed')$add('accept_'.$c['id'],'Review the proposed campaign: '.$c['title'].'.','/dashboard#campaigns','creator');
            if($c['overdue']&&$c['status']==='accepted')$add('overdue_'.$c['id'],'The deadline for '.$c['title'].' has passed. Submit the published URLs or agree a new plan with Hax.','/dashboard#campaigns','creator');
        }
        if(count(array_filter($contents,fn($c)=>$c['status']==='submitted')))$add('review','Published content is waiting for your review.','/admin/creators','admin');
        Database::transaction(function()use($partnerId,$active){
            Database::query('UPDATE creator_action_queue SET resolved_at=UTC_TIMESTAMP() WHERE partner_id=? AND resolved_at IS NULL',[$partnerId]);
            foreach($active as $key=>$a)Database::query('INSERT INTO creator_action_queue (partner_id,action_key,message,destination,audience) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE message=VALUES(message),resolved_at=NULL,updated_at=UTC_TIMESTAMP()',[$partnerId,$key,...$a]);
        });
        // In-portal reminders only. Never sends email, DMs or payments.
    }
}
