<?php

namespace App\Services;

use App\Http\Traits\ResponseHandler;
use App\Models\Category;
use App\Models\CategoryDiscount;
use App\Models\Coupon;
use App\Models\CustomerClassification;
use App\Models\CustomerClassificationDiscount;
use App\Models\Discount;
use App\Models\OrderCoupon;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductDiscount;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use App\Services\CustomerServices;

class DiscountService
{
    //--------------------------- Add customer classifications to a discount -----------------------------
    public static function addCustomerClassificationsToDiscount($customerClassificationIds, $discountId)
    {
        $ids = array_unique($customerClassificationIds);
        // get the customer classifications with the given Ids
        $customerClassifications = CustomerClassification::whereIn('customer_classification_id', $ids)->get();
        // Check if any of the provided classifications were not found
        if ($customerClassifications->count() !== count($ids)) {
            if ($customerClassifications->count() == 0) {
                return ResponseHandler::errorResponse(__('messages.not-found'), 404);
            } else {
                return ResponseHandler::errorResponse(__('messages.some-not-found'), 404);
            }
        }

        $discount = Discount::find($discountId);
        $changes = $discount->customerClassifications()->sync($ids);

        // Check if there were any changes
        if (count($changes['attached']) == 0 && count($changes['detached']) == 0) {
            return false; // No changes
        }

        // Return there's changes
        return true;
    }

    //--------------------------- add products&Categories for the discount -----------------------------
    public static function addProductsAndCategoriesToDiscount($discountByIds, Discount $discount)
    {
        $ids = array_unique($discountByIds);

        // define the relations and the model
        $relation = null;
        $relatedModel = null;

        // check if the discount is on products or categories
        if ($discount->discount_by === 'products') {
            $relation = 'products';
            $relatedModel = Product::class;
        } elseif ($discount->discount_by === 'categories') {
            $relation = 'categories';
            $relatedModel = Category::class;
        }

        // Check if all the entities exist
        $relatedEntities = $relatedModel::whereIn((new $relatedModel)->getKeyName(), $ids)->get();
        $relatedEntityIds = $relatedEntities->pluck((new $relatedModel)->getKeyName())->toArray();

        if (count($relatedEntityIds) !== count($ids)) {
            // Check if some entities don't exist
            if (count($relatedEntityIds) == 0) {
                return ResponseHandler::errorResponse(__('messages.not-found'), 404);
            }
            return ResponseHandler::errorResponse(__('messages.some-not-found'), 404);
        }

        // Sync the entities to the discount
        $changes = $discount->{$relation}()->sync($ids);

        // Check if there were any changes
        if (count($changes['attached']) == 0 && count($changes['detached']) == 0) {
            return false; // No changes
        }

        // Return there's changes
        return true;
    }

    // ---------------------------------- handle the discounts in the new order ----------------------------------------------
    public static function handelProductFlashSales(&$cartItems, $translate = null)
    {
        // Calculate discounted prices for each product
        foreach ($cartItems as $cartItem) {
            // Get the product

            $product = Product::whereNull('deleted_at')->where('status', 1)->find($cartItem->product_id);

            // Get the product variant
            $productVariant = null;
            if ($cartItem->product_variant_id) {
                $productVariant = ProductVariant::where('product_id', $cartItem->product_id)->find($cartItem->product_variant_id);
            }

            // get product info
            $price = $product->price;
            $localStock = $product->local_stock;
            $onlineStock = $product->online_stock;
            $productName = null; // important
            // get free shipping flag
            $p = Product::whereNull('deleted_at')->where('status', 1)->find($cartItem->product_id);
            $isFreeShipping = $p->is_free_shipping;

            if ($translate === false) {
                $productName = $product->getTranslations('product_name');
            } else if ($translate === true) {
                $productName =  $product->product_name;
            }

            if ($productVariant) {
                $price = $productVariant->price;
                $localStock = $productVariant->local_stock;
                $onlineStock = $productVariant->online_stock;

                if ($translate === false) {
                    $productName = $productVariant->getTranslations('product_variant_name');
                } else if ($translate === true) {
                    $productName =  $productVariant->product_variant_name;
                }
            }

            // get product discounts
            $discounts = DiscountService::getProductDiscounts($cartItem->product_id);
            $discountedPrices = [];

            // loop over discounts and get the greatest discounted price
            foreach ($discounts as $discount) {
                $discountValue = $discount->discount_value;
                $discountType = $discount->discount_value_type;
                if ($discountType === 'percentage') {
                    // Apply percentage discount
                    $discountedPrices[] = $price - ($price *  ($discountValue / 100));
                } elseif ($discountType === 'fixed_value') {
                    // Apply fixed amount discount
                    $discountedPrices[] = max($price - $discountValue, 0);
                }
            }

            // Get the greatest discounted price
            $bestPrice = $price;
            if (!empty($discountedPrices)) {
                $bestPrice = min($discountedPrices);
            }

            // Update the cart item
            $cartItem->is_free_shipping = $isFreeShipping;
            $cartItem->price = $price;
            $cartItem->price_after_discount = round($bestPrice, 2);
            //Calculate discount value
            $cartItem->discount_value = round($price - $bestPrice, 2);
            // Calculate discount percentage
            if ($price > 0) {
                $cartItem->discount_percentage = round((($price - $bestPrice) / $price) * 100, 2);
            } else {
                $cartItem->discount_percentage = 0;
            }

            $cartItem->local_stock = $localStock;
            $cartItem->online_stock = $onlineStock;
            if ($productName) { // important
                $cartItem->product_name = $productName;
            }
        }
        return $cartItems;
    }

