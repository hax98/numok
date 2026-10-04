<?php
use Numok\Services\CreatorRules;
$escape=static fn($v)=>htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');
$program=$programs[0]??null;
$linked=!empty($profile['firebase_uid']);
$snapshot=$profile['analytics_snapshot']??[];
$account=$profile['account_snapshot']??[];
$connections=$snapshot['connections']??[];
$csrfField='<input type="hidden" name="csrf" value="'.$escape($csrf).'">';
$money=static fn($v)=>'$'.number_format((float)$v,2);
?>
<link rel="stylesheet" href="/assets/css/creator-hub.css?v=sharing-cards-20261004">
<div class="creator-hub">
 <?php if($readOnly): ?><div class="ch-notice">Read-only creator workspace preview. No account, content or payout actions can be submitted from this view.</div><?php endif; ?>
 <div class="ch-top"><div><div class="ch-kicker">YOUR CREATOR WORKSPACE</div><h1>Hello, <?= $escape(explode(' ',$partner['contact_name'])[0]) ?>.</h1><p class="ch-muted">Your content, your referrals, your recurring income. One place to keep it moving.</p></div><span class="ch-badge">Repostit Partners</span></div>
 <?php foreach(['success','error'] as $notice): if(!empty($_SESSION[$notice])): ?><div class="ch-notice <?= $notice ?>" role="status"><?= $escape($_SESSION[$notice]) ?></div><?php unset($_SESSION[$notice]);endif;endforeach; ?>
 <?php if($syncError): ?><div class="ch-notice error" role="status"><?= $escape($syncError) ?></div><?php endif; ?>
 <div class="ch-two">
  <section class="ch-card accent" aria-labelledby="link-heading"><div class="ch-kicker">START HERE</div><h2 id="link-heading">Your recommendation has a home.</h2>
   <?php if($program): $link='https://partners.repostit.io/go/'.rawurlencode($program['tracking_code']); ?>
    <p class="ch-muted">Share your link in your bio, a Story link sticker, or a DM someone has asked for.</p>
    <div class="ch-linkfield"><input aria-label="Your affiliate link" readonly value="<?= $escape($link) ?>"><button class="ch-button" type="button" data-copy="<?= $escape($link) ?>">Copy link</button></div>
    <div class="ch-row" style="margin-top:18px"><span class="ch-badge">20% recurring while referrals stay subscribed</span><a class="ch-link" href="/programs">Customize your code</a></div>
    <p class="ch-subtle">Use a separate content link below to know which post brought a customer. A shared bio link cannot identify the exact video someone watched.</p>
   <?php else: ?><p class="ch-muted">Join the Repostit program to unlock your personal referral link and start tracking your results.</p><a class="ch-button" href="/programs">Get my affiliate link</a><?php endif; ?>
  </section>
  <section class="ch-card"><h2>Your next steps</h2>
   <div class="ch-step"><span class="ch-stepnum"><?= $program?'✓':'1' ?></span><div><a class="ch-link" href="/programs">Join the program and get your link</a><p>Review the terms, then choose a code that feels like you.</p></div></div>
   <div class="ch-step"><span class="ch-stepnum"><?= $linked?'✓':'2' ?></span><div><a class="ch-link" href="#socials">Link your Repostit account</a><p>Bring in your connected profiles and available analytics.</p></div></div>
   <div class="ch-step"><span class="ch-stepnum">3</span><div><a class="ch-link" href="#brief">Try it, then show your real workflow</a><p>Publish successfully before recommending it to your audience.</p></div></div>
   <div class="ch-step"><span class="ch-stepnum">4</span><div><a class="ch-link" href="#content">Add a content link and published URL</a><p>Measure what works, then make more of that.</p></div></div>
  </section>
 </div>
 <section class="ch-distribution ch-stack" aria-labelledby="share-heading">
  <div><div class="ch-kicker">YOU HAVE YOUR LINK. HERE'S WHERE TO USE IT.</div><h2 id="share-heading">Pick a way to share.</h2><p class="ch-muted">Show how you use Repostit, then give interested people an easy way to try it. Start with one of these.</p></div>
  <div class="ch-share-grid">
   <article class="ch-share-card">
    <div class="ch-share-visual ch-reel-visual" aria-hidden="true"><div class="ch-phone"><div class="ch-phone-notch"></div><div class="ch-video-scene"><span class="ch-play">▶</span><span class="ch-scene-caption">One video. More places.</span></div><div class="ch-mini-comment"><span class="ch-mini-avatar"></span><span>Send me the link?</span></div></div><div class="ch-float-bubble">Link sent <span>↗</span></div></div>
    <span class="ch-share-platform">INSTAGRAM REEL</span><h3>Turn a comment into a conversation.</h3>
    <ol class="ch-share-steps"><li>Show a real workflow in your Reel.</li><li>Invite viewers to comment <strong>LINK</strong> if they want to try it.</li><li>DM that Reel's tracking link to people who ask.</li></ol>
    <p class="ch-share-note">Send it yourself, or set up comment-to-DM in a tool such as Manychat. Repostit does not send these DMs for you.</p>
    <button class="ch-button" type="button" data-placement="instagram-reel" <?= $readOnly||!$program?'disabled':'' ?>>Create a Reel link</button>
   </article>
   <article class="ch-share-card">
    <div class="ch-share-visual ch-story-visual" aria-hidden="true"><div class="ch-phone"><div class="ch-story-progress"><i></i><i></i><i></i></div><div class="ch-story-caption">Here's how I<br>share my videos.</div><div class="ch-story-sticker"><span>↗</span> Try Repostit</div><div class="ch-story-tap">Tap to open</div></div><span class="ch-tap-ring"></span></div>
    <span class="ch-share-platform">INSTAGRAM STORY</span><h3>Give your Story a link sticker.</h3>
    <ol class="ch-share-steps"><li>Share a quick demo or your honest experience.</li><li>Add a link sticker with this Story's tracking link.</li><li>Save it to a Highlight if you want it to stay easy to find.</li></ol>
    <p class="ch-share-note">Use a separate link for each Story placement you want to compare. Check that the sticker opens the right page before sharing.</p>
    <button class="ch-button" type="button" data-placement="instagram-story" <?= $readOnly||!$program?'disabled':'' ?>>Create a Story link</button>
   </article>
   <article class="ch-share-card">
    <div class="ch-share-visual ch-bio-visual" aria-hidden="true"><div class="ch-phone"><div class="ch-bio-avatar">you</div><div class="ch-bio-name">@yourname</div><div class="ch-bio-caption">The tools I actually use.</div><div class="ch-bio-link">↗ Try Repostit</div><div class="ch-mini-grid"><i></i><i></i><i></i></div></div><div class="ch-float-bubble">Your link, in your bio.</div></div>
    <span class="ch-share-platform">TIKTOK / BIO LINK</span><h3>Make the next step easy to find.</h3>
    <ol class="ch-share-steps"><li>Show Repostit in a useful, normal video.</li><li>Put your link in your bio, or add a clear Repostit button to your link page.</li><li>Tell viewers where to find it.</li></ol>
    <p class="ch-share-note">TikTok website links depend on account eligibility. A shared bio link tracks the creator or placement, not the exact video someone watched.</p>
    <button class="ch-button" type="button" data-placement="tiktok-bio" <?= $readOnly||!$program?'disabled':'' ?>>Create a bio link</button>
   </article>
  </div>
  <p class="ch-subtle">Choose a card to prefill the link form below, nothing is published or sent automatically. Tell your audience about the affiliate relationship and any free account you received. <?= $readOnly?'Buttons are available in the creator\'s own dashboard.':(!$program?'Join the program first to create your links.':'') ?></p>
 </section>
 <div class="ch-stats">
  <div class="ch-card"><span class="ch-label">Referral link visits</span><div class="ch-number"><?= number_format($summary['clicks']) ?></div><div class="ch-period">All time, not unique people</div></div>
  <div class="ch-card"><span class="ch-label">Attributed signups</span><div class="ch-number"><?= !empty($profile['referrals_synced_at'])?number_format($summary['signups']):'Not synced' ?></div><div class="ch-period"><?= !empty($profile['referrals_synced_at'])?'Synced '. $escape($profile['referrals_synced_at']).' UTC':'Not the same as paying customers' ?></div></div>
  <div class="ch-card"><span class="ch-label">Paying subscribers</span><div class="ch-number"><?= number_format($summary['paying_customers']) ?></div><div class="ch-period">Unique verified customers, not renewals</div></div>
  <div class="ch-card"><span class="ch-label">Commission earned</span><div class="ch-number"><?= $money($summary['commission']) ?></div><div class="ch-period">USD, rejected payments excluded</div></div>
 </div>
 <div class="ch-two">
  <section class="ch-card" aria-labelledby="milestone-heading"><div class="ch-kicker">EXTRA REWARDS</div><h2 id="milestone-heading">Make your next milestone count.</h2><p class="ch-muted">Your 20% recurring commission continues separately. These bonuses stack, $5,200 in total if both milestones are reached.</p>
   <?php foreach($summary['milestones'] as $m): ?><div class="ch-milestone"><div class="ch-row"><div><strong><?= $money($m['amount']) ?></strong><p class="ch-muted" style="margin:3px 0"><?= $m['threshold']===500?'An additional bonus at 500 paying subscribers':'Your first bonus at 20 paying subscribers' ?></p></div><span class="ch-badge <?= $escape($m['status']) ?>"><?= ['locked'=>'In progress','earned'=>'Earned, awaiting bank transfer','paid'=>'Bank transfer recorded'][$m['status']] ?></span></div>
    <progress class="ch-progress" max="<?= $m['threshold'] ?>" value="<?= $m['count'] ?>" aria-label="Progress toward <?= $m['threshold'] ?> paying subscribers"></progress><div class="ch-row"><span class="ch-label"><?= $m['count'] ?> / <?= $m['threshold'] ?> paying subscribers</span><span class="ch-label"><?= $m['remaining'] ?> to go</span></div>
    <?php if($m['status']==='paid'): ?><p class="ch-subtle">Paid <?= $escape($m['payment']['paid_at']) ?> UTC. Reference: <?= $escape($m['payment']['payment_reference']) ?></p><?php endif; ?></div><?php endforeach; ?>
   <p class="ch-subtle">Trials and free accounts do not count. Renewals do not create extra subscribers. Refunded or rejected payments do not qualify. Bonus transfers are reviewed and made by Repostit, they are not instant automatic payouts.</p>
  </section>
  <section class="ch-card"><h2>Your earnings, clearly.</h2><div class="ch-step"><div style="width:100%"><div class="ch-row"><span>Commission awaiting payout</span><strong><?= $money($summary['unpaid']) ?></strong></div><p>Includes validation-pending and payable commission.</p></div></div>
   <div class="ch-step"><div style="width:100%"><div class="ch-row"><span>Commission paid</span><strong><?= $money($summary['paid']) ?></strong></div></div></div>
   <div class="ch-step"><div style="width:100%"><div class="ch-row"><span>Bonuses awaiting transfer</span><strong><?= $money($summary['bonus_earned']) ?></strong></div></div></div>
   <div class="ch-step"><div style="width:100%"><div class="ch-row"><span>Bonuses paid</span><strong><?= $money($summary['bonus_paid']) ?></strong></div></div></div>
   <a class="ch-link" href="/earnings" style="display:inline-block;margin-top:16px">See payment-by-payment commission history</a><p class="ch-subtle">Check your payment contact in <a class="ch-link" href="/settings">Settings</a>. Bank details are arranged privately with Repostit, never submitted through a public content link.</p>
  </section>
 </div>
 <section class="ch-card ch-stack" id="socials"><div class="ch-row"><div><div class="ch-kicker">YOUR SOCIAL ACCOUNTS</div><h2>Connect once. Know what is working.</h2><p class="ch-muted">Connect your social platforms in Repostit, then link that Repostit account here. Provider permissions and available metrics still apply.</p></div><a class="ch-button secondary" target="_blank" rel="noopener" href="https://app.repostit.io/connections">Connect social accounts in Repostit</a></div>
  <?php if($linked): ?><div class="ch-plan">Linked Repostit account: <strong><?= $escape($profile['repostit_email']) ?></strong>. Last snapshot: <?= $escape($profile['synced_at']) ?> UTC.
   <?php if(!empty($account['influencerExpiresAt'])): ?><p>Influencer access verified until <?= $escape($account['influencerExpiresAt']) ?>.</p><?php else: ?><p>Your promised annual Influencer access is not yet verified here. <a class="ch-link" href="mailto:support@repostit.io">Contact Hax with your Repostit account email</a>.</p><?php endif; ?></div>
   <?php foreach($connections as $connection): ?><div class="ch-profile ch-row"><div><strong><?= $escape($connection['name']??'Connected account') ?></strong> <span class="ch-muted"> · <?= $escape(ucfirst($connection['platform']??'')) ?></span></div><span class="ch-badge"><?= $escape($connection['authorizationRequired']??false?'Analytics authorization needed':($connection['insightsStatus']??'Not checked')) ?></span></div><?php endforeach; ?>
   <?php if(!$connections): ?><p class="ch-muted">No supported connections in the latest snapshot. Connect your accounts in Repostit, then refresh.</p><?php endif; ?>
  <?php endif; ?>
  <?php foreach($socials as $social): ?><div class="ch-profile"><a class="ch-link" href="<?= $escape($social['profile_url']) ?>" target="_blank" rel="noopener noreferrer"><?= $escape($social['display_name']) ?> · <?= $escape(ucfirst($social['platform'])) ?></a><span class="ch-muted"> · Profile supplied by you, not OAuth-verified</span></div><?php endforeach; ?>
  <?php if(!$readOnly): ?>
  <div id="repostit-linker" data-csrf="<?= $escape($csrf) ?>" data-linked="<?= $linked?'true':'false' ?>">
   <label class="ch-consent"><input type="checkbox" id="link-consent" <?= $linked?'checked':'' ?>>I authorize Repostit Partners to link this Repostit account and display its public post metrics, connected account names and Influencer-access status.</label>
   <div class="ch-row" style="justify-content:flex-start;margin-top:12px"><button class="ch-button" id="connect-google" type="button"><?= $linked?'Refresh with Google':'Link Repostit with Google' ?></button><button class="ch-button secondary" id="refresh-repostit" type="button">Refresh linked account</button></div>
   <details><summary>Use my existing Repostit email and password instead</summary><form id="repostit-email-login" class="ch-formgrid" style="max-width:640px"><div><label for="repostit-email">Repostit email</label><input id="repostit-email" type="email" autocomplete="username" required></div><div><label for="repostit-password">Repostit password</label><input id="repostit-password" type="password" autocomplete="current-password" required></div><div class="wide"><button class="ch-button secondary" type="submit">Link existing Repostit account</button></div></form><p class="ch-subtle">Your password is sent directly to Firebase Authentication, not stored in the partner portal. This does not create a new Repostit account.</p></details>
   <p id="connection-status" class="ch-statusline" role="status"></p>
  </div>
  <?php if($linked): ?><form action="/creator/disconnect" method="post"><?= $csrfField ?><button class="ch-button secondary small" id="disconnect-repostit" type="submit">Disconnect account from this portal</button></form><?php endif; ?>
  <details><summary>Add a public social profile</summary><form action="/creator/profile" method="post" class="ch-formgrid"><?= $csrfField ?><div><label for="profile-platform">Platform</label><select id="profile-platform" name="platform"><?php foreach(CreatorRules::PLATFORMS as $platform): ?><option><?= $escape($platform) ?></option><?php endforeach; ?></select></div><div><label for="display-name">Profile name</label><input id="display-name" name="display_name" maxlength="180" required></div><div class="wide"><label for="profile-url">Public profile URL</label><input id="profile-url" name="profile_url" type="url" placeholder="https://www.instagram.com/yourname/" required maxlength="500"></div><div class="wide"><button class="ch-button secondary" type="submit">Save profile</button></div></form></details>
  <?php endif; ?>
 </section>
 <section class="ch-card ch-stack" id="brief"><div><div class="ch-kicker">A SIMPLE CREATIVE BRIEF</div><h2>Show the workflow, not a sales pitch.</h2><p class="ch-muted">Your audience should see a useful solution to a problem they already have. Try Repostit first, then share your honest experience.</p></div>
  <div class="ch-formgrid"><div class="ch-plan"><strong>For ecommerce audiences</strong><p>Show how you distribute one product video across your store's social channels without uploading it repeatedly.</p></div><div class="ch-plan"><strong>For course sellers and service businesses</strong><p>Show how a useful tip or client-facing video becomes content on several channels through your real workflow.</p></div></div>
  <p class="ch-muted">Suggested first test: one short demo and two Stories with link stickers. This is a suggestion, not a new obligation. Agree deliverables and dates with Hax before committing.</p>
  <p class="ch-muted">Make the problem visible, show a successful result, then offer your link to people who want to try it. Disclose the free account and affiliate relationship clearly in the content. No fake comments or invented results.</p>
 </section>
 <section class="ch-card ch-stack" id="content"><div class="ch-row"><div><div class="ch-kicker">CONTENT THAT BRINGS CUSTOMERS</div><h2>Your content scoreboard</h2><p class="ch-muted">One content link per placement. Compare paying subscribers and earnings, not just views.</p></div><span class="ch-badge"><?= count($contents) ?> content links</span></div>
  <?php if(!$readOnly && $program): ?><p id="placement-status" class="ch-statusline" role="status" aria-live="polite"></p><details id="content-link-details" <?= !$contents?'open':'' ?>><summary>Create a content-specific link</summary><form id="content-link-form" action="/creator/content" method="post" class="ch-formgrid"><?= $csrfField ?><div class="wide"><label for="content-title">Give this content a name</label><input id="content-title" name="title" required maxlength="180" placeholder="How I share my Shopify product videos"></div><div><label for="content-platform">Platform</label><select id="content-platform" name="platform"><?php foreach(CreatorRules::PLATFORMS as $platform): ?><option><?= $escape($platform) ?></option><?php endforeach; ?></select></div><div><label for="content-kind">Format</label><select id="content-kind" name="kind"><option value="video">Reel / short video</option><option value="story">Story</option><option value="tutorial">Tutorial</option><option value="post">Post</option></select></div><div><label for="content-program">Program</label><select id="content-program" name="partner_program_id"><?php foreach($programs as $p): ?><option value="<?= (int)$p['id'] ?>"><?= $escape($p['name']) ?></option><?php endforeach; ?></select></div><div style="align-self:end"><button class="ch-button" type="submit">Create my content link</button></div></form></details><?php endif; ?>
  <?php if(!$contents): ?><div class="ch-empty">No promotional content recorded yet. Create a link before publishing, then submit the public URL here.</div><?php else: ?>
  <div class="ch-tablewrap"><table class="ch-table"><thead><tr><th>Content & placement</th><th>Provider views</th><th>Visits</th><th>Signups</th><th>Paying customers</th><th>Commission</th></tr></thead><tbody>
  <?php foreach($contents as $c): $metrics=$c['provider_metrics']; ?><tr><td><strong><?= $escape($c['title']) ?></strong><p><?= $escape(ucfirst($c['platform'])) ?> · <?= $escape($c['kind']) ?></p><span class="ch-badge <?= $escape($c['status']) ?>"><?= ['planned'=>'Ready to publish','submitted'=>'URL submitted, review pending','verified'=>'Promotion verified'][$c['status']] ?></span>
   <div style="margin-top:10px"><button type="button" class="ch-button secondary small" data-copy="<?= $escape($c['link']) ?>">Copy content link</button></div>
   <?php if($c['post_url']): ?><p><a class="ch-link" target="_blank" rel="noopener noreferrer" href="<?= $escape($c['post_url']) ?>">Open published content</a></p><?php endif; ?>
   <?php if(!$readOnly): ?><details><summary><?= $c['post_url']?'Update published URL':'Add published URL' ?></summary><form action="/creator/content/submit" method="post"><?= $csrfField ?><input type="hidden" name="content_id" value="<?= (int)$c['id'] ?>"><label for="post-url-<?= (int)$c['id'] ?>">Public post URL</label><input id="post-url-<?= (int)$c['id'] ?>" name="post_url" type="url" maxlength="500" required value="<?= $escape($c['post_url']) ?>"><button class="ch-button small" style="margin-top:8px" type="submit">Submit URL</button></form></details><?php endif; ?>
  </td><td class="num"><?= isset($metrics['views'])?number_format($metrics['views']):'Unavailable' ?><p><?= !empty($c['provider_checked_at'])?'Snapshot '.$escape($c['provider_checked_at']).' UTC':'Link account and refresh, provider coverage varies' ?></p></td><td class="num"><?= number_format($c['clicks']) ?></td><td class="num"><?= !empty($profile['referrals_synced_at'])?number_format($c['signups']):'Not synced' ?></td><td class="num"><?= number_format($c['customers']) ?></td><td class="num"><?= $money($c['earnings']) ?></td></tr><?php endforeach; ?>
  </tbody></table></div><?php endif; ?>
  <p class="ch-subtle">A content-specific link attributes the link visit and later referral, not everyone who watched the video. Provider views are imported only for matching posts published through Repostit when the platform makes metrics available. Missing metrics are not zero.</p>
 </section>
 <?php $ranked=$contents; usort($ranked,static fn($a,$b)=>[$b['customers'],$b['signups'],$b['clicks']]<=>[$a['customers'],$a['signups'],$a['clicks']]); $best=$ranked[0]??null; ?>
 <section class="ch-card ch-stack"><div class="ch-kicker">WHAT TO MAKE NEXT</div><h2>Double down on customers, not just views.</h2><?php if($best && $best['customers']>0): ?><p class="ch-muted">Your strongest tracked content is <strong><?= $escape($best['title']) ?></strong>, with <?= (int)$best['customers'] ?> paying customers from <?= (int)$best['clicks'] ?> link visits. Try another piece around that same problem, with a new content link so the result stays separate.</p><?php else: ?><p class="ch-muted">No content has a verified paying referral yet. Start with one real workflow demo and a clear way to get your link. Compare signups first, then paying customers before deciding what to repeat.</p><?php endif; ?></section>
 <div class="ch-row"><p class="ch-muted">Need a hand? <a class="ch-link" href="mailto:support@repostit.io">Reach out to Hax</a>.</p><span class="ch-period">Account access, content publication and customer payment are separate milestones.</span></div>
</div>
<script src="/assets/js/creator-hub.js?v=sharing-cards-20261004" defer></script>
<?php if(!$readOnly): ?><script type="application/json" id="firebase-public-config"><?= json_encode($firebaseConfig,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?></script><script type="module" src="/assets/js/creator-connect.js"></script><?php endif; ?>
