<?php
use Numok\Services\CreatorRules;
use Numok\Services\CreatorFlowRules;
use Numok\Services\PortalFormat as Fmt;
$escape=static fn($v)=>htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');
$program=$programs[0]??null;
$linked=!empty($profile['firebase_uid']);
$snapshot=$profile['analytics_snapshot']??[];
$account=$profile['account_snapshot']??[];
$connections=$snapshot['connections']??[];
$csrfField='<input type="hidden" name="csrf" value="'.$escape($csrf).'">';
$money=static fn($v)=>'$'.number_format((float)$v,2);
$firstName=explode(' ',trim((string)($partner['contact_name']??'')))[0]?:'there';
$stepsDone=['join'=>(bool)$program,'link'=>$linked,'content'=>(bool)$contents,'published'=>(bool)array_filter($contents,fn($c)=>!empty($c['post_url']))];
$allDone=!in_array(false,$stepsDone,true);
$recommendation=CreatorFlowRules::recommendation($contents);
$platformOptions=static function() use ($escape){ $o=''; foreach(CreatorRules::PLATFORMS as $p){ $o.='<option value="'.$escape($p).'">'.$escape(Fmt::app($p)).'</option>'; } return $o; };
?>
<link rel="stylesheet" href="/assets/css/creator-hub.css?v=ds-20261115c">
<div class="creator-hub">
 <?php if($readOnly): ?><div class="ch-notice">Read-only preview of this partner's dashboard. Nothing can be submitted from here.</div><?php endif; ?>

 <div class="ch-top"><div><h1>Hello, <?= $escape($firstName) ?>.</h1><p class="ch-muted">Your referral link, results and earnings.</p></div></div>
 <?php foreach(['success','error'] as $notice): if(!empty($_SESSION[$notice])): ?><div class="ch-notice <?= $notice ?>" role="status"><?= $escape($_SESSION[$notice]) ?></div><?php unset($_SESSION[$notice]);endif;endforeach; ?>
 <?php if($syncError): ?><div class="ch-notice error" role="status"><?= $escape($syncError) ?></div><?php endif; ?>
 <?php foreach(($actions??[]) as $action): if($action['audience']!=='creator')continue; ?><div class="ch-notice" role="status"><?= $escape($action['message']) ?> <a class="ch-link" href="<?= $escape($action['destination']) ?>">Go there</a></div><?php endforeach; ?>

 <div class="<?= $allDone?'ch-stack':'ch-two' ?>">
  <section class="ch-card accent" aria-labelledby="link-heading"><div class="ch-kicker">Your link</div>
   <?php if($program): $link=CreatorRules::affiliateLink((string)$program['tracking_code']); ?>
    <h2 id="link-heading">Share this link to earn 20%.</h2>
    <p class="ch-muted">Put it in your bio, a Story link sticker, or send it to someone who asks.</p>
    <div class="ch-linkfield"><input aria-label="Your referral link" readonly value="<?= $escape($link) ?>"><button class="ch-button" type="button" data-copy="<?= $escape($link) ?>">Copy link</button></div>
    <p class="ch-subtle"><a class="ch-link" href="/programs">Change your code</a> · Want to know which post brought a customer? <a class="ch-link" href="#content">Create a link per post</a>.</p>
   <?php else: ?>
    <h2 id="link-heading">Get your referral link.</h2>
    <p class="ch-muted">Join the Repostit program to get your personal link and start tracking results.</p>
    <a class="ch-button" href="/programs">Join the program</a>
   <?php endif; ?>
  </section>
  <?php if(!$allDone): ?>
  <section class="ch-card" aria-labelledby="steps-heading"><h2 id="steps-heading">Get started</h2>
   <?php $steps=[
     ['join','/programs','Join the program','Accept the terms and pick your code.'],
     ['link','#socials','Link your Repostit account','Use the account you post with. This lets us show your connected profiles and post metrics.'],
     ['content','#content','Create a link for your first post','One link per post shows which content brings customers.'],
     ['published','#content','Add the published URL','After you post, paste the public URL next to its link.'],
   ]; foreach($steps as $i=>[$key,$href,$label,$help]): ?>
    <div class="ch-step"><span class="ch-stepnum<?= $stepsDone[$key]?' done':'' ?>"><?= $stepsDone[$key]?'<span aria-hidden="true">✓</span><span class="sr-only">Done:</span>':($i+1) ?></span><div><a class="ch-link" href="<?= $href ?>"><?= $label ?></a><p><?= $help ?></p></div></div>
   <?php endforeach; ?>
  </section>
  <?php endif; ?>
 </div>

 <div class="ch-stats" aria-label="Your results">
  <div class="ch-card"><span class="ch-label">Link visits</span><div class="ch-number"><?= number_format($summary['clicks']) ?></div><div class="ch-period">All time</div></div>
  <div class="ch-card"><span class="ch-label">Signups</span><div class="ch-number"><?= !empty($profile['referrals_synced_at'])?number_format($summary['signups']):'—' ?></div><div class="ch-period"><?= !empty($profile['referrals_synced_at'])?'Updated '.$escape(Fmt::ago($profile['referrals_synced_at'])):'Not synced yet' ?></div></div>
  <div class="ch-card"><span class="ch-label">Paying subscribers</span><div class="ch-number"><?= number_format($summary['paying_customers']) ?></div><div class="ch-period">Unique customers</div></div>
  <div class="ch-card"><span class="ch-label">Commission earned</span><div class="ch-number"><?= $money($summary['commission']) ?></div><div class="ch-period">USD</div></div>
 </div>

 <div class="ch-two">
  <section class="ch-card" aria-labelledby="earnings-heading"><div class="ch-kicker">Earnings</div><h2 id="earnings-heading">Your money</h2>
   <div class="ch-step"><div style="width:100%"><div class="ch-row"><span>Commission awaiting payout</span><strong><?= $money($summary['unpaid']) ?></strong></div><p>Pending review or ready to pay.</p></div></div>
   <div class="ch-step"><div style="width:100%"><div class="ch-row"><span>Commission paid</span><strong><?= $money($summary['paid']) ?></strong></div></div></div>
   <div class="ch-step"><div style="width:100%"><div class="ch-row"><span>Bonuses awaiting transfer</span><strong><?= $money($summary['bonus_earned']) ?></strong></div></div></div>
   <div class="ch-step"><div style="width:100%"><div class="ch-row"><span>Bonuses paid</span><strong><?= $money($summary['bonus_paid']) ?></strong></div></div></div>
   <p class="ch-subtle"><a class="ch-link" href="/earnings">See every payment</a> · We arrange payouts with you privately, using your payment email in <a class="ch-link" href="/settings">Settings</a>.</p>
  </section>
  <section class="ch-card" aria-labelledby="milestone-heading"><div class="ch-kicker">Bonuses</div><h2 id="milestone-heading">Up to <?= Fmt::money(array_sum(CreatorRules::MILESTONES)) ?> on top</h2><p class="ch-muted">Paid once each, on top of your 20%.</p>
   <?php foreach($summary['milestones'] as $m): ?><div class="ch-milestone"><div class="ch-row"><div><strong><?= Fmt::money($m['amount']) ?></strong><p class="ch-muted" style="margin:3px 0">At <?= number_format($m['threshold']) ?> paying subscribers</p></div><span class="ch-badge <?= $escape($m['status']) ?>"><?= ['locked'=>'In progress','earned'=>'Earned, transfer pending','paid'=>'Paid'][$m['status']] ?></span></div>
    <progress class="ch-progress" max="<?= $m['threshold'] ?>" value="<?= $m['count'] ?>" aria-label="Progress toward <?= $m['threshold'] ?> paying subscribers"></progress><div class="ch-row"><span class="ch-label"><?= $m['count'] ?> of <?= number_format($m['threshold']) ?></span><span class="ch-label"><?= number_format($m['remaining']) ?> to go</span></div>
    <?php if($m['status']==='paid'): ?><p class="ch-subtle">Paid <?= $escape(Fmt::date($m['payment']['paid_at'])) ?>. Reference: <?= $escape($m['payment']['payment_reference']) ?></p><?php endif; ?></div><?php endforeach; ?>
  </section>
 </div>

 <section class="ch-distribution ch-stack" aria-labelledby="share-heading">
  <div><div class="ch-kicker">Share</div><h2 id="share-heading">Three easy ways to share</h2><p class="ch-muted">Show how you use Repostit, then make your link easy to find. Each button sets up a link for that placement below.</p></div>
  <div class="ch-share-grid">
   <article class="ch-share-card">
    <div class="ch-share-visual ch-reel-visual" aria-hidden="true"><div class="ch-phone"><div class="ch-phone-notch"></div><div class="ch-video-scene"><span class="ch-play">▶</span><span class="ch-scene-caption">One video. More places.</span></div><div class="ch-mini-comment"><span class="ch-mini-avatar"></span><span>Send me the link?</span></div></div><div class="ch-float-bubble">Link sent <span>↗</span></div></div>
    <span class="ch-share-platform">Instagram Reel</span><h3>Turn comments into conversations</h3>
    <ol class="ch-share-steps"><li>Show your real workflow in a Reel.</li><li>Ask viewers to comment <strong>LINK</strong> if they want to try it.</li><li>Send the Reel's link to people who ask.</li></ol>
    <p class="ch-share-note">Send the DMs yourself or with a tool such as Manychat. Repostit doesn't send them for you.</p>
    <button class="ch-button" type="button" data-placement="instagram-reel" <?= $readOnly||!$program?'disabled':'' ?>>Create a Reel link</button>
   </article>
   <article class="ch-share-card">
    <div class="ch-share-visual ch-story-visual" aria-hidden="true"><div class="ch-phone"><div class="ch-story-progress"><i></i><i></i><i></i></div><div class="ch-story-caption">Here's how I<br>share my videos.</div><div class="ch-story-sticker"><span>↗</span> Try Repostit</div><div class="ch-story-tap">Tap to open</div></div><span class="ch-tap-ring"></span></div>
    <span class="ch-share-platform">Instagram Story</span><h3>Add a link sticker</h3>
    <ol class="ch-share-steps"><li>Share a quick demo or your honest take.</li><li>Add a link sticker with the Story's link.</li><li>Save it to a Highlight so it stays easy to find.</li></ol>
    <p class="ch-share-note">Use a new link for each Story you want to compare.</p>
    <button class="ch-button" type="button" data-placement="instagram-story" <?= $readOnly||!$program?'disabled':'' ?>>Create a Story link</button>
   </article>
   <article class="ch-share-card">
    <div class="ch-share-visual ch-bio-visual" aria-hidden="true"><div class="ch-phone"><div class="ch-bio-avatar">you</div><div class="ch-bio-name">@yourname</div><div class="ch-bio-caption">The tools I actually use.</div><div class="ch-bio-link">↗ Try Repostit</div><div class="ch-mini-grid"><i></i><i></i><i></i></div></div><div class="ch-float-bubble">Your link, in your bio.</div></div>
    <span class="ch-share-platform">TikTok bio</span><h3>Make it easy to find</h3>
    <ol class="ch-share-steps"><li>Show Repostit in a normal, useful video.</li><li>Put your link in your bio or link page.</li><li>Tell viewers where to find it.</li></ol>
    <p class="ch-share-note">TikTok only allows bio links on eligible accounts.</p>
    <button class="ch-button" type="button" data-placement="tiktok-bio" <?= $readOnly||!$program?'disabled':'' ?>>Create a bio link</button>
   </article>
  </div>
  <?php if(!$program && !$readOnly): ?><p class="ch-subtle">Join the program first to create links.</p><?php endif; ?>
 </section>

 <section class="ch-card ch-stack" id="content"><div class="ch-row"><div><div class="ch-kicker">Content links</div><h2>Which posts bring customers</h2><p class="ch-muted">Create one link per post. Compare paying subscribers and earnings, not just views.</p></div><span class="ch-badge"><?= count($contents) ?> <?= count($contents)===1?'link':'links' ?></span></div>
  <div class="ch-plan"><strong><?= $escape($recommendation['title']) ?></strong><p style="margin:4px 0 0"><?= $escape($recommendation['text']) ?></p></div>
  <?php if(!$readOnly && $program): ?><p id="placement-status" class="ch-statusline" role="status" aria-live="polite"></p><details id="content-link-details" <?= !$contents?'open':'' ?>><summary>Create a link for a post</summary><form id="content-link-form" action="/creator/content" method="post" class="ch-formgrid"><?= $csrfField ?><div class="wide"><label for="content-title">Name</label><input id="content-title" name="title" required maxlength="180" placeholder="How I share my Shopify product videos"></div><div><label for="content-platform">App</label><select id="content-platform" name="platform"><?= $platformOptions() ?></select></div><div><label for="content-kind">Format</label><select id="content-kind" name="kind"><option value="video">Reel or short video</option><option value="story">Story</option><option value="tutorial">Tutorial</option><option value="post">Post</option></select></div><div><label for="content-program">Program</label><select id="content-program" name="partner_program_id"><?php foreach($programs as $p): ?><option value="<?= (int)$p['id'] ?>"><?= $escape($p['name']) ?></option><?php endforeach; ?></select></div><div><label for="content-campaign">Campaign</label><select id="content-campaign" name="campaign_id"><option value="0">None</option><?php foreach($campaigns as $campaign): if($campaign['status']!=='accepted')continue; ?><option value="<?= (int)$campaign['id'] ?>"><?= $escape($campaign['title']) ?></option><?php endforeach; ?></select></div><div class="wide"><button class="ch-button" type="submit">Create link</button></div></form></details><?php endif; ?>
  <?php if(!$contents): ?><div class="ch-empty">No post links yet. Create one before you publish, then add the public URL here.</div><?php else: ?>
  <div class="ch-tablewrap"><table class="ch-table"><thead><tr><th>Post</th><th>Views</th><th>Visits</th><th>Signups</th><th>Paying</th><th>Commission</th></tr></thead><tbody>
  <?php foreach($contents as $c): $metrics=$c['provider_metrics']; ?><tr><td><strong><?= $escape($c['title']) ?></strong><p><?= $escape(Fmt::app($c['platform'])) ?> · <?= $escape(['video'=>'Reel or short video','story'=>'Story','tutorial'=>'Tutorial','post'=>'Post'][$c['kind']]??$c['kind']) ?></p><span class="ch-badge <?= $escape($c['status']) ?>"><?= ['planned'=>'Ready to publish','submitted'=>'In review','verified'=>'Verified'][$c['status']] ?></span>
   <div style="margin-top:10px"><button type="button" class="ch-button secondary small" data-copy="<?= $escape($c['link']) ?>">Copy link</button></div>
   <?php if($c['post_url']): ?><p><a class="ch-link" target="_blank" rel="noopener noreferrer" href="<?= $escape($c['post_url']) ?>">Open the post</a></p><?php endif; ?>
   <?php if(!$readOnly): ?><details><summary><?= $c['post_url']?'Change post URL':'Add post URL' ?></summary><form action="/creator/content/submit" method="post"><?= $csrfField ?><input type="hidden" name="content_id" value="<?= (int)$c['id'] ?>"><label for="post-url-<?= (int)$c['id'] ?>">Public post URL</label><input id="post-url-<?= (int)$c['id'] ?>" name="post_url" type="url" maxlength="500" required value="<?= $escape($c['post_url']) ?>"><button class="ch-button small" style="margin-top:8px" type="submit">Save URL</button></form></details><?php endif; ?>
  </td><td class="num"><?= isset($metrics['views'])?number_format($metrics['views']):'—' ?><p><?= $escape(CreatorFlowRules::metricLabel($c['analytics_status']??'not_checked')) ?></p><?php if(!empty($c['provider_checked_at'])): ?><p>Checked <?= $escape(Fmt::ago($c['provider_checked_at'])) ?></p><?php endif; ?></td><td class="num"><?= number_format($c['clicks']) ?></td><td class="num"><?= !empty($profile['referrals_synced_at'])?number_format($c['signups']):'—' ?></td><td class="num"><?= number_format($c['customers']) ?></td><td class="num"><?= $money($c['earnings']) ?></td></tr><?php endforeach; ?>
  </tbody></table></div><?php endif; ?>
  <?php if($linked&&!$readOnly): ?><div class="ch-row" style="justify-content:flex-start"><form action="/creator/analytics/refresh" method="post"><?= $csrfField ?><button class="ch-button secondary small">Refresh metrics</button></form><span class="ch-period">Updated <?= $escape(Fmt::ago($profile['synced_at']??null)) ?>. Metrics also refresh every hour.</span></div><?php endif; ?>
 </section>

 <?php require __DIR__.'/campaigns.php'; ?>

 <section class="ch-card ch-stack" id="socials"><div class="ch-row"><div><div class="ch-kicker">Accounts</div><h2>Your Repostit account</h2><p class="ch-muted">Link the Repostit account you post with. We use it to show your connected profiles and post metrics.</p></div><a class="ch-button secondary" target="_blank" rel="noopener" href="<?= $escape(\Numok\Services\PortalEnvironment::appUrl()) ?>/connections">Manage connections in Repostit</a></div>
  <?php if($linked): ?><div class="ch-plan">Linked to <strong><?= $escape($profile['repostit_email']) ?></strong> · updated <?= $escape(Fmt::ago($profile['synced_at']??null)) ?>
   <?php if(!empty($account['influencerExpiresAt'])): ?><p style="margin:6px 0 0">Influencer access active until <?= $escape(Fmt::date($account['influencerExpiresAt'])) ?>.</p><?php else: ?><p style="margin:6px 0 0">Influencer access isn't active on this account. If it's part of your agreement, email <a class="ch-link" href="mailto:support@repostit.io">support@repostit.io</a> from your Repostit account email.</p><?php endif; ?></div>
   <?php foreach($connections as $connection): ?><div class="ch-profile ch-row"><div><strong><?= $escape($connection['name']??'Connected account') ?></strong> <span class="ch-muted"> · <?= $escape(Fmt::app($connection['platform']??'')) ?></span></div><span class="ch-badge"><?= $escape(CreatorFlowRules::analyticsLabel($connection)) ?></span></div><?php endforeach; ?>
   <?php if(!$connections): ?><p class="ch-muted">No connected profiles yet. Connect them in Repostit, then refresh.</p><?php endif; ?>
  <?php endif; ?>
  <?php foreach($socials as $social): ?><div class="ch-profile"><a class="ch-link" href="<?= $escape($social['profile_url']) ?>" target="_blank" rel="noopener noreferrer"><?= $escape($social['display_name']) ?> · <?= $escape(Fmt::app($social['platform'])) ?></a><span class="ch-muted"> · Added by you, not verified</span></div><?php endforeach; ?>
  <?php if(!$readOnly): ?>
  <div id="repostit-linker" data-csrf="<?= $escape($csrf) ?>" data-linked="<?= $linked?'true':'false' ?>">
   <label class="ch-consent"><input type="checkbox" id="link-consent" <?= $linked?'checked':'' ?>>I allow Repostit Partners to link this Repostit account and show its connected profiles, public post metrics and Influencer access status.</label>
   <div class="ch-row" style="justify-content:flex-start;margin-top:12px"><button class="ch-button" id="connect-google" type="button"><?= $linked?'Refresh with Google':'Link with Google' ?></button><button class="ch-button secondary" id="refresh-repostit" type="button">Refresh</button></div>
   <details><summary>Use my Repostit email and password instead</summary><form id="repostit-email-login" class="ch-formgrid" style="max-width:640px"><div><label for="repostit-email">Repostit email</label><input id="repostit-email" type="email" autocomplete="username" required></div><div><label for="repostit-password">Repostit password</label><input id="repostit-password" type="password" autocomplete="current-password" required></div><div class="wide"><button class="ch-button secondary" type="submit">Link account</button></div></form><p class="ch-subtle">Your password goes straight to Repostit's sign-in service and isn't stored here. This doesn't create a new account.</p></details>
   <p id="connection-status" class="ch-statusline" role="status"></p>
  </div>
  <?php if($linked): ?><form action="/creator/disconnect" method="post"><?= $csrfField ?><button class="ch-button secondary small" id="disconnect-repostit" type="submit">Unlink this account</button></form><?php endif; ?>
  <details><summary>Add a public social profile</summary><form action="/creator/profile" method="post" class="ch-formgrid"><?= $csrfField ?><div><label for="profile-platform">App</label><select id="profile-platform" name="platform"><?= $platformOptions() ?></select></div><div><label for="display-name">Profile name</label><input id="display-name" name="display_name" maxlength="180" required></div><div class="wide"><label for="profile-url">Public profile URL</label><input id="profile-url" name="profile_url" type="url" placeholder="https://www.instagram.com/yourname/" required maxlength="500"></div><div class="wide"><button class="ch-button secondary" type="submit">Save profile</button></div></form></details>
  <?php endif; ?>
 </section>

 <section class="ch-card ch-stack" id="brief"><div><div class="ch-kicker">Tips</div><h2>What works</h2><p class="ch-muted">Show a real problem, show Repostit solving it, then offer your link to people who want to try it.</p></div>
  <div class="ch-formgrid"><div class="ch-plan"><strong>Ecommerce audiences</strong><p style="margin:4px 0 0">Show one product video going out to every channel without uploading it again.</p></div><div class="ch-plan"><strong>Course sellers and service businesses</strong><p style="margin:4px 0 0">Show one tip or client video becoming posts on several apps.</p></div></div>
  <p class="ch-muted">A good first test: one short demo and two Stories with link stickers. Say clearly that you're an affiliate and if you got a free account.</p>
 </section>

 <details class="ch-card ch-fineprint" id="how-tracking-works"><summary>How tracking and payouts work</summary>
  <ul>
   <li>Commission is 20% of each valid payment from customers you refer, for as long as they keep paying. Refunded or rejected payments don't count.</li>
   <li>Link visits count every visit, not unique people. Signups aren't the same as paying customers.</li>
   <li>Bonuses count unique paying subscribers. Trials, free accounts and renewals don't count. We review each bonus before paying it, so it isn't instant.</li>
   <li>A post link credits visits and referrals that come through that link, not everyone who watched the post.</li>
   <li>We can check views for Instagram and TikTok posts on your linked accounts. Story views aren't available. Other apps use Repostit's publishing data. A missing number means we couldn't check it, not that it's zero.</li>
   <li>Joining the program doesn't commit you to making content. Campaigns are only agreed when you accept them, and we review the published URLs before marking them complete.</li>
   <li>We arrange payouts privately, using the payment email in Settings. Never share bank details in a post or link.</li>
  </ul>
 </details>

 <p class="ch-muted" style="margin-top:24px">Questions? Email <a class="ch-link" href="mailto:support@repostit.io">support@repostit.io</a>.</p>
</div>
<script src="/assets/js/creator-hub.js?v=sharing-cards-20261004" defer></script>
<?php if(!$readOnly): ?><script type="application/json" id="firebase-public-config"><?= json_encode($firebaseConfig,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?></script><script type="module" src="/assets/js/creator-connect.js"></script><?php endif; ?>