    //------------------------------------ handel coupons in the new order -----------------------------------------------
    public static function handelCoupons($couponCodes, $dataBeforeCoupon)
    {
        $orderCoupons = [];
        $allCouponsDiscounts = 0;
        //get the coupons
        $coupons = Coupon::whereIn('coupon_code', $couponCodes)
            ->join('discounts', 'discounts.discount_id', '=', 'coupons.discount_id')
            ->notExpired()
            ->with('discount')
            ->get();

        //check if there is no exists coupon and return the no exists coupon code the user used
        $validCouponCodes = $coupons->pluck('coupon_code')->toArray();

        // Find coupon codes that do not exist in the list of valid coupon codes
        $invalidCouponCodes = array_diff($couponCodes, $validCouponCodes);

        // Check if there are any invalid coupon codes
        if (!empty($invalidCouponCodes)) {
            // Handle the case where there are invalid coupon codes
            return ResponseHandler::errorResponse(__('messages.invalid-coupon', ['coupon' => implode(',', $invalidCouponCodes)]), 400);
        }

        // initialize the data
        $dataAfterCoupon = $dataBeforeCoupon;

        // re arrange coupons to put the on_order (discount->apply_discount_on) coupons the last applied coupons
        $coupons = CustomerServices::rearrangeCoupons($coupons);

        // Loop through each coupon in the $coupons array
        foreach ($coupons as $coupon) {
            // make order coupon instance
            $orderCoupon = new OrderCoupon();
            $orderCoupon->coupon_code = $coupon->coupon_code;
            $orderCoupon->apply_discount_on = $coupon->apply_discount_on;
            $orderCoupon->discount_value = $coupon->discount_value;
            $orderCoupon->discount_value_type = $coupon->discount_value_type;

            // add to array
            $orderCoupons[] = $orderCoupon;

            // Check if the coupon is classified and if the user is authenticated
            if ($coupon->discount->classified == true) {

                // Fetch the classification IDs associated with the discount
                $classificationIds = CustomerClassificationDiscount::where('discount_id', $coupon->discount->discount_id)->get();

                // Get the user IDs associated with the classification IDs
                $userIds = CustomerServices::getUsersByClassificationIds($classificationIds);

                // Check if the current user's ID is in the array of user IDs
                if (!session()->has('user_id') || !in_array(session('user_id'), $userIds)) {
                    return ResponseHandler::errorResponse(__('messages.invalid-coupon', ['coupon' => $coupon->coupon_code]), 400);
                }
            }
            // If the coupon is not classified or the user is not authenticated, apply the coupon to the order data without any additional checks
            $dataAfterCoupon = DiscountService::applyCoupon($coupon, $dataAfterCoupon['products'], $dataAfterCoupon['order_subtotal'], $dataAfterCoupon['order_shipping'], $dataAfterCoupon['order_discount'], $dataAfterCoupon['shipping_discount'], $dataAfterCoupon['product_discount']);
            if ($dataAfterCoupon instanceof JsonResponse) {
                return $dataAfterCoupon;
            }
            $allCouponsDiscounts += $dataAfterCoupon['coupon_discount'];
        }

        $dataAfterCoupon['coupons_discount'] = $allCouponsDiscounts;
        $dataAfterCoupon['order_coupons'] = $orderCoupons;
        return $dataAfterCoupon;
    }

