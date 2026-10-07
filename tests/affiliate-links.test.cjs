const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const source = (name) => fs.readFileSync(path.join(__dirname, '..', name), 'utf8');

test('dashboard and program page both use the canonical affiliate link builder', () => {
  for (const name of ['src/Views/partner/creator/index.php', 'src/Views/partner/programs/index.php']) {
    assert.match(source(name), /CreatorRules::affiliateLink\(/);
  }
  assert.doesNotMatch(source('src/Views/partner/programs/index.php'), /\$trackingSeparator|\$landingPage\s*\./);
});

test('Programs displays and copies the same generated affiliate link', () => {
  const view = source('src/Views/partner/programs/index.php');
  assert.match(view, /htmlspecialchars\(\$trackingUrl, ENT_QUOTES, 'UTF-8'\)/);
  assert.match(view, /json_encode\(\$trackingUrl\)/);
  assert.match(view, /Your affiliate link/);
  assert.doesNotMatch(view, /Existing links stop working/);
});

test('canonical share link does not replace content links or old short-link aliases', () => {
  const rules = source('src/Services/CreatorRules.php');
  assert.match(rules, /partners\.repostit\.io\/r\/.*\$token/);
  assert.match(rules, /partners\.repostit\.io\/go\/.*rawurlencode\(\$code\)/);
  assert.match(source('src/Controllers/CreatorLinkController.php'), /creator_tracking_aliases/);
});
