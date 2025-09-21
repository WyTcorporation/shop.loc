<?php

namespace App\Services\Marketing;

use App\Models\CustomerSegment;
use App\Models\MarketingCampaign;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SegmentService
{
    public function resolveRecipients(MarketingCampaign $campaign, ?CustomerSegment $segment = null): Collection
    {
        if ($segment) {
            return $this->buildQuery($segment)->get();
        }

        $segments = $campaign->relationLoaded('segments') ? $campaign->segments : $campaign->segments()->get();

        if ($segments->isEmpty()) {
            return $this->defaultAudience();
        }

        return $segments
            ->filter(fn (CustomerSegment $segment) => $segment->is_active)
            ->flatMap(fn (CustomerSegment $segment) => $this->buildQuery($segment)->get())
            ->unique('id')
            ->values();
    }

    protected function defaultAudience(): Collection
    {
        return User::query()
            ->whereNotNull('email')
            ->get();
    }

    protected function buildQuery(CustomerSegment $segment): Builder
    {
        $query = User::query();
        $conditions = $segment->conditions ?? [];

        if (! is_array($conditions)) {
            return $query;
        }

        foreach ($conditions as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            switch (Str::lower((string) $key)) {
                case 'email_verified':
                    if (filter_var($value, FILTER_VALIDATE_BOOL)) {
                        $query->whereNotNull('email_verified_at');
                    }

                    break;
                case 'created_after':
                    $query->whereDate('created_at', '>=', $value);

                    break;
                case 'created_before':
                    $query->whereDate('created_at', '<=', $value);

                    break;
                case 'email_domain':
                    $query->where('email', 'like', '%@' . ltrim((string) $value, '@'));

                    break;
            }
        }

        return $query;
    }
}
