<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CampaignTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'channel',
        'subject',
        'content',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function campaigns(): HasMany
    {
        return $this->hasMany(MarketingCampaign::class, 'template_id');
    }
}
