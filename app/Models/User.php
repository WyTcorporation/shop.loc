<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
        return true; // TODO: звузимо пізніше (роль is_admin тощо)
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
}
