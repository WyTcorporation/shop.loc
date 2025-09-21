<?php

namespace App\Services\Marketing;

use App\Models\CampaignSchedule;
use App\Models\MarketingCampaign;
use Carbon\Carbon;
use Cron\CronExpression;
use Illuminate\Support\Facades\Date;

class CampaignScheduleService
{
    public function ensureNextRun(MarketingCampaign $campaign): ?Carbon
    {
        $schedule = $campaign->schedule;

        if (! $schedule || ! $schedule->is_active) {
            return null;
        }

        $nextRun = $this->calculateNextRun($schedule);

        $schedule->next_run_at = $nextRun;
        $schedule->save();

        return $nextRun;
    }

    public function markDispatched(MarketingCampaign $campaign): void
    {
        $schedule = $campaign->schedule;

        if (! $schedule) {
            return;
        }

        $schedule->last_run_at = Date::now();
        $schedule->next_run_at = $this->calculateNextRun($schedule);
        $schedule->save();
    }

    public function calculateNextRun(CampaignSchedule $schedule): ?Carbon
    {
        $now = Date::now($schedule->timezone);

        if ($schedule->ends_at && $now->greaterThan($schedule->ends_at)) {
            return null;
        }

        $startDate = $schedule->starts_at && $schedule->starts_at->greaterThan($now)
            ? $schedule->starts_at
            : $now;

        if (class_exists(CronExpression::class)) {
            $expression = new CronExpression($schedule->cron_expression);
            $date = $expression->getNextRunDate($startDate);

            return Carbon::instance($date)->setTimezone($schedule->timezone);
        }

        return $startDate->copy()->addMinutes(5);
    }
}
