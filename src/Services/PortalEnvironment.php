<?php
declare(strict_types=1);
namespace Numok\Services;

// Production remains the default for the existing deployment. A staging
// deployment must explicitly opt in and can never fall back to live services.
final class PortalEnvironment {
    public static function staging(): bool {
        $environment = getenv('APP_ENV') ?: 'production';
        if (!in_array($environment, ['production', 'staging'], true)) {
            throw new \RuntimeException('Unsupported portal environment.');
        }
        return $environment === 'staging';
    }
    public static function firebaseProject(): string {
        return self::staging() ? 'repostit-dev' : 'repostit-91b0e';
    }
    public static function appUrl(): string {
        return self::staging() ? 'https://preview.repostit.io' : 'https://app.repostit.io';
    }
    public static function referralHosts(): array {
        return self::staging() ? ['preview.repostit.io'] : ['repostit.io','www.repostit.io','app.repostit.io'];
    }
    public static function functionUrl(string $name): string {
        if (!in_array($name, ['partnerPortalBridge', 'getCreatorPerformance', 'manageComplimentaryPublishingAccess'], true)) {
            throw new \InvalidArgumentException('Unknown portal function.');
        }
        return 'https://us-central1-' . self::firebaseProject() . '.cloudfunctions.net/' . $name;
    }
    public static function baseUrl(): string {
        if (!self::staging()) return 'https://partners.repostit.io';
        $url = rtrim(getenv('APP_URL') ?: '', '/');
        $parts = parse_url($url);
        if (!$parts || ($parts['scheme'] ?? '') !== 'https' ||
            !preg_match('/^[a-z0-9-]+\.up\.railway\.app$/', $parts['host'] ?? '') ||
            array_diff(array_keys($parts), ['scheme', 'host'])) {
            throw new \RuntimeException('An isolated staging portal URL is required.');
        }
        return $url;
    }
    public static function firebaseConfig(): array {
        if (!self::staging()) return [
            'apiKey'=>'AIzaSyBadVKoP1A-ZTF5vsh_VL-YkMbAWoNMpX0',
            'authDomain'=>'repostit-91b0e.firebaseapp.com',
            'projectId'=>'repostit-91b0e',
            'appId'=>'1:286284314864:web:f0253867299d62cf672a35',
        ];
        $key = getenv('PARTNER_FIREBASE_API_KEY') ?: '';
        $app = getenv('PARTNER_FIREBASE_APP_ID') ?: '';
        if (!$key || !$app) throw new \RuntimeException('Staging Firebase web configuration is required.');
        return ['apiKey'=>$key, 'authDomain'=>'repostit-dev.firebaseapp.com', 'projectId'=>'repostit-dev', 'appId'=>$app];
    }
}
