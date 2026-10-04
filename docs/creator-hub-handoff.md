# Repostit creator workspace, 2026-10-04

## Implemented and deployed

- Signup transaction creates the partner and joins the public program after explicit terms acceptance; the next screen contains the referral link.
- Creator dashboard: general/customizable affiliate link, dedicated links per content placement, public post URL submission, profile URLs, signups, unique paying subscribers, USD earnings and payout history.
- Reuses social connections in Repostit. The creator authenticates their own existing Firebase account and consents to importing profile/available analytics. ID tokens are verified server-side; provider tokens are not copied into the portal.
- Admin Creator Hub: account linkage, real annual-access approval, submitted promotional content verification, signup refresh, and audited bonus transfer recording. Recording a transfer does not execute a bank payment.
- Bonuses: USD 200 at 20 distinct paying subscribers, plus an ADDITIONAL USD 5,000 at 500, USD 5,200 total. Recurring 20% commission remains separate. Trials, free accounts, renewals and rejected/refunded/disputed payments do not create extra subscribers.
- Existing tracking codes remain valid aliases after customization. Redirects preserve attribution and add a content token where applicable.
- Billing/referral sync reads the existing Repostit Firebase and Stripe records through a secret-authenticated bridge. Hourly Cloud Scheduler job is ENABLED in Europe/Rome. No Stripe secret is exposed to PHP or browser clients.
- Additive MySQL migrations and persistent portal sessions; existing users and subscriptions are preserved.

## Verification

- Railway latest verified SUCCESS deployment: df1aada3-f66a-4f2a-9a69-0fbf60def55e.
- Firebase codebase repostit-partners deployed successfully: partnerPortalBridge and syncPartnerPortal only.
- Authenticated integrated Browser: admin Creator Hub and live read-only creator preview loaded successfully. Mobile 390px and desktop 1280px layouts showed no horizontal overflow in local read-only preview.
- PHP syntax check: 60 files passed before final commission-maturity/earnings amendments; both amended files passed direct PHP lint on the live deployment. The full final rerun hit a Railway SSH connection reset; the isolated ledger still passed against the final deployment.
- Pure PHP rules: 30 checks passed. Node bridge rules: 7 passed. Production MySQL TEMPORARY-table ledger test: 10 checks passed, no persistent fixture writes. Includes distinct customers, renewal commission, duplicate invoice idempotency, refund, dispute, creator isolation and commission maturity.
- Actual protected sync endpoint returned synced=6, failed=0. Unauthenticated POST returns 401. General affiliate HEAD redirect returned 302 with the correct via parameter, without adding a visit.
- Existing persistent conversion count was zero. No simulated revenue, bonus payment, or promotional publication was entered.

## Explicit boundaries

- Existing affiliates must link their Repostit account themselves. Joining alone does not grant complimentary publishing access or create agreed sponsored deliverables.
- Analytics cover matching posts published through Repostit only, for metrics made available by the provider. Arbitrary native posts and unavailable metrics are not silently counted as tracked/zero. Existing provider analytics does not support YouTube insights.
- Content-specific attribution requires using that content's dedicated link. A generic bio link cannot identify the video viewed; this is link-based attribution, not view-through attribution.
- Account synchronization imports analytics on creator refresh; hourly job refreshes referrals/billing, not social-provider analytics.
- USD balances are displayed in the creator summary. Genuine non-USD subscribers can count toward bonuses, but their currency amounts are not added to a USD balance without conversion.
- Existing 60-day recurring commission validation delay is preserved; bonus eligibility uses the stated distinct verified-paying-subscriber rule, not an added 60-day creator requirement.
- No new creator registration, creator OAuth session, complimentary entitlement grant, live checkout or bank transfer was exercised during this rollout. Their authenticated routes are implemented, but a complete creator-operated end-to-end pilot remains necessary.
- Portal Stripe webhook configuration remains absent. Automatic verified bridge reconciliation is the deployed ingestion path; do not register another unconfigured webhook or duplicate receipts.

## Operations

Open https://partners.repostit.io/admin/creators for creator operations. A creator opens https://partners.repostit.io/dashboard, links their existing Repostit account, creates a placement link and submits the public promotional URL. Verify actual content separately from product publishing activity, then compare attributed paying customers. Record milestone transfers only after the actual transfer has been completed.

Deploy PHP via Railway from this repository. Deploy only the isolated bridge with `firebase deploy --only functions:repostit-partners --project repostit-91b0e --config firebase.bridge.json`; do not deploy the unrelated dirty main Repostit worktree.
