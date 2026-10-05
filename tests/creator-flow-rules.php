<?php
use Numok\Services\{CreatorFlowRules as F,CreatorRules};
$checks=0;
$assert=function($condition,$name)use(&$checks){if(!$condition)throw new RuntimeException($name);$checks++;};
$assert(str_contains(F::analyticsLabel(['insightsStatus'=>'unknown']),'Not checked'),'Unknown is actionable');
$assert(str_contains(F::analyticsLabel(['authorizationRequired'=>true]),'Reconnect'),'Auth error actionable');
$assert(str_contains(F::metricLabel('not_owned_or_missing'),'authorized account'),'Wrong owner label');
$d=F::deliverables([['platform'=>'instagram','kind'=>'video','count'=>1],['platform'=>'instagram','kind'=>'story','count'=>2]]);
$base=['platform'=>'instagram','kind'=>'video','status'=>'verified','post_url'=>'https://instagram.com/reel/AbC/'];
$progress=F::campaignProgress($d,[$base,$base,['platform'=>'instagram','kind'=>'story','status'=>'submitted']]);
$assert($progress['done']===1&&!$progress['complete'],'Duplicate format cannot fulfill another deliverable; submitted is not verified');
$assert(F::campaignProgress($d,[$base,['platform'=>'instagram','kind'=>'story','status'=>'verified','post_url'=>'https://instagram.com/stories/a/1/'],['platform'=>'instagram','kind'=>'story','status'=>'verified','post_url'=>'https://instagram.com/stories/a/2/']])['complete'],'Agreed deliverables completed');
$duplicate=['platform'=>'instagram','kind'=>'story','status'=>'verified','post_url'=>'https://instagram.com/stories/a/1/'];
$assert(!F::campaignProgress($d,[$base,$duplicate,$duplicate])['complete'],'Same public URL cannot count as two promotions');
$assert(!F::campaignProgress([],[])['complete'],'Empty is not complete');
foreach([[],[['platform'=>'instagram','kind'=>'video','count'=>0]],[['platform'=>'invalid','kind'=>'video','count'=>1]],[['platform'=>'instagram','kind'=>'video','count'=>21]],[['platform'=>'instagram','kind'=>'video','count'=>1],['platform'=>'instagram','kind'=>'video','count'=>1]]] as $invalid){try{F::deliverables($invalid);$ok=false;}catch(InvalidArgumentException $e){$ok=true;}$assert($ok,'Reject invalid deliverables');}
$post=['title'=>'Demo','status'=>'verified','customers'=>0,'signups'=>0,'clicks'=>5];
$assert(str_contains(F::recommendation([])['text'],'no verified'),'Empty evidence');
$assert(str_contains(F::recommendation([$post])['text'],'too few'),'Low sample');
$post['clicks']=20;$assert(str_contains(F::recommendation([$post])['text'],'no attributed signups'),'Clicks without signups');
$post['signups']=2;$assert(str_contains(F::recommendation([$post])['text'],'no verified paying'),'Signup not payment');
$post['customers']=1;$assert(str_contains(F::recommendation([$post])['text'],'not proof'),'Paid guidance caveat');
$post['status']='submitted';$assert(str_contains(F::recommendation([$post])['text'],'no verified'),'Unverified content not winner');
$assert(CreatorRules::MILESTONES===[20=>200,500=>5000],'Manual bonus offer unchanged');
echo "Creator flow rules: $checks checks passed.\n";
