<?php

namespace App\Services\Carts;

use App\Data\CartTotals;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\User;

class CartPricingService
{
    public static function calculate(Cart $cart, ?int $overridePoints = null, ?Coupon $couponOverride = null): CartTotals
    {
        $cart->loadMissing('items.product', 'coupon', 'user');

        $subtotal = (float) $cart->items->sum(fn (CartItem $item) => (float) $item->price * (int) $item->qty);

        $coupon = $couponOverride ?? $cart->coupon;
        $couponCode = $coupon?->code ?? $cart->coupon_code;
        $couponDiscount = 0.0;

        if ($coupon) {
            if (! self::couponIsApplicable($coupon, $cart, $subtotal)) {
                $coupon = null;
                $couponCode = null;
            } else {
                $couponDiscount = $coupon->calculateDiscount($subtotal);
            }
        }

        $user = $cart->user instanceof User ? $cart->user : null;
        $availablePoints = $user?->loyaltyPointsBalance() ?? 0;

        $pointsUsed = $overridePoints ?? (int) $cart->loyalty_points_used;
        $pointsUsed = max(0, (int) $pointsUsed);

        $redeemValue = (float) config('shop.loyalty.redeem_value', 0.1);
        $redeemValue = $redeemValue > 0 ? $redeemValue : 0.0;

        $maxPercent = (float) config('shop.loyalty.max_redeem_percent', 0.5);
        $maxPercent = min(max($maxPercent, 0.0), 1.0);

        $amountEligible = max(0.0, $subtotal - $couponDiscount);
        $maxRedeemablePoints = 0;

        if ($redeemValue > 0) {
            $maxByPercent = (int) floor(($amountEligible * $maxPercent) / $redeemValue + 1e-9);
            $maxByAmount = (int) floor($amountEligible / $redeemValue + 1e-9);
            $maxRedeemablePoints = max(0, min($maxByPercent, $maxByAmount));
        }

        $pointsUsed = min($pointsUsed, $availablePoints, $maxRedeemablePoints);

        $pointsValue = round($pointsUsed * $redeemValue, 2);

        if ($pointsValue > $amountEligible) {
            $pointsValue = round($amountEligible, 2);
            $pointsUsed = $redeemValue > 0 ? (int) floor($pointsValue / $redeemValue + 1e-9) : 0;
        }

        $couponDiscount = round(min($couponDiscount, $subtotal), 2);
        $subtotal = round($subtotal, 2);
        $total = max(0, round($subtotal - $couponDiscount - $pointsValue, 2));

        return new CartTotals(
            subtotal: $subtotal,
            couponDiscount: $couponDiscount,
            coupon: $coupon,
            couponCode: $couponCode,
            pointsUsed: $pointsUsed,
            pointsValue: $pointsValue,
            total: $total,
            availablePoints: (int) $availablePoints,
            maxRedeemablePoints: $maxRedeemablePoints,
        );
    }

    public static function couponIsApplicable(Coupon $coupon, Cart $cart, float $subtotal): bool
    {
        if (! $coupon->isCurrentlyActive()) {
            return false;
        }

        if ($subtotal < (float) $coupon->min_cart_total) {
            return false;
        }

        if ($coupon->per_user_limit !== null) {
            $user = $cart->user;
            if ($user instanceof User) {
                if ($coupon->usageCountForUser($user) >= $coupon->per_user_limit) {
                    return false;
                }
            }
        }

        return true;
    }

    public static function syncCartAdjustments(Cart $cart, CartTotals $totals): void
    {
        $updated = false;

        if ($totals->coupon) {
            if ($cart->coupon_id !== $totals->coupon->id) {
                $cart->coupon()->associate($totals->coupon);
                $updated = true;
            }
        } elseif ($cart->coupon_id !== null) {
            $cart->coupon()->dissociate();
            $updated = true;
        }

        if ($cart->coupon_code !== $totals->couponCode) {
            $cart->coupon_code = $totals->couponCode;
            $updated = true;
        }

        if ($cart->loyalty_points_used !== $totals->pointsUsed) {
            $cart->loyalty_points_used = $totals->pointsUsed;
            $updated = true;
        }

        if ($updated) {
            $cart->save();
            $cart->setRelation('coupon', $totals->coupon);
        }
    }
}
