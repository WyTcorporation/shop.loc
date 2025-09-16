<?php

namespace Database\Factories;

use App\Models\LoyaltyPointTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoyaltyPointTransaction>
 */
class LoyaltyPointTransactionFactory extends Factory
{
    protected $model = LoyaltyPointTransaction::class;

    public function definition(): array
    {
        $points = $this->faker->numberBetween(-200, 200);
        $points = $points === 0 ? 25 : $points;
        $type = $points >= 0
            ? LoyaltyPointTransaction::TYPE_EARN
            : LoyaltyPointTransaction::TYPE_REDEEM;

        return [
            'user_id' => User::factory(),
            'order_id' => null,
            'type' => $type,
            'points' => $points,
            'amount' => $this->amountForPoints($points),
            'description' => $this->faker->sentence(),
            'meta' => null,
        ];
    }

    public function earn(?int $points = null): static
    {
        $value = abs($points ?? $this->faker->numberBetween(25, 200));

        return $this->state(function () use ($value) {
            return [
                'type' => LoyaltyPointTransaction::TYPE_EARN,
                'points' => $value,
                'amount' => $this->amountForPoints($value),
            ];
        });
    }

    public function redeem(?int $points = null): static
    {
        $value = abs($points ?? $this->faker->numberBetween(25, 150));

        return $this->state(function () use ($value) {
            return [
                'type' => LoyaltyPointTransaction::TYPE_REDEEM,
                'points' => -$value,
                'amount' => $this->amountForPoints(-$value),
            ];
        });
    }

    public function adjustment(?int $points = null): static
    {
        $value = $points ?? $this->faker->numberBetween(-100, 100);
        $value = $value === 0 ? 10 : $value;

        return $this->state(function () use ($value) {
            return [
                'type' => LoyaltyPointTransaction::TYPE_ADJUST,
                'points' => $value,
                'amount' => $this->amountForPoints($value),
            ];
        });
    }

    private function amountForPoints(int $points): float
    {
        $ratio = (float) config('shop.loyalty.redeem_value', 0.1);

        return round($points * $ratio, 2);
    }
}
