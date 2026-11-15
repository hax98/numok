<?php
declare(strict_types=1);
namespace Numok\Services;

/** Display helpers for partner-facing pages: app names, money and dates in plain words. */
final class PortalFormat {
    private const APPS = ['instagram'=>'Instagram','tiktok'=>'TikTok','youtube'=>'YouTube','facebook'=>'Facebook',
        'linkedin'=>'LinkedIn','x'=>'X','threads'=>'Threads','pinterest'=>'Pinterest','google'=>'Google'];

    public static function app(?string $platform): string {
        $key = strtolower(trim((string) $platform));
        return self::APPS[$key] ?? ucfirst($key);
    }

    /** $200 for whole amounts, $12.50 otherwise. */
    public static function money($value): string {
        $v = (float) $value;
        return '$' . (fmod($v, 1.0) == 0.0 ? number_format($v) : number_format($v, 2));
    }

    /** "4 minutes ago", "3 hours ago", "yesterday", otherwise "15 Nov 2026". Input is a UTC datetime string. */
    public static function ago(?string $utc, ?int $now = null): string {
        if ($utc === null || trim($utc) === '') return 'never';
        try { $ts = (new \DateTimeImmutable($utc, new \DateTimeZone('UTC')))->getTimestamp(); }
        catch (\Throwable $e) { return $utc; }
        $diff = ($now ?? time()) - $ts;
        if ($diff < 60) return 'just now';
        if ($diff < 3600) { $m = intdiv($diff, 60); return $m . ($m === 1 ? ' minute ago' : ' minutes ago'); }
        if ($diff < 86400) { $h = intdiv($diff, 3600); return $h . ($h === 1 ? ' hour ago' : ' hours ago'); }
        if ($diff < 172800) return 'yesterday';
        return gmdate('j M Y', $ts);
    }

    /** "15 Nov 2026" for dates such as deadlines and payment days. */
    public static function date(?string $utc): string {
        if ($utc === null || trim($utc) === '') return '';
        try { return (new \DateTimeImmutable($utc, new \DateTimeZone('UTC')))->format('j M Y'); }
        catch (\Throwable $e) { return $utc; }
    }
}
