<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'name',
        'variant_a_template_id',
        'variant_b_template_id',
        'traffic_split_a',
        'traffic_split_b',
        'status',
        'metrics',
        'winning_template_id',
    ];

    protected $casts = [
        'metrics' => 'array',
        'traffic_split_a' => 'integer',
        'traffic_split_b' => 'integer',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(MarketingCampaign::class, 'campaign_id');
    }

    public function variantATemplate(): BelongsTo
    {
        return $this->belongsTo(CampaignTemplate::class, 'variant_a_template_id');
    }

    public function variantBTemplate(): BelongsTo
    {
        return $this->belongsTo(CampaignTemplate::class, 'variant_b_template_id');
    }

    public function winningTemplate(): BelongsTo
    {
        return $this->belongsTo(CampaignTemplate::class, 'winning_template_id');
    }
}
