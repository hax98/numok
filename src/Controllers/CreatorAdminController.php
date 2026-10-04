<?php
declare(strict_types=1);
namespace Numok\Controllers;
use Numok\Database\Database;
use Numok\Middleware\AuthMiddleware;
use Numok\Services\{CreatorHub,CreatorRules,PortalSecurity};
class CreatorAdminController extends Controller {
    public function __construct() { AuthMiddleware::adminOnly(); }
    public function index(): void {
        $partners=Database::query('SELECT id,contact_name,email,company_name FROM partners ORDER BY created_at DESC')->fetchAll();
        foreach ($partners as &$p) {
            $p['summary']=CreatorHub::summary((int)$p['id']); $p['profile']=CreatorHub::profile((int)$p['id']);
            $p['contents']=CreatorHub::contents((int)$p['id']);
            $p['socials']=Database::query('SELECT * FROM creator_social_profiles WHERE partner_id=?',[$p['id']])->fetchAll();
        }
        unset($p);
        $this->view('creators/index',['title'=>'Creator operations | Repostit Partners','partners'=>$partners,'csrf'=>PortalSecurity::token(),'firebaseConfig'=>CreatorHub::firebaseConfig()]);
    }
    public function preview(int $id): void {
        $partner=Database::query('SELECT id,contact_name,email FROM partners WHERE id=?',[$id])->fetch();
        if (!$partner) { http_response_code(404); exit('Partner not found'); }
        $this->view('partner/creator/index',['title'=>'Read-only creator preview | Repostit Partners','partner'=>$partner,
            'programs'=>CreatorHub::programs($id),'summary'=>CreatorHub::summary($id),'profile'=>CreatorHub::profile($id),
            'contents'=>CreatorHub::contents($id),'socials'=>Database::query('SELECT * FROM creator_social_profiles WHERE partner_id=?',[$id])->fetchAll(),
            'csrf'=>PortalSecurity::token(),'firebaseConfig'=>CreatorHub::firebaseConfig(),'readOnly'=>true,'syncError'=>null]);
    }
    public function verifyContent(): void {
        PortalSecurity::requirePost();
        $id=(int)($_POST['content_id']??0);
        $content=Database::query('SELECT * FROM creator_content WHERE id=?',[$id])->fetch();
        if (!$content || !$content['post_url']) { http_response_code(400); exit('A published URL is required.'); }
        Database::transaction(function () use ($content,$id) {
            Database::update('creator_content',['status'=>'verified','verified_at'=>gmdate('Y-m-d H:i:s')],'id=?',[$id]);
            Database::insert('creator_audit_log',['actor_id'=>$_SESSION['user_id'],'partner_id'=>$content['partner_id'],'action'=>'content_verified','details'=>json_encode(['contentId'=>$id,'url'=>$content['post_url']])]);
        });
        $_SESSION['success']='Promotional content verified.'; header('Location: /admin/creators'); exit;
    }
    public function recordBonus(): void {
        PortalSecurity::requirePost();
        $id=(int)($_POST['partner_id']??0); $threshold=(int)($_POST['threshold']??0); $reference=trim((string)($_POST['payment_reference']??''));
        if (!isset(CreatorRules::MILESTONES[$threshold]) || strlen($reference)<3 || strlen($reference)>180 || empty($_POST['wire_confirmed'])) { http_response_code(400); exit('Confirm the completed wire and enter its reference.'); }
        try {
            Database::transaction(function ($db) use ($id,$threshold,$reference) {
                $partner=Database::query('SELECT id FROM partners WHERE id=? FOR UPDATE',[$id])->fetch();
                if (!$partner || CreatorHub::payingCount($id)<$threshold) throw new \RuntimeException('This milestone has not been reached by verified paying customers.');
                $existing=Database::query('SELECT id,status FROM creator_bonus_payouts WHERE partner_id=? AND threshold=? FOR UPDATE',[$id,$threshold])->fetch();
                if (($existing['status']??'')==='paid') throw new \RuntimeException('This bonus was already recorded as paid.');
                Database::query("INSERT INTO creator_bonus_payouts (partner_id,threshold,amount,status,paid_at,payment_reference,paid_by) VALUES (?,?,?,'paid',UTC_TIMESTAMP(),?,?) ON DUPLICATE KEY UPDATE status='paid',paid_at=UTC_TIMESTAMP(),payment_reference=VALUES(payment_reference),paid_by=VALUES(paid_by)",[$id,$threshold,CreatorRules::MILESTONES[$threshold],$reference,$_SESSION['user_id']]);
                Database::insert('creator_audit_log',['actor_id'=>$_SESSION['user_id'],'partner_id'=>$id,'action'=>'bonus_wire_recorded','details'=>json_encode(['threshold'=>$threshold,'amount'=>CreatorRules::MILESTONES[$threshold],'reference'=>$reference])]);
            });
            $_SESSION['success']='Completed bank transfer recorded. No money was sent by this portal.';
        } catch (\Throwable $e) { $_SESSION['error']=$e instanceof \RuntimeException ? $e->getMessage() : 'Unable to record the payout.'; }
        header('Location: /admin/creators'); exit;
    }
    public function syncReferrals(): void {
        PortalSecurity::requirePost();
        try { CreatorHub::refreshReferrals((int)($_POST['partner_id']??0)); $_SESSION['success']='Signup attribution synchronized from Repostit.'; }
        catch (\Throwable $e) { $_SESSION['error']='Repostit signup sync is unavailable. No totals were invented.'; }
        header('Location: /admin/creators'); exit;
    }
    public function grantAccess(): void {
        PortalSecurity::requirePost();
        $body=json_decode(file_get_contents('php://input'),true)?:[];
        $id=(int)($body['partnerId']??0);$token=$body['idToken']??'';
        if(!is_string($token)||strlen($token)>10000)PortalSecurity::json(['error'=>'Sign in as a Repostit administrator.'],400);
        $profile=CreatorHub::profile($id);
        if(empty($profile['firebase_uid'])||empty($profile['repostit_email']))PortalSecurity::json(['error'=>'The creator must link their verified Repostit account first.'],400);
        session_write_close();
        try{
            // The existing Firebase callable independently rechecks the administrator claim,
            // exact target email, grant duration and idempotent audit request ID.
            $requestId='portal_creator_'.substr(hash('sha256',$id.':'.$profile['firebase_uid']),0,48);
            $reply=PortalSecurity::postJson('https://us-central1-repostit-91b0e.cloudfunctions.net/manageComplimentaryPublishingAccess',
                ['data'=>['userId'=>$profile['firebase_uid'],'email'=>$profile['repostit_email'],'action'=>'grant',
                    'reason'=>'Annual creator partner access approved in Repostit Partners admin.','requestId'=>$requestId]],['Authorization: Bearer '.$token],125);
            $result=$reply['result']??[];
            if(empty($result['success'])||($result['userId']??null)!==$profile['firebase_uid'])throw new \RuntimeException('Grant was not verified.');
            $snapshot=$profile['account_snapshot'];$snapshot['grantState']=$result['state'];
            $snapshot['influencerExpiresAt']=$result['state']==='active'?$result['endsAt']:null;
            Database::update('creator_profiles',['account_snapshot'=>json_encode($snapshot)],'partner_id=?',[$id]);
            Database::insert('creator_audit_log',['actor_id'=>$_SESSION['user_id'],'partner_id'=>$id,'action'=>'annual_access_granted','details'=>json_encode(['requestId'=>$requestId,'endsAt'=>$result['endsAt']])]);
            PortalSecurity::json(['ok'=>true,'endsAt'=>$result['endsAt']]);
        }catch(\Throwable $e){PortalSecurity::json(['error'=>'Access was not granted. Sign in with a current Repostit admin account and check whether this creator already has annual access.'],400);}
    }
}
