<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CustomerSegment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'conditions',
        'is_active',
    ];

    protected $casts = [
        'conditions' => 'array',
        'is_active' => 'boolean',
    ];

    public function campaigns(): BelongsToMany
    {
        return $this->belongsToMany(MarketingCampaign::class, 'campaign_segment', 'segment_id', 'campaign_id')
            ->withTimestamps();
    }
}
