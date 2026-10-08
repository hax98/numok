const CONNECT_FAILURES = new Set(['EAI_AGAIN', 'ENOTFOUND', 'ECONNREFUSED', 'UND_ERR_CONNECT_TIMEOUT']);
const wait = ms => new Promise(resolve => setTimeout(resolve, ms));

// Only retry known failures before a connection is established. A dropped
// socket or response timeout may have reached the portal: do not replay it.
async function syncPortal({ key, url = 'https://partners.repostit.io/internal/creator-sync', fetchImpl = fetch, waitImpl = wait, now = Date.now,
  logger = console, timeoutMs = 500000 }) {
  if (typeof key !== 'string' || !key) throw new Error('Partner portal sync key unavailable');
  const deadline = now() + timeoutMs;
  let response;
  for (let attempt = 1; attempt <= 3; attempt++) {
    const remaining = deadline - now();
    if (remaining <= 0) throw new Error('Partner portal sync deadline exceeded');
    try {
      response = await fetchImpl(url, {
        method: 'POST', redirect: 'error',
        headers: { 'X-Partner-Bridge-Key': key, 'Content-Type': 'application/json' },
        body: '{}', signal: AbortSignal.timeout(Math.ceil(remaining)),
      });
      break;
    } catch (error) {
      const rawCode = error?.cause?.code || error?.code;
      const code = CONNECT_FAILURES.has(rawCode) ? rawCode : 'NETWORK_ERROR';
      const delay = attempt * 500;
      const retry = CONNECT_FAILURES.has(rawCode) && attempt < 3 && now() + delay < deadline;
      logger.warn('Partner portal sync transport failure', { attempt, code, retry });
      if (!retry) throw new Error(`Partner portal sync transport failed (${code})`);
      await waitImpl(delay);
    }
  }
  // Never log provider bodies or credentials. Non-2xx and partial responses
  // need an explicit later sync, not an immediate replay of completed work.
  if (!response.ok) throw new Error(`Partner portal sync HTTP ${response.status}`);
  let result;
  try { result = await response.json(); }
  catch { throw new Error('Partner portal sync returned invalid JSON'); }
  const count = value => Number.isSafeInteger(value) && value >= 0;
  if (!result || !count(result.synced) || !count(result.failed)) {
    throw new Error('Partner portal sync returned invalid counts');
  }
  const summary = { synced: result.synced, failed: result.failed };
  for (const field of ['analyticsSynced', 'analyticsFailed', 'analyticsSkipped']) {
    if (result[field] === undefined) continue;
    if (!count(result[field])) throw new Error('Partner portal sync returned invalid counts');
    summary[field] = result[field];
  }
  if (summary.failed || summary.analyticsFailed) throw new Error('Partner portal sync incomplete');
  return summary;
}
module.exports = { syncPortal };
