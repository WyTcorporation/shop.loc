<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'cron_expression',
        'timezone',
        'starts_at',
        'ends_at',
        'last_run_at',
        'next_run_at',
        'is_active',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(MarketingCampaign::class, 'campaign_id');
    }
}
