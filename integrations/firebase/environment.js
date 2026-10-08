const PRODUCTION_PROJECT = 'repostit-91b0e';
const STAGING_PROJECT = 'repostit-dev';
function portalUrl(project, configured) {
  const production = 'https://partners.repostit.io/internal/creator-sync';
  if (project === PRODUCTION_PROJECT) {
    if (configured && configured !== production) throw new Error('Production portal endpoint mismatch');
    return production;
  }
  if (project !== STAGING_PROJECT) throw new Error('Unknown partner bridge project');
  let url;
  try { url = new URL(configured); } catch { throw new Error('Staging portal endpoint required'); }
  if (url.protocol !== 'https:' || !/^[a-z0-9-]+\.up\.railway\.app$/.test(url.hostname) ||
    url.username || url.password || url.port || url.search || url.hash || url.pathname !== '/internal/creator-sync') {
    throw new Error('Isolated staging portal endpoint required');
  }
  return url.href;
}
function stripeKey(project, key) {
  // Keep existing production credential handling unchanged; the new fence
  // applies to staging only, which may never read live billing data.
  if (project === PRODUCTION_PROJECT && typeof key === 'string' && key) return key;
  if (project !== STAGING_PROJECT || typeof key !== 'string' || !key.startsWith('sk_test_')) throw new Error('Partner Stripe environment mismatch');
  return key;
}
module.exports = {portalUrl, stripeKey, STAGING_PROJECT};
