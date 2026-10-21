<?php
declare(strict_types=1);

namespace Numok\Services;

/**
 * Preserves a small, first-touch set of UTM campaign tags through partner
 * registration so the team can compare recruitment channels with referrals.
 */
final class PartnerAcquisitionAttribution
{
    public const SESSION_KEY = 'partner_acquisition_utm';

    private const FIELDS = [
        'utm_source' => 'acquisition_utm_source',
        'utm_medium' => 'acquisition_utm_medium',
        'utm_campaign' => 'acquisition_utm_campaign',
        'utm_content' => 'acquisition_utm_content',
        'utm_term' => 'acquisition_utm_term',
        'utm_id' => 'acquisition_utm_id',
    ];

    /**
     * Keep the first non-empty value for each standard UTM field in the
     * server-side session. No click IDs or arbitrary query parameters persist.
     */
    public static function capture(array $query, array &$session): void
    {
        $incoming = self::sanitize($query);
        if ($incoming === []) {
            return;
        }

        $existing = isset($session[self::SESSION_KEY]) && is_array($session[self::SESSION_KEY])
            ? self::sanitize($session[self::SESSION_KEY])
            : [];
        $session[self::SESSION_KEY] = $existing + $incoming;
    }

    /** Build an encoded query suffix for landing-page registration CTAs. */
    public static function querySuffix(array $query): string
    {
        $values = self::sanitize($query);
        return $values === [] ? '' : '?' . http_build_query($values, '', '&', PHP_QUERY_RFC3986);
    }

    /** Map captured session tags to the partners table's attribution columns. */
    public static function databaseFields(array $session): array
    {
        $values = isset($session[self::SESSION_KEY]) && is_array($session[self::SESSION_KEY])
            ? self::sanitize($session[self::SESSION_KEY])
            : [];
        $fields = array_fill_keys(array_values(self::FIELDS), null);
        foreach (self::FIELDS as $utmKey => $column) {
            if (isset($values[$utmKey])) {
                $fields[$column] = $values[$utmKey];
            }
        }
        return $fields;
    }

    private static function sanitize(array $query): array
    {
        $clean = [];
        foreach (self::FIELDS as $utmKey => $_column) {
            if (!isset($query[$utmKey]) || !is_string($query[$utmKey])) {
                continue;
            }

            $value = trim($query[$utmKey]);
            $value = preg_replace('/[^A-Za-z0-9._~ -]/', '', $value) ?? '';
            $value = substr($value, 0, 100);
            if ($value !== '') {
                $clean[$utmKey] = $value;
            }
        }
        return $clean;
    }
}
