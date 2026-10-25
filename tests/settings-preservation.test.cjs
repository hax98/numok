const test = require('node:test');
const assert = require('node:assert/strict');
const { execFileSync } = require('node:child_process');
const path = require('node:path');

const php = process.env.PHP_BINARY || 'php';
const fixture = path.join(__dirname, 'fixtures', 'settings-request.php');
const initial = {
  app_name: 'Repostit',
  partner_welcome_message: 'Existing partner instructions',
  stripe_secret_key: 'sk_test_settings_fixture_only',
  stripe_webhook_secret: 'whsec_settings_fixture_only',
};
function request(post, options = {}) {
  return JSON.parse(execFileSync(php, [fixture], {
    input: JSON.stringify({ initial, post, ...options }),
    encoding: 'utf8',
  }));
}
test('saving partner instructions preserves Stripe credentials and the app name', () => {
  const result = request({ partner_welcome_message: 'New partner instructions' });
  assert.deepEqual(result.settings, { ...initial, partner_welcome_message: 'New partner instructions' });
  assert.equal(result.session.settings_success, 'Settings updated successfully.');
});
test('an intentionally empty welcome message clears only that message', () => {
  assert.deepEqual(request({ partner_welcome_message: '' }).settings,
    { ...initial, partner_welcome_message: '' });
});
test('updating Stripe credentials preserves partner instructions and app name', () => {
  assert.deepEqual(request({ stripe_secret_key: 'sk_test_replacement_fixture', stripe_webhook_secret: 'whsec_replacement_fixture' }).settings,
    { ...initial, stripe_secret_key: 'sk_test_replacement_fixture', stripe_webhook_secret: 'whsec_replacement_fixture' });
});
test('blank credential inputs keep existing configuration', () => {
  assert.deepEqual(request({ stripe_secret_key: '', stripe_webhook_secret: '   ' }).settings, initial);
});
test('one credential can rotate while an empty companion input preserves the other', () => {
  assert.deepEqual(request({ stripe_secret_key: '', stripe_webhook_secret: 'whsec_replacement_fixture' }).settings,
    { ...initial, stripe_webhook_secret: 'whsec_replacement_fixture' });
});
test('an empty or unrelated request does not clear settings', () => {
  assert.deepEqual(request({}).settings, initial);
  assert.deepEqual(request({ unrelated_field: 'ignored' }).settings, initial);
});
test('a malformed value rejects the whole request without partially changing settings', () => {
  const result = request({ partner_welcome_message: 'Must not persist', stripe_secret_key: ['unexpected array'] });
  assert.deepEqual(result.settings, initial);
  assert.equal(result.session.settings_error, 'Failed to update settings. Please try again.');
});
test('the settings page never renders saved Stripe credential values', () => {
  const result = request({}, { mode: 'render' });
  assert.ok(!result.html.includes(initial.stripe_secret_key));
  assert.ok(!result.html.includes(initial.stripe_webhook_secret));
  assert.ok(result.html.includes('Secret Key'));
  assert.ok(result.html.includes('Webhook Secret'));
  assert.ok(result.html.includes('Configured'));
  assert.ok(result.html.includes('Leave blank to keep'));
});
test('a fresh installation reports missing credentials without rendering a secret', () => {
  const result = request({}, { mode: 'render', initial: { ...initial, stripe_secret_key: '', stripe_webhook_secret: '' } });
  assert.ok(result.html.includes('Not configured'));
});
