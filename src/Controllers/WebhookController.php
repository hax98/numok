<?php
declare(strict_types=1);
namespace Numok\Controllers;
use Numok\Database\Database;
use Numok\Services\CreatorRules;

/** Receipts, not checkouts or free trials, create commission. */
class WebhookController extends Controller {
    public function stripeWebhook(): void {
        $secret=Database::query("SELECT value FROM settings WHERE name='stripe_webhook_secret' LIMIT 1")->fetchColumn();
        if (!$secret) { http_response_code(503); return; }
        try {
            $event=\Stripe\Webhook::constructEvent(file_get_contents('php://input'),$_SERVER['HTTP_STRIPE_SIGNATURE']??'',(string)$secret);
            if (!$event->livemode) { http_response_code(200); return; }
            $object=$event->data->object;
            switch ($event->type) {
                case 'invoice.paid': $this->invoice($object); break;
                case 'checkout.session.completed':
                    if (($object->mode??'')==='payment' && ($object->payment_status??'')==='paid')
                        $this->receipt($object,$object->payment_intent??null,(int)($object->amount_total??0),$object->metadata??(object)[],null,$object->customer_details->email??null);
                    break;
                case 'payment_intent.succeeded':
                    if (empty($object->invoice)) $this->receipt($object,$object->id,(int)($object->amount_received??0),$object->metadata??(object)[],null,$object->receipt_email??null);
                    break;
                case 'charge.refunded': $this->refund($object); break;
            }
            http_response_code(200);
        } catch (\UnexpectedValueException|\Stripe\Exception\SignatureVerificationException $e) {
            http_response_code(400);
        } catch (\Throwable $e) {
            // Never log payloads, signatures, email addresses or credentials.
            error_log('Affiliate receipt processing failed: '.get_class($e));
            http_response_code(500);
        }
    }
    private function invoice(object $invoice): void {
        if ((int)($invoice->amount_paid??0)<=0 || ($invoice->status??'')!=='paid') return;
        $sub=$invoice->subscription??$invoice->parent->subscription_details->subscription??null;
        if (is_object($sub)) $sub=$sub->id??null;
        $metadata=$invoice->subscription_details->metadata??$invoice->parent->subscription_details->metadata??$invoice->metadata??(object)[];
        if (!CreatorRules::attribution($metadata)['tracking_code'] && $sub) {
            $key=Database::query("SELECT value FROM settings WHERE name='stripe_secret_key' LIMIT 1")->fetchColumn();
            if (!$key) throw new \RuntimeException('Stripe receipt lookup unavailable.');
            $metadata=(new \Stripe\StripeClient((string)$key))->subscriptions->retrieve($sub,[])->metadata??(object)[];
        }
        $this->receipt($invoice,$invoice->id,(int)$invoice->amount_paid,$metadata,$sub,$invoice->customer_email??null);
    }
    private function receipt(object $object, ?string $paymentId, int $cents, object $metadata, ?string $subscription, ?string $email): void {
        if (!$paymentId || $cents<=0) return;
        $attribution=CreatorRules::attribution($metadata);
        if (!$attribution['tracking_code']) return;
        $program=Database::query("SELECT pp.*,p.reward_days,p.is_recurring,p.commission_type,p.commission_value,partner.email partner_email FROM partner_programs pp JOIN programs p ON p.id=pp.program_id JOIN partners partner ON partner.id=pp.partner_id WHERE pp.tracking_code=? AND pp.status='active' AND p.status='active' AND partner.status='active' LIMIT 1",[$attribution['tracking_code']])->fetch();
        if (!$program || ($subscription && !$program['is_recurring'])) return;
        if ($email && strcasecmp(trim($email),trim($program['partner_email']))===0) return;
        $customer=CreatorRules::customerKey($object);
        if (!$customer && !empty($metadata->userId)) $customer='uid:'.hash('sha256',(string)$metadata->userId);
        $ownUid=Database::query('SELECT firebase_uid FROM creator_profiles WHERE partner_id=?',[$program['partner_id']])->fetchColumn();
        if ($ownUid && ($metadata->userId??null)===$ownUid) return;
        $content=$attribution['content_token'];
        if ($content && !Database::query('SELECT id FROM creator_content WHERE token=? AND partner_program_id=?',[$content,$program['id']])->fetch()) $content=null;
        $amount=$cents/100; $currency=strtolower((string)($object->currency??''));
        if (!preg_match('/^[a-z]{3}$/',$currency)) throw new \RuntimeException('Missing receipt currency.');
        $commission=$program['commission_type']==='percentage' ? round($amount*(float)$program['commission_value']/100,2) : (float)$program['commission_value'];
        $meta=['subscription_id'=>$subscription,'payment_intent'=>$object->payment_intent??(($object->object??'')==='payment_intent'?$object->id:null),'sid'=>$content,'utm_content'=>$content];
        Database::query('INSERT INTO conversions (partner_program_id,stripe_payment_id,amount,commission_amount,status,customer_email,metadata,customer_key,content_token,currency) VALUES (?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE stripe_payment_id=stripe_payment_id',
            [$program['id'],$paymentId,$amount,$commission,$program['reward_days']>0?'pending':'payable',$email,json_encode($meta),$customer,$content,$currency]);
    }
    private function refund(object $charge): void {
        $intent=$charge->payment_intent??null; if (!$intent) return;
        $original=(int)($charge->amount??0); $refunded=(int)($charge->amount_refunded??0);
        if (!$original || !$refunded) return;
        $rows=Database::query("SELECT * FROM conversions WHERE stripe_payment_id=? OR JSON_UNQUOTE(JSON_EXTRACT(metadata,'$.payment_intent'))=?",[$intent,$intent])->fetchAll();
        foreach ($rows as $row) {
            $meta=json_decode($row['metadata']??'{}',true)?:[];
            $base=(float)($meta['original_amount']??$row['amount']); $baseCommission=(float)($meta['original_commission']??$row['commission_amount']);
            $meta['original_amount']=$base; $meta['original_commission']=$baseCommission; $meta['refunded_cents']=$refunded;
            $fraction=max(0,1-$refunded/$original);
            if ($row['status']==='paid') $meta['paid_refund_requires_review']=true;
            Database::update('conversions',['amount'=>round($base*$fraction,2),'commission_amount'=>round($baseCommission*$fraction,2),
                'status'=>$fraction===0.0?'rejected':$row['status'],'metadata'=>json_encode($meta)],'id=?',[$row['id']]);
        }
    }
}
