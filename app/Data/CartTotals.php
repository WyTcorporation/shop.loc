<?php

namespace App\Data;

use App\Models\Coupon;

class CartTotals
{
    public function __construct(
        public float $subtotal,
        public float $couponDiscount,
        public ?Coupon $coupon,
        public ?string $couponCode,
        public int $pointsUsed,
        public float $pointsValue,
        public float $total,
        public int $availablePoints,
        public int $maxRedeemablePoints,
    ) {
    }

    public function discountTotal(): float
    {
        return round($this->couponDiscount + $this->pointsValue, 2);
    }

    public function toArray(): array
    {
        return [
            'subtotal' => $this->subtotal,
            'coupon_discount' => $this->couponDiscount,
            'coupon_code' => $this->couponCode,
            'loyalty_points_used' => $this->pointsUsed,
            'loyalty_points_value' => $this->pointsValue,
            'discount_total' => $this->discountTotal(),
            'total' => $this->total,
            'available_points' => $this->availablePoints,
            'max_redeemable_points' => $this->maxRedeemablePoints,
        ];
    }
}
