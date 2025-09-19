<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    use HasFactory;
    use HasTranslations {
        HasTranslations::initializeHasTranslations as protected initializeTranslationsTrait;
    }

    protected $fillable = [
        'code',
        'name',
        'name_translations',
        'description',
        'description_translations',
    ];

    protected $casts = [
        'name_translations' => 'array',
        'description_translations' => 'array',
    ];

    public function initializeHasTranslations(): void
    {
        $this->translatable = ['name', 'description'];
        $this->initializeTranslationsTrait();
    }

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
                'name_translations' => [config('app.locale') => 'Main Warehouse'],
                'description_translations' => [],
            ]);
    }
}
