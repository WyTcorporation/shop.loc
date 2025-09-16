<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
    ];

    public function stocks(): HasMany
    {
        return $this->hasMany(ProductStock::class);
    }

    public static function getDefault(): self
    {
        return static::query()->orderBy('id')->first()
            ?? static::create([
                'code' => 'MAIN',
                'name' => 'Main Warehouse',
            ]);
    }
}