    //-------------------------- apply the coupons in the new order -----------------------------------
    public static function applyCoupon($coupon, $products, $orderSubtotal, $orderShipping, $order_discount, $shipping_discount, $product_discount = false)
    {
        $couponDiscount = 0;
        switch ($coupon->discount->apply_discount_on) {
            case 'on_order':
                if (!$order_discount) { // Check if order discount has not been applied
                    if ($coupon->discount->discount_value_type == 'percentage') {
                        $couponDiscount = ($orderSubtotal * ($coupon->discount->discount_value / 100));
                    } elseif ($coupon->discount->discount_value_type == 'fixed_value') {
                        $couponDiscount = max($coupon->discount->discount_value, 0);
                    }
                    $order_discount = true; // Update the variable to indicate discount applied
                } else {
                    if ($coupon->is_combined_with_order_discounts) {
                        if ($coupon->discount->discount_value_type == 'percentage') {
                            $couponDiscount = ($orderSubtotal * ($coupon->discount->discount_value / 100));
                        } elseif ($coupon->discount->discount_value_type == 'fixed_value') {
                            $couponDiscount = max($coupon->discount->discount_value, 0);
                        }
                    } else {
                        // return error response
                        return ResponseHandler::errorResponse(__('messages.coupon-can-not-be-combined-with-order-discounts', ['coupon' => $coupon->coupon_code]), 400);
                    }
                }
                break;
            case 'on_product':
                $totalProductDiscount = 0;
                // check if the coupon can be combined with previous product coupons
                if ($product_discount == true && $coupon->is_combined_with_product_discounts == false) {
                    foreach ($products as &$product) {
                        if ($coupon->is_combined_with_flash_sales == false && $product['price'] != $product['price_after_discount']) {
                            return ResponseHandler::errorResponse(__('messages.coupon-can-not-be-combined-with-flash-sales', ['coupon' => $coupon->coupon_code]), 400);
                        }
                        $originalPrice = $product['price_after_discount'];
                        if ($coupon->discount->discount_value_type == 'percentage') {
                            $product['price_after_discount'] -= $originalPrice * ($coupon->discount->discount_value / 100);
                        } elseif ($coupon->discount->discount_value_type == 'fixed_value') {
                            $product['price_after_discount'] = max($originalPrice - $coupon->discount->discount_value, 0);
                        }
                        $totalProductDiscount += $originalPrice - $product['price_after_discount'];
                        $couponDiscount = $totalProductDiscount;
                    }
                    $product_discount = true;
                } else {

                    return ResponseHandler::errorResponse(__('messages.coupon-can-not-be-combined-with-product-discounts', ['coupon' => $coupon->coupon_code]), 400);
                }
                break;

            case 'on_shipping':
                if (!$shipping_discount) {
                    if ($coupon->discount->discount_value_type == 'percentage') {
                        $orderShipping = $orderShipping - ($orderShipping * ($coupon->discount->discount_value / 100));
                    } elseif ($coupon->discount->discount_value_type == 'fixed_value') {
                        $orderShipping = $orderShipping - max($coupon->discount->discount_value, 0);
                    }
                    $shipping_discount = true;
                } else {
                    if ($coupon->is_combined_with_shipping_discounts) {
                        if ($coupon->discount->discount_value_type == 'percentage') {
                            $orderShipping = $orderShipping - ($orderShipping * ($coupon->discount->discount_value / 100));
                        } elseif ($coupon->discount->discount_value_type == 'fixed_value') {
                            $orderShipping = $orderShipping - max($coupon->discount->discount_value, 0);
                        }
                    } else {
                        // return error response
                        return ResponseHandler::errorResponse(__('messages.coupon-can-not-be-combined-with-shipping-discounts', ['coupon' => $coupon->coupon_code]), 400);
                    }
                }
                break;
        }
        return [
            'products' => $products,
            'order_discount' => $order_discount,
            'order_shipping' => $orderShipping,
            'product_discount' => $product_discount,
            'shipping_discount' => $shipping_discount,
            'coupon_discount' => $couponDiscount,
            'order_subtotal' => round($orderSubtotal - $couponDiscount, 2)
        ];
    }

    //------------------------------------------- get product discounts ----------------------------------------------------------
    public static function getProductDiscounts($productId)
    {
        // get product discounts
        $discountIds = ProductDiscount::where('product_id', $productId)->pluck('discount_id')->toArray();
        $discountIds = DiscountService::getDiscountIdsForCategory($productId, $discountIds);
        $discounts = Discount::whereIn('discount_id', $discountIds)
            ->active()
            ->notDeleted()
            ->TypeOffer()
            ->where('end_date', '>', now()) // Assuming discounts are active if end_date is in the future
            ->select(
                "discounts.discount_id",
                TranslateService::localizedColumn('discount_name'),
                "discount_value_type",
                "discount_value",
                "start_date",
                "end_date",
                "discounts.created_at"
            )
            ->get();

        return $discounts;
    }

    //---------------------------------- get the discount ids for category in the new order----------------------------------
    public static function getDiscountIdsForCategory($productId, $discountIds)
    {
        // Get all category IDs for the given product
        $categoryIds = ProductCategory::where('product_id', $productId)->pluck('category_id')->toArray();
        // Fetch discount IDs for each category
        foreach ($categoryIds as $categoryId) {
            $newDiscountIds = CategoryDiscount::where('category_id', $categoryId)->pluck('discount_id')->toArray();
            // Merge the new discount IDs with the existing ones
            $discountIds = array_merge($discountIds, $newDiscountIds);
        }
        return $discountIds;
    }
}
