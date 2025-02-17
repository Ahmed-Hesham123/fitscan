<?php

namespace App\Services;

class Numbers
{
    // format data prices
    public static function formatDataPrices($input)
    {
        $keysToFormat = [
            "subtotal",
            "discount_value",
            "tax_value",
            "shipping_value",
            "total",
            "price",
            'net_profit',
            'paid_money',
            'returned_money',
            'total_after_returned',
            'final_tax',
            "revenue",
            "total_earning",
            "total_tax",
            "price_before_discount",
            "price_after_discount",
            "coupons_discount_value",
        ];

        foreach ($input as &$item) {
            // handle array of objects
            if (is_object($item) || is_array($item)) {
                $item = self::formatDataPrices($item);
            }

            // handel normal values
            else {
                if (is_array($input) || is_object($input)) {
                    foreach ($keysToFormat as $key) {
                        if (isset($input[$key])) {
                            if (!is_string($input[$key])) {
                                $input[$key] = number_format($input[$key], 2);
                            }
                        }
                    }
                }
            }
        }

        return $input;
    }
}
