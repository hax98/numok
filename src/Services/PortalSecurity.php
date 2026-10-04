<?php
declare(strict_types=1);
namespace Numok\Services;
final class PortalSecurity {
    public static function token(): string {
        return $_SESSION['creator_csrf'] ??= bin2hex(random_bytes(32));
    }
    public static function csrf(): string { return self::token(); }
    public static function requirePost(): void {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { http_response_code(405); exit('POST required'); }
        $value = $_POST['csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!is_string($value) || !hash_equals(self::token(), $value)) { http_response_code(403); exit('Please refresh this page and try again.'); }
    }
    public static function json(array $data, int $status = 200): void {
        http_response_code($status); header('Content-Type: application/json'); header('Cache-Control: no-store');
        echo json_encode($data, JSON_THROW_ON_ERROR); exit;
    }
    public static function postJson(string $url, array $data, array $headers = [], int $timeout = 35): array {
        $context = stream_context_create(['http'=>['method'=>'POST', 'header'=>implode("\r\n", array_merge(['Content-Type: application/json'], $headers)),
            'content'=>json_encode($data, JSON_THROW_ON_ERROR), 'timeout'=>$timeout, 'ignore_errors'=>true,'follow_location'=>0],
            'ssl'=>['verify_peer'=>true,'verify_peer_name'=>true]]);
        $raw = @file_get_contents($url, false, $context);
        $body = json_decode($raw ?: '', true);
        if (!is_array($body) || isset($body['error'])) throw new \RuntimeException('The connection could not be verified. Please sign in again or retry later.');
        return $body;
    }
}
