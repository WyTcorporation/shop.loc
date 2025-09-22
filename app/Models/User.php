<?php

namespace App\Models;

use App\Enums\Permission;
use App\Enums\Role;
use App\Mail\ResetPasswordMail;
use App\Models\Category;
use App\Models\TwoFactorSecret;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $appends = [
        'two_factor_enabled',
        'two_factor_confirmed_at',
    ];

    protected string $guard_name = 'web';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        if ($this->hasAnyRole([Role::Administrator->value, Role::Accountant->value])) {
            return true;
        }

        return $this->hasAnyPermission(Permission::values());
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function loyaltyPointTransactions(): HasMany
    {
        return $this->hasMany(LoyaltyPointTransaction::class);
    }

    public function vendor(): HasOne
    {
        return $this->hasOne(Vendor::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)
            ->withTimestamps();
    }

    public function permittedCategoryIds(): Collection
    {
        if ($this->relationLoaded('categories')) {
            return $this->categories->pluck('id');
        }

        return $this->categories()
            ->pluck('categories.id');
    }

    public function loyaltyPointsBalance(): int
    {
        return (int) $this->loyalty_points_balance;
    }

    public function getLoyaltyPointsBalanceAttribute(): int
    {
        if (array_key_exists('loyalty_points_balance', $this->attributes)) {
            return (int) $this->attributes['loyalty_points_balance'];
        }

        if ($this->relationLoaded('loyaltyPointTransactions')) {
            return (int) $this->loyaltyPointTransactions->sum('points');
        }

        return (int) $this->loyaltyPointTransactions()->sum('points');
    }


    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function twoFactorSecret(): HasOne
    {
        return $this->hasOne(TwoFactorSecret::class);
    }

    public function getTwoFactorEnabledAttribute(): bool
    {
        return (bool) $this->twoFactorSecret?->isConfirmed();
    }

    public function getTwoFactorConfirmedAtAttribute(): ?string
    {
        return $this->twoFactorSecret?->confirmed_at?->toISOString();
    }

    public function sendPasswordResetNotification($token): void
    {
        $locale = resolveMailLocale();

        Mail::to($this)
            ->locale($locale)
            ->queue((new ResetPasswordMail($this, $token))->locale($locale));
    }
}
