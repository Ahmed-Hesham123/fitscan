<?php

namespace App\Services;

use App\Models\CustomerClassification;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CustomerServices
{

    //---------------------------get user with Classification IDs-------------------------------------------
    public static function getUsersByClassificationIds($ids)
    {
        $users = [];
        foreach ($ids as $id) {
            $customerClassification = CustomerClassification::where('customer_classification_id', $id)->first();
            if ($customerClassification) {
                if ($customerClassification->attribute == 'number_of_purchases') {
                    switch ($customerClassification->condition) {
                        case 'more_than':
                            $value = $customerClassification->value_1;
                            $value = 1; // Ensure $value is an integer
                            $userIDs = DB::table('orders')
                                ->selectRaw('user_id')
                                ->groupBy('user_id')
                                ->havingRaw('COUNT(*) > ?', [$value])
                                ->get()
                                ->pluck('user_id');
                            $users = array_merge($users, $userIDs->toArray());
                            dd($users);
                            break;
                        case 'less_than':
                            $userIDs = DB::table('orders')
                                ->select('user_id')
                                ->groupBy('user_id')
                                ->havingRaw('COUNT(*) < ?', [$customerClassification->value_1])
                                ->get()
                                ->pluck('user_id');
                            $users = array_merge($users, $userIDs->toArray());
                            break;
                        case 'between':
                            $start = $customerClassification->value_1;
                            $end = $customerClassification->value_2;
                            $userIDs = DB::table('orders')
                                ->select('user_id')
                                ->groupBy('user_id')
                                ->havingRaw('COUNT(*) >= ? AND COUNT(*) <= ?', [$start, $end])
                                ->get()
                                ->pluck('user_id');
                            $users = array_merge($users, $userIDs->toArray());
                            break;
                    }
                }
                if ($customerClassification->attribute == 'date_of_account_creation') {
                    switch ($customerClassification->condition) {
                        case 'less_than':
                            $usersIDs = User::whereDate('created_at', '<', $customerClassification->value)->pluck('id');
                            $users = array_merge($users, $usersIDs->toArray());
                            break;
                        case 'more_than':
                            $usersIDs = User::whereDate('created_at', '>', $customerClassification->value)->pluck('id');
                            $users = array_merge($users, $usersIDs->toArray());
                            break;
                        case 'between':
                            $start = $customerClassification->value_1;
                            $end = $customerClassification->value_2;
                            $usersIDs = User::whereDate('created_at', '>=', $start)->whereDate('created_at', '<=', $end)->pluck('id');
                            $users = array_merge($users, $usersIDs->toArray());
                            break;
                    }
                }
            }
        }
        return $users;
    }
    //------------------------------------ rearrange coupons -------------------------------------
    public static function rearrangeCoupons($couponsArray): Collection
    {
        $coupons = collect($couponsArray);

        // Using the partition method to separate and then concatenate the collection
        list($onOrder, $others) = $coupons->partition(function ($coupon) {
            return $coupon['apply_discount_on'] === 'on_order';
        });

        // Concatenate the collections: first those not on_order, then on_order
        return $others->merge($onOrder);
    }
    //-------------------------------------get product attributes ------------------------------------
    public static function getProductAttributes($productId, $productVariantId)
    {
        $attributes = ProductVariant::where('product_id', $productId)
            ->where('product_variant_id', $productVariantId)
            ->with('attributes')
            ->with('product_variant_attributes')
            ->first();
        return $attributes;
    }
}
