<?php
declare(strict_types=1);
namespace Numok\Controllers;
use Numok\Database\Database;
use Numok\Services\{CreatorHub,CreatorCampaigns,PortalSecurity};
final class CreatorSyncController extends Controller {
    public function sync(): void {
        if(($_SERVER['REQUEST_METHOD']??'')!=='POST')PortalSecurity::json(['error'=>'POST required'],405);
        $expected=getenv('PARTNER_PORTAL_BRIDGE_KEY')?:'';$provided=$_SERVER['HTTP_X_PARTNER_BRIDGE_KEY']??'';
        if(strlen($expected)<32||!hash_equals($expected,$provided))PortalSecurity::json(['error'=>'Unauthorized'],401);
        session_write_close();
        $ids=Database::query("SELECT DISTINCT pp.partner_id FROM partner_programs pp JOIN partners p ON p.id=pp.partner_id WHERE p.status='active' AND pp.status='active' ORDER BY pp.partner_id")->fetchAll();
        $synced=0;$failed=0;$analyticsSynced=0;$analyticsFailed=0;
        foreach($ids as $row){
            try{CreatorHub::refreshReferrals((int)$row['partner_id']);$synced++;}
            catch(\Throwable $e){$failed++;error_log('Creator scheduled sync unavailable for partner '.(int)$row['partner_id']);}
            try{CreatorHub::refreshAnalytics((int)$row['partner_id']);$analyticsSynced++;}
            catch(\Throwable $e){$analyticsFailed++;error_log('Creator analytics sync unavailable for partner '.(int)$row['partner_id']);}
            CreatorCampaigns::reconcile((int)$row['partner_id']);CreatorCampaigns::refreshActions((int)$row['partner_id']);
        }
        PortalSecurity::json(['synced'=>$synced,'failed'=>$failed,'analyticsSynced'=>$analyticsSynced,'analyticsFailed'=>$analyticsFailed,'reminders'=>'in_portal_only','automaticPayments'=>false],($failed||$analyticsFailed)?503:200);
    }
}
