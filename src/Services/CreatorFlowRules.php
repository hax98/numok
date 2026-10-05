<?php
declare(strict_types=1);
namespace Numok\Services;

final class CreatorFlowRules {
    public static function analyticsLabel(array $connection): string {
        if (!empty($connection['authorizationRequired'])) return 'Reconnect in Repostit to allow analytics';
        return match ($connection['insightsStatus'] ?? '') {
            'active','available' => 'Analytics available for supported posts',
            'reconnect_required' => 'Reconnect in Repostit, token expired or permission missing',
            'unsupported' => 'Automatic metrics not supported for this platform',
            default => 'Not checked yet, add a published URL to check metrics'
        };
    }
    public static function metricLabel(string $status): string {
        return match($status) {
            'available' => 'Provider metrics verified',
            'authorization_required','reconnect_required' => 'Reconnect this account in Repostit, then refresh',
            'not_owned_or_missing' => 'Post not found in your authorized account, check the URL and account',
            'unsupported' => 'Automatic metrics unavailable for this platform or format',
            'retry' => 'Provider temporarily unavailable, next scheduled sync will retry',
            default => 'Awaiting a supported post URL and connected account'
        };
    }
    public static function deliverables(array $input): array {
        $out=[];
        foreach ($input as $item) {
            $platform=(string)($item['platform']??''); $kind=(string)($item['kind']??''); $count=(int)($item['count']??0);
            if (!in_array($platform,CreatorRules::PLATFORMS,true) || !in_array($kind,['video','story','post','tutorial'],true) || $count<1 || $count>20) throw new \InvalidArgumentException('Choose a platform, format and quantity from 1 to 20.');
            $key=$platform.':'.$kind;
            if (isset($out[$key])) throw new \InvalidArgumentException('List each platform and format only once.');
            $out[$key]=['platform'=>$platform,'kind'=>$kind,'count'=>$count];
        }
        if (!$out || count($out)>8) throw new \InvalidArgumentException('Add between one and eight deliverables.');
        return array_values($out);
    }
    public static function campaignProgress(array $deliverables,array $contents): array {
        $done=0;$total=0;$rows=[];
        $unique=[];
        foreach($contents as $content){
            if($content['status']!=='verified'||empty($content['post_url']))continue;
            $url=preg_replace('~[?#].*$~','',rtrim((string)$content['post_url'],'/'));
            $unique[$content['platform'].':'.$url]=$content;
        }
        foreach($deliverables as $item){
            $count=count(array_filter($unique,fn($c)=>$c['platform']===$item['platform'] && $c['kind']===$item['kind']));
            $done+=min($count,$item['count']);$total+=$item['count'];
            $rows[]=$item+['verified'=>min($count,$item['count'])];
        }
        return ['done'=>$done,'total'=>$total,'complete'=>$total>0&&$done===$total,'rows'=>$rows];
    }
    public static function recommendation(array $contents): array {
        // Exploratory guidance, not a causal claim or statistically validated winner.
        $published=array_values(array_filter($contents,fn($c)=>$c['status']==='verified'));
        if (!$published) return ['title'=>'Publish your first agreed demo','text'=>'Try a real workflow, use its dedicated link and submit the published URL. There is no verified promotional content to compare yet.'];
        usort($published,fn($a,$b)=>[$b['customers'],$b['signups'],$b['clicks']]<=>[$a['customers'],$a['signups'],$a['clicks']]);
        $best=$published[0];$clicks=(int)$best['clicks'];$paid=(int)$best['customers'];$signups=(int)$best['signups'];
        if ($paid>0) return ['title'=>'Repeat this problem with a new angle','text'=>$best['title'].' brought '.$paid.' paying customers, '.$signups.' signups and '.$clicks.' visits. Test a follow-up with a new link. This is an early signal, not proof that it will win again.'];
        if ($signups>0) return ['title'=>'Check what happens after signup','text'=>$best['title'].' brought '.$signups.' signups but no verified paying customers. Review whether viewers are the right customers and whether they finish their first successful publish before making more content.'];
        if ($clicks>=20) return ['title'=>'Review the demo and the landing page','text'=>$best['title'].' brought '.$clicks.' visits but no attributed signups. Check the link path and whether the demo solves a specific audience problem.'];
        return ['title'=>'Make the next step easy to find','text'=>'There are too few tracked visits to pick a winner. Show one useful result and make the content link easy to get. Views alone do not prove customer demand.'];
    }
}
