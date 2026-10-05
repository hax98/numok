<?php
declare(strict_types=1);
namespace Numok\Controllers;
use Numok\Database\Database;
use Numok\Middleware\PartnerMiddleware;
use Numok\Services\{CreatorHub,CreatorRules,CreatorCampaigns,PortalSecurity};

class CreatorHubController extends PartnerBaseController {
    public function __construct() { PartnerMiddleware::handle(); }
    public function index(): void {
        $partnerId=(int)$_SESSION['partner_id'];
        CreatorCampaigns::reconcile($partnerId);
        CreatorCampaigns::refreshActions($partnerId);
        $syncError=null;
        try { CreatorHub::refreshReferrals($partnerId); } catch (\Throwable $e) { $syncError='Signup sync unavailable. Existing records are shown, not live signup totals.'; }
        $this->renderHub($partnerId,$syncError);
    }
    protected function renderHub(int $partnerId, ?string $syncError = null): void {
        $this->view('partner/creator/index',['title'=>'Your creator workspace | Repostit Partners',
            'partner'=>Database::query('SELECT id,contact_name,email FROM partners WHERE id=?',[$partnerId])->fetch(),
            'programs'=>CreatorHub::programs($partnerId),'summary'=>CreatorHub::summary($partnerId),
            'profile'=>CreatorHub::profile($partnerId),'contents'=>CreatorHub::contents($partnerId),
            'campaigns'=>CreatorCampaigns::all($partnerId),'actions'=>CreatorCampaigns::actions($partnerId),
            'socials'=>Database::query('SELECT * FROM creator_social_profiles WHERE partner_id=?',[$partnerId])->fetchAll(),
            'csrf'=>PortalSecurity::token(),'firebaseConfig'=>CreatorHub::firebaseConfig(),'readOnly'=>false,'syncError'=>$syncError]);
    }
    public function addProfile(): void {
        PortalSecurity::requirePost();
        try {
            $platform=(string)($_POST['platform']??'');
            $url=CreatorRules::socialUrl((string)($_POST['profile_url']??''),$platform);
            $name=trim((string)($_POST['display_name']??''));
            if (!$name || strlen($name)>180) throw new \InvalidArgumentException('Enter your profile name, up to 180 characters.');
            Database::query('INSERT INTO creator_social_profiles (partner_id,platform,profile_url,display_name) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE display_name=VALUES(display_name)',[$_SESSION['partner_id'],$platform,$url,$name]);
            $_SESSION['success']='Social profile saved. Link Repostit below to import provider metrics.';
        } catch (\Throwable $e) { $_SESSION['error']=$e instanceof \InvalidArgumentException ? $e->getMessage() : 'Could not save this profile.'; }
        header('Location: /dashboard#socials'); exit;
    }
    public function createContent(): void {
        PortalSecurity::requirePost();
        try {
            $partnerId=(int)$_SESSION['partner_id']; $programId=(int)($_POST['partner_program_id']??0);
            if (!Database::query("SELECT id FROM partner_programs WHERE id=? AND partner_id=? AND status='active'",[$programId,$partnerId])->fetch()) throw new \InvalidArgumentException('Join a program before creating your content link.');
            $title=trim((string)($_POST['title']??'')); $platform=(string)($_POST['platform']??''); $kind=(string)($_POST['kind']??'');
            if (!$title || strlen($title)>180 || !in_array($platform,CreatorRules::PLATFORMS,true) || !in_array($kind,['video','story','post','tutorial'],true)) throw new \InvalidArgumentException('Choose a title, platform and format.');
            $campaignId=(int)($_POST['campaign_id']??0);
            if($campaignId){
                $campaign=Database::query("SELECT * FROM creator_campaigns WHERE id=? AND partner_id=? AND partner_program_id=? AND status='accepted'",[$campaignId,$partnerId,$programId])->fetch();
                if(!$campaign)throw new \InvalidArgumentException('Accept this campaign before adding its content.');
                $items=json_decode($campaign['deliverables'],true)?:[];
                if(!array_filter($items,fn($d)=>$d['platform']===$platform&&$d['kind']===$kind))throw new \InvalidArgumentException('Use a format and platform agreed for this campaign.');
            }
            Database::insert('creator_content',['partner_id'=>$partnerId,'partner_program_id'=>$programId,'campaign_id'=>$campaignId?:null,'token'=>bin2hex(random_bytes(12)),'title'=>$title,'platform'=>$platform,'kind'=>$kind]);
            $_SESSION['success']='Your content link is ready. Use this specific link in the Story, bio or requested DM for this content.';
        } catch (\Throwable $e) { $_SESSION['error']=$e instanceof \InvalidArgumentException ? $e->getMessage() : 'Could not create the content link.'; }
        header('Location: /dashboard#content'); exit;
    }
    public function submitContent(): void {
        PortalSecurity::requirePost();
        try {
            $id=(int)($_POST['content_id']??0); $partnerId=(int)$_SESSION['partner_id'];
            $content=Database::query('SELECT * FROM creator_content WHERE id=? AND partner_id=?',[$id,$partnerId])->fetch();
            if (!$content) throw new \InvalidArgumentException('Content not found in your account.');
            $url=CreatorRules::socialUrl((string)($_POST['post_url']??''),$content['platform']);
            Database::update('creator_content',['post_url'=>$url,'status'=>'submitted','submitted_at'=>gmdate('Y-m-d H:i:s'),'verified_at'=>null,'provider_metrics'=>null,'analytics_status'=>'not_checked','provider_attempt_id'=>null,'provider_checked_at'=>null],'id=? AND partner_id=?',[$id,$partnerId]);
            if(!empty($content['campaign_id']))Database::query("UPDATE creator_campaigns SET status='accepted' WHERE id=? AND partner_id=? AND status='completed'",[$content['campaign_id'],$partnerId]);
            CreatorHub::matchProviderPosts($partnerId,CreatorHub::profile($partnerId)['analytics_snapshot']['posts']??[]);
            $_SESSION['success']='Published URL submitted. Repostit will verify the promotional content separately from its analytics.';
        } catch (\Throwable $e) { $_SESSION['error']=$e instanceof \InvalidArgumentException ? $e->getMessage() : 'Could not submit this URL.'; }
        header('Location: /dashboard#content'); exit;
    }
    public function syncAccount(): void {
        PortalSecurity::requirePost();
        $input=json_decode(file_get_contents('php://input'),true) ?: [];
        $token=$input['idToken']??'';
        if (!is_string($token) || strlen($token)>10000 || empty($input['consent'])) PortalSecurity::json(['error'=>'Please authorize linking your Repostit account.'],400);
        $partnerId=(int)$_SESSION['partner_id'];
        if (($_SESSION['creator_sync_at']??0)>time()-20) PortalSecurity::json(['error'=>'Please wait a moment before refreshing again.'],429);
        $_SESSION['creator_sync_at']=time();
        // Release the session lock before provider requests.
        session_write_close();
        try {
            $account=CreatorHub::bridge(['action'=>'account','idToken'=>$token]);
            $user=$account['identity']??null;
            if (!$user || empty($user['uid']) || empty($user['email'])) throw new \RuntimeException('Verify your Repostit email before linking this account.');
            $uid=$user['uid']; $existing=CreatorHub::profile($partnerId);
            if (!empty($existing['firebase_uid']) && $existing['firebase_uid']!==$uid) throw new \RuntimeException('Disconnect your current Repostit account before linking a different one.');
            if (Database::query('SELECT partner_id FROM creator_profiles WHERE firebase_uid=? AND partner_id<>?',[$uid,$partnerId])->fetch()) throw new \RuntimeException('This Repostit account is already linked to another partner.');
            // Callable checks the Firebase ID token and queries only this authenticated creator.
            $analytics=PortalSecurity::postJson('https://us-central1-repostit-91b0e.cloudfunctions.net/getCreatorPerformance',['data'=>['periodDays'=>90,'refresh'=>false]],['Authorization: Bearer '.$token],125)['result']??[];
            // Store only the public metrics needed for the portal, never provider tokens.
            $snapshot=['connections'=>$analytics['capability']['connections']??[],'posts'=>array_map(static fn($p)=>[
                'attemptId'=>$p['attemptId']??null,'platform'=>$p['platform']??null,'publishedUrl'=>$p['publishedUrl']??null,
                'title'=>$p['title']??'Published post','publishedAtMs'=>$p['publishedAtMs']??null,
                'metrics'=>$p['metrics']??null,'insightsStatus'=>$p['insightsStatus']??'unavailable','fetchedAtMs'=>$p['fetchedAtMs']??null],
                array_values(array_filter($analytics['posts']??[],fn($p)=>empty($p['qaFixture']))))];
            Database::query('INSERT INTO creator_profiles (partner_id,firebase_uid,repostit_email,consent_at,account_snapshot,analytics_snapshot,synced_at) VALUES (?,?,?,UTC_TIMESTAMP(),?,?,UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE firebase_uid=VALUES(firebase_uid),repostit_email=VALUES(repostit_email),consent_at=VALUES(consent_at),account_snapshot=VALUES(account_snapshot),analytics_snapshot=VALUES(analytics_snapshot),synced_at=UTC_TIMESTAMP()',
                [$partnerId,$uid,$user['email'],json_encode($account['account']??[]),json_encode($snapshot)]);
            CreatorHub::matchProviderPosts($partnerId,$snapshot['posts']);
            // Import native-post metrics too, with only server-side provider credentials.
            CreatorHub::refreshAnalytics($partnerId,true);
            PortalSecurity::json(['ok'=>true]);
        } catch (\Throwable $e) {
            $safe=$e instanceof \RuntimeException ? $e->getMessage() : 'Could not synchronize your account.';
            PortalSecurity::json(['error'=>$safe],400);
        }
    }
    public function disconnectAccount(): void {
        PortalSecurity::requirePost();
        Database::update('creator_profiles',['firebase_uid'=>null,'repostit_email'=>null,'consent_at'=>null,'account_snapshot'=>null,'analytics_snapshot'=>null,'synced_at'=>null],'partner_id=?',[$_SESSION['partner_id']]);
        Database::query("UPDATE creator_content SET provider_metrics=NULL,provider_attempt_id=NULL,provider_checked_at=NULL,analytics_status='not_checked' WHERE partner_id=?",[$_SESSION['partner_id']]);
        $_SESSION['success']='Portal account disconnected. Your social connections in Repostit have not been changed.';
        header('Location: /dashboard#socials'); exit;
    }
    public function refreshAnalytics(): void {
        PortalSecurity::requirePost();
        try { CreatorHub::refreshAnalytics((int)$_SESSION['partner_id']); $_SESSION['success']='Available post metrics refreshed. Missing permissions or unsupported formats are shown separately.'; }
        catch(\Throwable $e){$_SESSION['error']='Analytics refresh is temporarily unavailable. The last snapshot is shown.';}
        header('Location: /dashboard#content');exit;
    }
    public function respondCampaign(): void {
        PortalSecurity::requirePost();$id=(int)($_POST['campaign_id']??0);$decision=$_POST['decision']??'';
        if(!in_array($decision,['accepted','declined'],true)){http_response_code(400);exit('Choose accept or decline.');}
        try{CreatorCampaigns::respond((int)$_SESSION['partner_id'],$id,$decision);}
        catch(\Throwable $e){$_SESSION['error']='This campaign is no longer awaiting your response.';header('Location: /dashboard#campaigns');exit;}
        $_SESSION['success']=$decision==='accepted'?'Campaign accepted. Create a dedicated link for each agreed placement below.':'Campaign declined. No content obligation was added.';
        header('Location: /dashboard#campaigns');exit;
    }
}
