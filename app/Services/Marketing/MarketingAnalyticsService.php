<?php

namespace App\Services\Marketing;

use App\Models\MarketingCampaign;
use App\Support\Dashboard\DashboardPeriod;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;

class MarketingAnalyticsService
{
    public function __construct(private readonly CampaignStatisticsService $statisticsService)
    {
    }

    public function getCampaignPerformance(DashboardPeriod $period): array
    {
        [$start, $end] = $period->range();

        $campaigns = MarketingCampaign::query()
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $grouped = $campaigns->groupBy('type');

        $map = fn (Collection $collection): array => [
            'campaigns' => $collection->count(),
            'opens' => $collection->sum('open_count'),
            'clicks' => $collection->sum('click_count'),
            'conversions' => $collection->sum('conversion_count'),
            'average_conversion_rate' => $collection->count() > 0
                ? round($collection->avg(fn (MarketingCampaign $campaign) => $campaign->conversionRate()), 2)
                : 0.0,
        ];

        return [
            'email' => $map($grouped->get(MarketingCampaign::TYPE_EMAIL, collect())),
            'push' => $map($grouped->get(MarketingCampaign::TYPE_PUSH, collect())),
            'total_conversions' => $campaigns->sum('conversion_count'),
        ];
    }

    public function syncCampaign(MarketingCampaign $campaign): void
    {
        $settings = $campaign->settings ?? [];
        $settings['conversion_rate'] = $this->statisticsService->calculateConversionRate($campaign);

        $campaign->forceFill([
            'settings' => $settings,
            'last_synced_at' => Date::now(),
        ]);
        $campaign->save();
    }

    public function getKeyMetrics(DashboardPeriod $period): Collection
    {
        $performance = $this->getCampaignPerformance($period);

        return collect([
            'email_opens' => Arr::get($performance, 'email.opens', 0),
            'email_clicks' => Arr::get($performance, 'email.clicks', 0),
            'push_clicks' => Arr::get($performance, 'push.clicks', 0),
            'total_conversions' => Arr::get($performance, 'total_conversions', 0),
            'email_conversion_rate' => Arr::get($performance, 'email.average_conversion_rate', 0.0),
            'push_conversion_rate' => Arr::get($performance, 'push.average_conversion_rate', 0.0),
        ]);
    }
}
