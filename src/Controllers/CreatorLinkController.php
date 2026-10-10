<?php
declare(strict_types=1);
namespace Numok\Controllers;
use Numok\Database\Database;
use Numok\Services\PortalEnvironment;
class CreatorLinkController extends Controller {
    public function content(string $token): void {
        $row=Database::query("SELECT c.token,pp.id,pp.tracking_code,p.landing_page FROM creator_content c JOIN partner_programs pp ON pp.id=c.partner_program_id JOIN programs p ON p.id=pp.program_id JOIN partners partner ON partner.id=pp.partner_id WHERE c.token=? AND pp.status='active' AND p.status='active' AND partner.status='active'",[$token])->fetch();
        $this->redirect($row ?: null, true);
    }
    public function affiliate(string $code): void {
        $row=Database::query("SELECT pp.id,pp.tracking_code,p.landing_page FROM partner_programs pp JOIN programs p ON p.id=pp.program_id JOIN partners partner ON partner.id=pp.partner_id WHERE pp.tracking_code=? AND pp.status='active' AND p.status='active' AND partner.status='active'",[$code])->fetch();
        if (!$row) $row=Database::query("SELECT pp.id,pp.tracking_code,p.landing_page FROM creator_tracking_aliases a JOIN partner_programs pp ON pp.id=a.partner_program_id JOIN programs p ON p.id=pp.program_id JOIN partners partner ON partner.id=pp.partner_id WHERE a.tracking_code=? AND pp.status='active' AND p.status='active' AND partner.status='active'",[$code])->fetch();
        $this->redirect($row ?: null, false);
    }
    private function redirect(?array $row, bool $isContent): void {
        if (!$row) { http_response_code(404); exit('This referral link is not active.'); }
        $method=$_SERVER['REQUEST_METHOD']??'GET';
        if (!in_array($method,['GET','HEAD'],true)) { http_response_code(405); exit; }
        $target=$row['landing_page'] ?: PortalEnvironment::appUrl().'/'; $parts=parse_url($target);
        if (($parts['scheme']??'')!=='https' || !in_array(strtolower($parts['host']??''),PortalEnvironment::referralHosts(),true) || isset($parts['user']) || isset($parts['pass'])) { http_response_code(503); exit('Referral destination unavailable.'); }
        // ref is the app's explicit signup-code path; via remains consented legacy attribution.
        $params=['ref'=>$row['tracking_code'],'via'=>$row['tracking_code'],'utm_source'=>'creator','utm_medium'=>'affiliate','utm_campaign'=>'repostit_partners'];
        if ($isContent) $params['utm_content']=$row['token'];
        // Link previews are not visitor clicks; GET counts remain visits, not unique people.
        if ($method==='GET' && !preg_match('/bot|crawler|spider|facebookexternalhit|preview|headless/i',$_SERVER['HTTP_USER_AGENT']??'')) {
            Database::insert('clicks',['partner_program_id'=>$row['id'],'click_id'=>bin2hex(random_bytes(16)),
                'ip_address'=>$_SERVER['REMOTE_ADDR']??null,'user_agent'=>substr($_SERVER['HTTP_USER_AGENT']??'',0,500),
                'referer'=>substr($_SERVER['HTTP_REFERER']??'',0,255),'sub_ids'=>$isContent?json_encode(['sid'=>$row['token'],'utm_content'=>$row['token']]):null]);
        }
        $fragment=''; if (str_contains($target,'#')) { [$target,$fragment]=explode('#',$target,2); $fragment='#'.$fragment; }
        $query=[]; parse_str($parts['query']??'', $query);
        $target=explode('?', $target, 2)[0];
        $location=$target.'?'.http_build_query(array_merge($query,$params)).$fragment;
        header('Cache-Control: no-store'); header('Location: '.$location, true,302); exit;
    }
}
