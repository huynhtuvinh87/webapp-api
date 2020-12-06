<?php

namespace App\Lib;


class StripeUtils
{
    public static function isCouponValid($coupon)
    {
        \Stripe\Stripe::setApiKey(\Config::get('services.stripe.secret'));

        try {
            $coupon = \Stripe\Coupon::retrieve($coupon);

            return $coupon->valid;
        } catch(\Exception $e) {
            return false;
        }
    }

    public static function describeCoupon($coupon){

        if (self::isCouponValid($coupon)){
            return \Stripe\Coupon::retrieve($coupon);
        }

        return null;

    }

    public static function getStripePlans($plan)
    {
        \Stripe\Stripe::setApiKey(\Config::get('services.stripe.secret'));

        try {
            $plans = \Stripe\Plan::retrieve($plan);

            return $plans;
        } catch(\Exception $e) {
            return false;
        }
    }
}

