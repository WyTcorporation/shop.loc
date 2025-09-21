<?php

namespace App\Services\Marketing;

use App\Models\CampaignTest;
use App\Models\MarketingCampaign;
use Illuminate\Support\Arr;

class CampaignStatisticsService
{
    public function recordOpen(MarketingCampaign $campaign, int $count = 1): void
    {
        $campaign->incrementMetric('open_count', $count);
    }

    public function recordClick(MarketingCampaign $campaign, int $count = 1): void
    {
        $campaign->incrementMetric('click_count', $count);
    }

    public function recordConversion(MarketingCampaign $campaign, int $count = 1): void
    {
        $campaign->incrementMetric('conversion_count', $count);
    }

    public function refresh(MarketingCampaign $campaign): void
    {
        $current = [
            'open_count' => (int) $campaign->getAttribute('open_count'),
            'click_count' => (int) $campaign->getAttribute('click_count'),
            'conversion_count' => (int) $campaign->getAttribute('conversion_count'),
        ];

        $totals = [
            'open_count' => 0,
            'click_count' => 0,
            'conversion_count' => 0,
        ];

        $campaign->tests
            ->each(function (CampaignTest $test) use (&$totals): void {
                $metrics = $test->metrics ?? [];
                $totals['open_count'] += (int) Arr::get($metrics, 'opens', 0);
                $totals['click_count'] += (int) Arr::get($metrics, 'clicks', 0);
                $totals['conversion_count'] += (int) Arr::get($metrics, 'conversions', 0);
            });

        $campaign->forceFill([
            'open_count' => max($current['open_count'], $totals['open_count']),
            'click_count' => max($current['click_count'], $totals['click_count']),
            'conversion_count' => max($current['conversion_count'], $totals['conversion_count']),
        ]);
        $campaign->save();
    }

    public function calculateConversionRate(MarketingCampaign $campaign): float
    {
        return $campaign->conversionRate();
    }
}
