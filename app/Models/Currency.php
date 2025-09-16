<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'rate',
    ];

    protected $casts = [
        'rate' => 'float',
    ];

    public function setCodeAttribute($value): void
    {
        $this->attributes['code'] = strtoupper((string) $value);
    }
}
