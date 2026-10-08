<?php
declare(strict_types=1);
namespace Numok\Services;

final class CreatorRules {
    public const MILESTONES = [20 => 200, 500 => 5000];
    public const BONUS_TERMS = "Creator bonuses\nEarn a USD 200 bonus after 20 distinct genuine paying subscribers and an additional USD 5,000 bonus after 500, USD 5,200 total. Free signups, renewals, self-referrals, refunded payments and disputed payments do not count as extra qualifying subscribers. Repostit reviews the milestone and sends the bonus by bank transfer, separately from recurring commission. Approved invited creators also receive one year of Influencer publishing access; joining the affiliate program alone is not approval for sponsored content or complimentary access.";
    public const PLATFORMS = ['instagram','tiktok','youtube','facebook','linkedin','x','threads','pinterest'];
    public static function milestones(int $customers, array $payouts): array {
        return array_map(static function ($threshold) use ($customers, $payouts) {
            $paid = ($payouts[$threshold]['status'] ?? '') === 'paid';
            return ['threshold' => $threshold, 'amount' => self::MILESTONES[$threshold],
                'count' => min($threshold, max(0, $customers)), 'percent' => min(100, 100 * max(0, $customers) / $threshold),
                'status' => $paid ? 'paid' : ($customers >= $threshold ? 'earned' : 'locked'),
                'remaining' => max(0, $threshold - $customers), 'payment' => $payouts[$threshold] ?? null];
        }, array_keys(self::MILESTONES));
    }
    public static function socialUrl(string $value, string $platform): string {
        $hosts = ['instagram'=>['instagram.com','www.instagram.com'], 'tiktok'=>['tiktok.com','www.tiktok.com'],
            'youtube'=>['youtube.com','www.youtube.com','youtu.be'], 'facebook'=>['facebook.com','www.facebook.com','m.facebook.com'],
            'linkedin'=>['linkedin.com','www.linkedin.com'], 'x'=>['x.com','www.x.com','twitter.com','www.twitter.com'],
            'threads'=>['threads.net','www.threads.net','threads.com','www.threads.com'], 'pinterest'=>['pinterest.com','www.pinterest.com','pin.it']];
        $parts = parse_url(trim($value));
        if (!$parts || ($parts['scheme'] ?? '') !== 'https' || isset($parts['user']) || isset($parts['pass']) || isset($parts['port']) ||
            !in_array(strtolower($parts['host'] ?? ''), $hosts[$platform] ?? [], true) || strlen($value) > 500) {
            throw new \InvalidArgumentException('Use an HTTPS profile or post URL from the selected platform.');
        }
        return trim($value);
    }
    public static function attribution(object $metadata): array {
        return ['tracking_code' => $metadata->numok_tracking_code ?? $metadata->via ?? $metadata->referral_via ?? null,
            'content_token' => $metadata->utm_content ?? $metadata->referral_utm_content ?? $metadata->numok_sid ?? null];
    }
    public static function customerKey(object $object): ?string {
        $customer = $object->customer ?? null;
        if (is_object($customer)) $customer = $customer->id ?? null;
        if (is_string($customer) && preg_match('/^cus_[A-Za-z0-9]+$/', $customer)) return $customer;
        $user = $object->metadata->userId ?? null;
        return is_string($user) && $user !== '' ? 'uid:' . hash('sha256', $user) : null;
    }
    public static function link(string $token): string {
        if (!preg_match('/^[a-f0-9]{24}$/', $token)) throw new \InvalidArgumentException('Invalid content token.');
        return PortalEnvironment::baseUrl() . '/r/' . $token;
    }
    public static function affiliateLink(string $code): string {
        return PortalEnvironment::baseUrl() . '/go/' . rawurlencode($code);
    }
}
