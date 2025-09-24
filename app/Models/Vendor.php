<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Support\Phone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    use HasFactory;
    use HasTranslations {
        HasTranslations::initializeHasTranslations as protected initializeTranslationsTrait;
    }

    protected $fillable = [
        'user_id',
        'name',
        'name_translations',
        'slug',
        'contact_email',
        'contact_phone',
        'description',
        'description_translations',
    ];

    protected $casts = [
        'name_translations' => 'array',
        'description_translations' => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $vendor): void {
            $vendor->syncBaseColumnFromTranslations('name');
            $vendor->syncBaseColumnFromTranslations('description');
        });
    }

    public function initializeHasTranslations(): void
    {
        $this->translatable = ['name', 'description'];
        $this->initializeTranslationsTrait();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        if (! $user?->vendor) {
            return $query;
        }

        return $query->whereKey($user->vendor->id);
    }

    protected function contactPhone(): Attribute
    {
        return Attribute::make(
            get: static fn (?string $value): ?string => Phone::format($value),
            set: static fn (?string $value): ?string => Phone::normalize($value),
        );
    }

    protected function syncBaseColumnFromTranslations(string $attribute): void
    {
        $translations = $this->getAttribute($attribute . '_translations');

        if (! is_array($translations) || $translations === []) {
            return;
        }

        $primaryLocale = config('app.locale');
        $fallbackLocale = config('app.fallback_locale');

        $candidate = $translations[$primaryLocale]
            ?? ($fallbackLocale ? ($translations[$fallbackLocale] ?? null) : null)
            ?? reset($translations)
            ?? null;

        if ($candidate !== null && $candidate !== '') {
            $this->attributes[$attribute] = $candidate;
        }
    }
}
