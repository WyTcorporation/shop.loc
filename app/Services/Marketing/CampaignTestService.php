<?php

namespace App\Services\Marketing;

use App\Models\CampaignTest;
use App\Models\MarketingCampaign;
use Illuminate\Support\Arr;

class CampaignTestService
{
    public function startTest(CampaignTest $test): void
    {
        $test->status = 'running';
        $test->save();
    }

    public function concludeTest(CampaignTest $test): void
    {
        $metrics = $test->metrics ?? [];
        $winner = ($metrics['variant_b']['conversions'] ?? 0) > ($metrics['variant_a']['conversions'] ?? 0)
            ? $test->variantBTemplate
            : $test->variantATemplate;

        $test->winning_template_id = $winner?->getKey();
        $test->status = 'completed';
        $test->save();

        if ($test->campaign && $winner) {
            $test->campaign->template()->associate($winner);
            $test->campaign->save();
        }
    }

    public function mergeMetrics(MarketingCampaign $campaign): array
    {
        return $campaign->tests
            ->map(fn (CampaignTest $test) => $test->metrics ?? [])
            ->reduce(function (array $carry, array $metrics): array {
                $carry['opens'] = ($carry['opens'] ?? 0) + (int) Arr::get($metrics, 'opens', 0);
                $carry['clicks'] = ($carry['clicks'] ?? 0) + (int) Arr::get($metrics, 'clicks', 0);
                $carry['conversions'] = ($carry['conversions'] ?? 0) + (int) Arr::get($metrics, 'conversions', 0);

                return $carry;
            }, []);
    }
}
