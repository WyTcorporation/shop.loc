<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MarketingCampaign extends Model
{
    use HasFactory;

    public const TYPE_EMAIL = 'email';
    public const TYPE_PUSH = 'push';

    protected $fillable = [
        'name',
        'type',
        'template_id',
        'status',
        'settings',
        'audience_filters',
        'scheduled_for',
        'last_dispatched_at',
        'last_synced_at',
        'open_count',
        'click_count',
        'conversion_count',
    ];

    protected $casts = [
        'settings' => 'array',
        'audience_filters' => 'array',
        'scheduled_for' => 'datetime',
        'last_dispatched_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(CampaignTemplate::class, 'template_id');
    }

    public function segments(): BelongsToMany
    {
        return $this->belongsToMany(CustomerSegment::class, 'campaign_segment', 'campaign_id', 'segment_id')
            ->withTimestamps();
    }

    public function tests(): HasMany
    {
        return $this->hasMany(CampaignTest::class, 'campaign_id');
    }

    public function schedule(): HasOne
    {
        return $this->hasOne(CampaignSchedule::class, 'campaign_id');
    }

    public function scopeEmail($query)
    {
        return $query->where('type', self::TYPE_EMAIL);
    }

    public function scopePush($query)
    {
        return $query->where('type', self::TYPE_PUSH);
    }

    public function conversionRate(): float
    {
        $clicks = max(1, (int) $this->click_count);

        return round(((int) $this->conversion_count / $clicks) * 100, 2);
    }

    public function incrementMetric(string $metric, int $amount = 1): void
    {
        if (! in_array($metric, ['open_count', 'click_count', 'conversion_count'], true)) {
            return;
        }

        $this->increment($metric, $amount);
    }
}
