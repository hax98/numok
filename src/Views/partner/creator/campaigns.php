<?php use Numok\Services\CreatorCampaigns; use Numok\Services\PortalFormat as Fmt;
$campaigns=$campaigns??CreatorCampaigns::all((int)$partner['id']);
if($campaigns): ?>
<section class="ch-card ch-stack" id="campaigns"><div><div class="ch-kicker">Campaigns</div><h2>Content you've agreed to make</h2><p class="ch-muted">Accept only what you want to take on. A campaign is complete once we've reviewed the published posts.</p></div>
<?php foreach($campaigns as $campaign): ?><article class="ch-plan"><div class="ch-row"><h3><?= $escape($campaign['title']) ?></h3><span class="ch-badge"><?= $escape(['proposed'=>'Waiting for you','accepted'=>'Accepted','declined'=>'Declined','completed'=>'Completed','cancelled'=>'Cancelled'][$campaign['status']]??ucfirst($campaign['status'])) ?></span></div><p class="ch-textwrap" style="white-space:pre-line"><?= $escape($campaign['brief']) ?></p>
<p class="ch-muted">Due <?= $escape(Fmt::date($campaign['due_at'])) ?><?= $campaign['overdue']?' · Deadline passed':'' ?> · <?= (int)$campaign['progress']['done'] ?> of <?= (int)$campaign['progress']['total'] ?> posts verified</p>
<ul><?php foreach($campaign['progress']['rows'] as $d): ?><li><?= (int)$d['count'] ?> × <?= $escape(Fmt::app($d['platform']).' '.$d['kind']) ?>, <?= (int)$d['verified'] ?> verified</li><?php endforeach; ?></ul>
<?php if(!$readOnly&&$campaign['status']==='proposed'): ?><form method="post" action="/creator/campaign/respond" class="ch-row" style="justify-content:flex-start"><?= $csrfField ?><input type="hidden" name="campaign_id" value="<?= (int)$campaign['id'] ?>"><button class="ch-button" name="decision" value="accepted">Accept</button><button class="ch-button secondary" name="decision" value="declined">Decline</button></form><?php elseif($campaign['status']==='accepted'): ?><a class="ch-link" href="#content">Create a link for each post and pick this campaign</a><?php endif; ?>
</article><?php endforeach; ?>
</section>
<?php endif; ?>
