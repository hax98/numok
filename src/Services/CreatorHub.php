<?php
declare(strict_types=1);
namespace Numok\Services;
use Numok\Database\Database;

final class CreatorHub {
    public static function firebaseConfig(): array {
        // Public Firebase web configuration, not an administrator credential.
        return ['apiKey'=>'AIzaSyBadVKoP1A-ZTF5vsh_VL-YkMbAWoNMpX0','authDomain'=>'repostit-91b0e.firebaseapp.com',
            'projectId'=>'repostit-91b0e','appId'=>'1:286284314864:web:f0253867299d62cf672a35'];
    }
    public static function programs(int $partnerId): array {
        return Database::query("SELECT pp.*, p.name, p.landing_page, p.commission_value, p.terms FROM partner_programs pp JOIN programs p ON p.id=pp.program_id WHERE pp.partner_id=? AND pp.status='active' AND p.status='active' ORDER BY pp.id", [$partnerId])->fetchAll();
    }
    public static function profile(int $partnerId): array {
        $row = Database::query('SELECT * FROM creator_profiles WHERE partner_id=?', [$partnerId])->fetch() ?: [];
        foreach (['account_snapshot','analytics_snapshot'] as $field) $row[$field] = json_decode($row[$field] ?? 'null', true) ?: [];
        return $row;
    }
    public static function payingCount(int $partnerId): int {
        return (int) Database::query("SELECT COUNT(DISTINCT c.customer_key) FROM conversions c JOIN partner_programs pp ON pp.id=c.partner_program_id WHERE pp.partner_id=? AND c.amount>0 AND c.status <> 'rejected' AND c.customer_key IS NOT NULL AND JSON_EXTRACT(c.metadata,'$.subscription_id') IS NOT NULL AND JSON_UNQUOTE(JSON_EXTRACT(c.metadata,'$.subscription_id'))<>'null'", [$partnerId])->fetchColumn();
    }
    public static function summary(int $partnerId): array {
        $payments = Database::query("SELECT COUNT(*) payment_count, COALESCE(SUM(CASE WHEN c.status<>'rejected' THEN c.commission_amount ELSE 0 END),0) commission, COALESCE(SUM(CASE WHEN c.status='paid' THEN c.commission_amount ELSE 0 END),0) paid, COALESCE(SUM(CASE WHEN c.status IN ('pending','payable') THEN c.commission_amount ELSE 0 END),0) unpaid FROM conversions c JOIN partner_programs pp ON pp.id=c.partner_program_id WHERE pp.partner_id=? AND c.currency='usd'", [$partnerId])->fetch();
        $payouts = [];
        foreach (Database::query('SELECT * FROM creator_bonus_payouts WHERE partner_id=?', [$partnerId])->fetchAll() as $row) $payouts[(int)$row['threshold']]=$row;
        $count = self::payingCount($partnerId);
        $milestones = CreatorRules::milestones($count, $payouts);
        return $payments + ['paying_customers'=>$count,'milestones'=>$milestones,
            'signups'=>(int)Database::query('SELECT COUNT(*) FROM creator_referrals WHERE partner_id=?',[$partnerId])->fetchColumn(),
            'clicks'=>(int)Database::query('SELECT COUNT(*) FROM clicks c JOIN partner_programs pp ON pp.id=c.partner_program_id WHERE pp.partner_id=?',[$partnerId])->fetchColumn(),
            'bonus_paid'=>array_sum(array_map(fn($m)=>$m['status']==='paid' ? $m['amount'] : 0,$milestones)),
            'bonus_earned'=>array_sum(array_map(fn($m)=>$m['status']==='earned' ? $m['amount'] : 0,$milestones))];
    }
    public static function contents(int $partnerId): array {
        $rows = Database::query('SELECT * FROM creator_content WHERE partner_id=? ORDER BY created_at DESC',[$partnerId])->fetchAll();
        foreach ($rows as &$row) {
            $token=$row['token']; $pp=$row['partner_program_id'];
            $row['clicks']=(int)Database::query("SELECT COUNT(*) FROM clicks WHERE partner_program_id=? AND (JSON_UNQUOTE(JSON_EXTRACT(sub_ids,'$.sid'))=? OR JSON_UNQUOTE(JSON_EXTRACT(sub_ids,'$.utm_content'))=?)",[$pp,$token,$token])->fetchColumn();
            $row['signups']=(int)Database::query('SELECT COUNT(*) FROM creator_referrals WHERE partner_id=? AND content_token=?',[$partnerId,$token])->fetchColumn();
            $money=Database::query("SELECT COUNT(DISTINCT CASE WHEN JSON_EXTRACT(metadata,'$.subscription_id') IS NOT NULL AND JSON_UNQUOTE(JSON_EXTRACT(metadata,'$.subscription_id'))<>'null' THEN customer_key ELSE NULL END) customers, COALESCE(SUM(CASE WHEN currency='usd' THEN commission_amount ELSE 0 END),0) earnings FROM conversions WHERE partner_program_id=? AND content_token=? AND amount>0 AND status<>'rejected'",[$pp,$token])->fetch();
            $row += $money; $row['provider_metrics']=json_decode($row['provider_metrics'] ?? 'null',true);
            $row['link']=CreatorRules::link($token);
        }
        unset($row);
        return $rows;
    }
    public static function bridge(array $data): array {
        $secret=getenv('PARTNER_PORTAL_BRIDGE_KEY') ?: '';
        if (!$secret) throw new \RuntimeException('Repostit referral sync is not configured.');
        return PortalSecurity::postJson('https://us-central1-repostit-91b0e.cloudfunctions.net/partnerPortalBridge', $data,
            ['X-Partner-Bridge-Key: '.$secret], 90);
    }
    public static function refreshReferrals(int $partnerId): void {
        $profile=self::profile($partnerId);
        if (!empty($profile['referrals_synced_at']) && strtotime($profile['referrals_synced_at'])>time()-300) return;
        $programs=self::programs($partnerId);
        if (!$programs) return;
        $partner=Database::query('SELECT email FROM partners WHERE id=?',[$partnerId])->fetch();
        $byCode=array_column($programs,'id','tracking_code');
        foreach(Database::query('SELECT a.tracking_code,a.partner_program_id FROM creator_tracking_aliases a JOIN partner_programs pp ON pp.id=a.partner_program_id WHERE pp.partner_id=?',[$partnerId])->fetchAll() as $alias) $byCode[$alias['tracking_code']]=$alias['partner_program_id'];
        $result=self::bridge(['action'=>'referrals','codes'=>array_keys($byCode),'excludeEmail'=>$partner['email'],'excludeUid'=>$profile['firebase_uid']??null]);
        Database::transaction(function () use ($result,$byCode,$partnerId) {
            foreach ($result['referrals'] ?? [] as $referral) {
                $programId=$byCode[$referral['code'] ?? ''] ?? null;
                if (!$programId || !preg_match('/^[a-f0-9]{64}$/',$referral['customerHash'] ?? '')) continue;
                $date=static fn($v)=>$v ? gmdate('Y-m-d H:i:s',strtotime($v)) : null;
                Database::query('INSERT INTO creator_referrals (partner_id,partner_program_id,customer_hash,stripe_customer_id,content_token,signed_up_at,first_publish_at) VALUES (?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE stripe_customer_id=IF(partner_id=VALUES(partner_id),VALUES(stripe_customer_id),stripe_customer_id), first_publish_at=IF(partner_id=VALUES(partner_id),VALUES(first_publish_at),first_publish_at)',
                    [$partnerId,$programId,$referral['customerHash'],$referral['stripeCustomerId']??null,$referral['contentToken']??null,$date($referral['signedUpAt']),$date($referral['firstPublishAt']??null)]);
            }
            foreach ($result['payments']??[] as $payment) self::syncPayment($partnerId,$byCode,$payment);
            Database::query('INSERT INTO creator_profiles (partner_id,referrals_synced_at) VALUES (?,UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE referrals_synced_at=UTC_TIMESTAMP()',[$partnerId]);
        });
    }
    private static function syncPayment(int $partnerId,array $byCode,array $payment): void {
        $ppId=$byCode[$payment['code']??'']??null;
        if (!$ppId || !preg_match('/^in_[A-Za-z0-9]+$/',$payment['invoiceId']??'') || !preg_match('/^cus_[A-Za-z0-9]+$/',$payment['customerKey']??'')) return;
        $program=Database::query('SELECT p.reward_days,p.commission_type,p.commission_value FROM partner_programs pp JOIN programs p ON p.id=pp.program_id WHERE pp.id=? AND pp.partner_id=?',[$ppId,$partnerId])->fetch();
        $amount=max(0,(int)$payment['amountCents'])/100;
        $commission=$program['commission_type']==='percentage' ? round($amount*(float)$program['commission_value']/100,2) : ($amount>0?(float)$program['commission_value']:0);
        $matured=strtotime($payment['paidAt']) <= time()-max(0,(int)$program['reward_days'])*86400;
        $status=(!$amount || !empty($payment['disputed']))?'rejected':($matured?'payable':'pending');
        $token=$payment['contentToken']??null;
        if ($token && !Database::query('SELECT 1 FROM creator_content WHERE token=? AND partner_program_id=?',[$token,$ppId])->fetchColumn()) $token=null;
        $existing=Database::query('SELECT * FROM conversions WHERE stripe_payment_id=? FOR UPDATE',[$payment['invoiceId']])->fetch();
        if ($existing && (int)$existing['partner_program_id']!==(int)$ppId) return;
        $meta=json_decode($existing['metadata']??'{}',true)?:[];
        $meta += ['subscription_id'=>$payment['subscriptionId'],'payment_intent'=>$payment['paymentIntent'],'sid'=>$token,'utm_content'=>$token,'source'=>'stripe_verified_sync'];
        if ($existing && $existing['status']==='paid' && $amount<(float)$existing['amount']) {
            $meta['paid_refund_requires_review']=true; $meta['original_commission']=$existing['commission_amount'];
        }
        if ($existing && $status!=='rejected' && $existing['status']!=='pending') $status=$existing['status'];
        $date=gmdate('Y-m-d H:i:s',strtotime($payment['paidAt']));
        $data=['partner_program_id'=>$ppId,'stripe_payment_id'=>$payment['invoiceId'],'amount'=>$amount,'commission_amount'=>$commission,'status'=>$status,
            'metadata'=>json_encode($meta),'customer_key'=>$payment['customerKey'],'content_token'=>$token,'currency'=>$payment['currency'],'created_at'=>$date];
        if ($existing) Database::update('conversions',$data,'id=?',[$existing['id']]); else Database::insert('conversions',$data);
    }
    public static function matchProviderPosts(int $partnerId, array $posts): void {
        foreach (self::contents($partnerId) as $content) {
            if (!$content['post_url']) continue;
            $normalized=fn($url)=>preg_replace('~[?#].*$~','',rtrim((string)$url,'/'));
            foreach ($posts as $post) {
                if (!empty($post['qaFixture'])) continue;
                if ($normalized($post['publishedUrl'] ?? '') !== $normalized($content['post_url'])) continue;
                Database::update('creator_content',['provider_attempt_id'=>$post['attemptId']??null,
                    'provider_metrics'=>json_encode($post['metrics']??null),'analytics_status'=>$post['insightsStatus']??'not_checked','provider_checked_at'=>!empty($post['fetchedAtMs'])?gmdate('Y-m-d H:i:s',(int)($post['fetchedAtMs']/1000)):null], 'id=? AND partner_id=?',[$content['id'],$partnerId]);
            }
        }
    }
    public static function refreshAnalytics(int $partnerId,bool $force=false): bool {
        $profile=self::profile($partnerId);
        if(empty($profile['firebase_uid'])||empty($profile['consent_at']))return false;
        if(!$force&&!empty($profile['synced_at'])&&strtotime($profile['synced_at'].' UTC')>time()-900)return false;
        $posts=array_values(array_filter(self::contents($partnerId),fn($c)=>!empty($c['post_url'])));
        $snapshot=['connections'=>[],'posts'=>[]];$account=null;
        // Bounded chunks, no submitted content is silently dropped.
        $chunks=array_chunk($posts,20);if(!$chunks)$chunks=[[]];
        foreach($chunks as $chunk){
            $result=self::bridge(['action'=>'analytics','uid'=>$profile['firebase_uid'],'email'=>$profile['repostit_email'],
                'contents'=>array_map(fn($c)=>['token'=>$c['token'],'platform'=>$c['platform'],'kind'=>$c['kind'],'url'=>$c['post_url']],$chunk)]);
            $snapshot['connections']=$result['connections']??[];$account=$result['account']??$account;
            foreach($result['posts']??[] as $post){
                $token=$post['token']??'';if(!preg_match('/^[a-f0-9]{24}$/',$token))continue;
                $snapshot['posts'][]=$post;
                Database::update('creator_content',['provider_metrics'=>json_encode($post['metrics']??null),'analytics_status'=>$post['status']??'retry',
                    'provider_checked_at'=>!empty($post['fetchedAtMs'])?gmdate('Y-m-d H:i:s',(int)($post['fetchedAtMs']/1000)):null], 'partner_id=? AND token=? AND EXISTS (SELECT 1 FROM creator_profiles WHERE partner_id=? AND firebase_uid=? AND consent_at IS NOT NULL)',[$partnerId,$token,$partnerId,$profile['firebase_uid']]);
            }
        }
        $data=['analytics_snapshot'=>json_encode($snapshot),'synced_at'=>gmdate('Y-m-d H:i:s')];
        if($account!==null)$data['account_snapshot']=json_encode($account);
        Database::update('creator_profiles',$data,'partner_id=? AND firebase_uid=? AND consent_at IS NOT NULL',[$partnerId,$profile['firebase_uid']]);
        return true;
    }
}
