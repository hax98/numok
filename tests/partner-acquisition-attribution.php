<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';

use Numok\Services\PartnerAcquisitionAttribution;

$checks = 0;
$assert = static function (bool $condition, string $message) use (&$checks): void {
    $checks++;
    if (!$condition) {
        throw new RuntimeException($message);
    }
};

$query = [
    'utm_source' => 'linkedin',
    'utm_medium' => 'organic_social',
    'utm_campaign' => 'partner_recruitment_20261021',
    'utm_content' => 'founder_post',
    'gclid' => 'must-not-persist',
    'next' => 'https://attacker.example',
];
$session = [];
PartnerAcquisitionAttribution::capture($query, $session);
$fields = PartnerAcquisitionAttribution::databaseFields($session);
$assert($fields['acquisition_utm_source'] === 'linkedin', 'Campaign source is saved to the partner attribution fields');
$assert($fields['acquisition_utm_campaign'] === 'partner_recruitment_20261021', 'Campaign name is saved');
$assert($fields['acquisition_utm_content'] === 'founder_post', 'Campaign content is saved');
$assert($fields['acquisition_utm_id'] === null, 'Missing UTM fields remain null');
$assert(!isset($session[PartnerAcquisitionAttribution::SESSION_KEY]['gclid']), 'Click identifiers are not retained');
$assert(!isset($session[PartnerAcquisitionAttribution::SESSION_KEY]['next']), 'Arbitrary query parameters are not retained');

PartnerAcquisitionAttribution::capture([
    'utm_source' => 'another-channel',
    'utm_term' => 'video editors',
], $session);
$afterSecondTouch = PartnerAcquisitionAttribution::databaseFields($session);
$assert($afterSecondTouch['acquisition_utm_source'] === 'linkedin', 'First-touch source is not overwritten');
$assert($afterSecondTouch['acquisition_utm_term'] === 'video editors', 'Later touch fills previously absent UTM fields');

$suffix = PartnerAcquisitionAttribution::querySuffix($query);
$assert(str_starts_with($suffix, '?utm_source=linkedin&'), 'Registration CTA receives an encoded UTM query');
$assert(!str_contains($suffix, 'gclid') && !str_contains($suffix, 'attacker.example'), 'CTA suffix contains only allowlisted UTM fields');
$assert(PartnerAcquisitionAttribution::querySuffix(['utm_source' => ['bad']]) === '', 'Non-string query values are ignored');

echo "Partner acquisition attribution: {$checks} checks passed.\n";
