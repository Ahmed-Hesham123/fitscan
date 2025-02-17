<?php

namespace App\Services;

use App\Models\Discount;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductDiscount;
use App\Models\ProductMedia;
use App\Models\ProductOptionValue;
use App\Models\ProductOptionValueMedia;
use App\Models\ProductVariant;
use App\Models\Review;
use Illuminate\Support\Facades\DB;

class ProductsService
{
    // get Variant Attributes
    public static function getVariantAttributes($variantId)
    {
        $attributes_ar = '';
        $attributes_en = '';

        $productVariant = ProductVariant::select('product_variant_id')->with('product_variant_attributes')->find($variantId);

        foreach ($productVariant->product_variant_attributes as $option) {
            // Concatenate the Arabic and English values for each option
            $attributes_ar .= $option->value_ar . ' - ';
            $attributes_en .= $option->value_en . ' - ';
        }

        // Remove the trailing ' - ' from both strings
        $attributes_ar = rtrim($attributes_ar, ' - ');
        $attributes_en = rtrim($attributes_en, ' - ');

        return ['en' => $attributes_en, 'ar' => $attributes_ar];
    }

    //--------------------------------- get media preview -----------------------------------------------
    public static function addMediaPreview(&$product)
    {
        // simple media in products table, cart table, or order items
        if ($product->product_type == "simple" || (!$product->product_variant_id && $product->product_type != "variable")) {
            $firstMedia = ProductMedia::where("product_media.product_id", "=", $product->product_id)
                ->join('media', 'product_media.media_id', '=', 'media.media_id')
                ->first();
        }

        // variable media in products table
        elseif ($product->product_type == "variable") {
            $firstMedia = ProductOptionValueMedia::join('media', 'product_option_value_media.media_id', '=', 'media.media_id')
                ->join('product_option_values', 'product_option_value_media.product_option_value_id', '=', 'product_option_values.product_option_value_id')
                ->join('product_options', 'product_option_values.product_option_id', '=', 'product_options.product_option_id')
                ->join('products', 'products.product_id', '=', 'product_options.product_id')
                ->where('product_options.has_media', true)
                // ->where('media_type', "image")
                ->where('products.product_id', $product->product_id)
                ->first();
        }


        // variable media in cart table or order items
        elseif ($product->product_variant_id) {
            $firstMedia = ProductOptionValueMedia::join('media', 'product_option_value_media.media_id', '=', 'media.media_id')
                ->join('product_option_values', 'product_option_value_media.product_option_value_id', '=', 'product_option_values.product_option_value_id')
                ->join('product_variant_attributes', 'product_variant_attributes.product_option_value_id', '=', 'product_option_values.product_option_value_id')
                // ->where('media_type', "image")
                ->where('product_variant_id', $product->product_variant_id)
                ->first();
        }


        $product->media_file_path = $firstMedia ? $firstMedia->media_file_path : null;
        return $product;
    }


    //--------------------------------- get related products -----------------------------------------------
    public static function getRelatedProducts($productId, $limit)
    {
        $relatedProducts = Product::whereNull('products.deleted_at')
            ->where('products.status', 1)
            ->with('categories')
            ->leftJoin('brands', 'brands.brand_id', '=', 'products.brand_id')
            // get the products that have similar tags
            ->WhereHas('tags', function ($query) use ($productId) {
                $query->whereIn('tags.tag_id', function ($subQuery) use ($productId) {
                    $subQuery->select('product_tags.tag_id')
                        ->from('product_tags')
                        ->where('product_tags.product_id', $productId);
                });
            })
            ->select(
                'products.product_id',
                TranslateService::localizedColumn('product_name'),
                TranslateService::localizedColumn('description'),
                'products.brand_id',
                'products.price',
                'products.product_type',
                'brands.brand_img',
                'products.is_refundable',
                'products.is_exchangeable',
                'products.is_cancelable',
                'products.local_stock',
                'products.online_stock',
                TranslateService::localizedColumn('brand_name', null, true),
            )
            // get the average rating and number of reviews for each product
            ->selectSub(function ($query) {
                $query->selectRaw('COALESCE(AVG(reviews.rating), 0)')
                    ->from('reviews')
                    ->whereColumn('reviews.product_id', 'products.product_id')
                    ->where('reviews.status', 'visible');
            }, 'average_rating')
            ->selectSub(function ($query) {
                $query->selectRaw('COUNT(*)')
                    ->from('reviews')
                    ->whereColumn('reviews.product_id', 'products.product_id')
                    ->where('reviews.status', 'visible');
            }, 'reviews_count')
            ->limit($limit)
            ->get();

        // Add products other than the current one to the new array
        $filteredProducts = [];
        foreach ($relatedProducts as $product) {
            if ($product->product_id != $productId) {
                $filteredProducts[] = $product;
            }
        }

        $productWithDiscount = DiscountService::handelProductFlashSales($filteredProducts);

        // add media
        foreach ($productWithDiscount as $product) {
            $product = ProductsService::addMediaPreview($product);
            $product = ProductsService::addDiscountFlags($product);
        }

        return $productWithDiscount;
    }

    //-------------------------------- get similar products -----------------------------------------------
    public static function getSimilarProducts($productId, $limit)
    {
        $similarProducts = Product::whereNull('products.deleted_at')
            ->where('products.status', 1)
            ->with('categories')
            ->leftJoin('brands', 'brands.brand_id', '=', 'products.brand_id')
            // get the products that have similar categories
            ->whereHas('categories', function ($query) use ($productId) {
                $query->whereIn('categories.category_id', function ($subQuery) use ($productId) {
                    $subQuery->select('product_categories.category_id')
                        ->from('product_categories')
                        ->where('product_categories.product_id', $productId);
                });
            })
            ->select(
                'products.product_id',
                TranslateService::localizedColumn('product_name'),
                TranslateService::localizedColumn('description'),
                'products.brand_id',
                'products.price',
                'products.product_type',
                'brands.brand_img',
                'products.is_refundable',
                'products.is_exchangeable',
                'products.is_cancelable',
                'products.local_stock',
                'products.online_stock',
                TranslateService::localizedColumn('brand_name', null, true),
            )
            // get the average rating and number of reviews for each product
            ->selectSub(function ($query) {
                $query->selectRaw('COALESCE(AVG(reviews.rating), 0)')
                    ->from('reviews')
                    ->whereColumn('reviews.product_id', 'products.product_id')
                    ->where('reviews.status', 'visible');
            }, 'average_rating')
            ->selectSub(function ($query) {
                $query->selectRaw('COUNT(*)')
                    ->from('reviews')
                    ->whereColumn('reviews.product_id', 'products.product_id')
                    ->where('reviews.status', 'visible');
            }, 'reviews_count')
            ->limit($limit)
            ->get();

        // Add products other than the current one to the new array
        $filteredProducts = [];
        foreach ($similarProducts as $product) {
            if ($product->product_id != $productId) {
                $filteredProducts[] = $product;
            }
        }

        $product = DiscountService::handelProductFlashSales($filteredProducts);


        // add media
        foreach ($filteredProducts as $product) {
            $product = ProductsService::addMediaPreview($product);
            $product = ProductsService::addDiscountFlags($product);
        }

        return $filteredProducts;
    }

    //-------------------------------- Get Product Media -----------------------------------------------
    public static function getProductMedia($productId, $productType)
    {
        $productMedia = [];
        // get the product productMedia
        if ($productType == 'simple') {
            $productMedia = ProductMedia::where('product_id', $productId)
                ->join('media', 'product_media.media_id', '=', 'media.media_id')
                ->select(
                    'product_media.id as product_media_id',
                    'media.media_id',
                    'media_type',
                    'media_file_path',
                )
                ->get();
        }
        // variable
        else {
            $productMedia = ProductOptionValue::where('product_id', $productId)
                ->join('product_options', 'product_option_values.product_option_id', '=', 'product_options.product_option_id')
                ->join('variant_option_values', 'variant_option_values.variant_option_value_id', '=', 'product_option_values.variant_option_value_id')
                ->where('product_options.has_media', true)
                ->select(
                    "product_option_value_id",
                    "product_options.product_option_id",
                    "variant_option_values.variant_option_value_id",
                    "product_options.variant_option_id",
                    "has_media",
                    TranslateService::localizedColumn("value", null, true),
                    "additional_data",
                )
                ->with('productOptionValueMedia')
                ->get();
        }

        return $productMedia;
    }

    //------------------------------- add is in flash-sale flag-------------------------------------
    public static function addDiscountFlags(&$product)
    {
        // get product discounts
        $discountIds = ProductDiscount::where('product_id', $product->product_id)->pluck('discount_id')->toArray();
        $discountIds = DiscountService::getDiscountIdsForCategory($product->product_id, $discountIds);
        $discounts = Discount::whereIn('discount_id', $discountIds)
            ->active()
            ->notDeleted()
            ->TypeOffer()
            ->where('end_date', '>', now()) // Assuming discounts are active if end_date is in the future
            ->get();
        if ($discounts->count() > 0) {
            foreach ($discounts as $discount) {
                if ($discount->discount_type == 'flash_sale') {
                    $product->is_flash_sale = true;
                } else {
                    $product->is_flash_sale = false;
                }
                if ($discount->discount_type == 'special_offers') {
                    $product->is_special_offer = true;
                } else {
                    $product->is_special_offer = false;
                }
            }
        } else {
            $product->is_flash_sale = false;
            $product->is_special_offer = false;
        }

        return $product;
    }

    //-------------------------------- add review flag --------------------------------------------
    public static function addReviewFlag(&$product)
    {
        // get the user
        $userId = session('user_id');
        // only the users can review the products
        if ($userId == null) {
            $product->customer_reviewed = false;
            $product->can_review = false;
            $product->is_order_item = false;
        } else {
            // check if the user ordered the product before
            $orders = Order::where('user_id', $userId)
                // ->where('order_status','completed')
                ->pluck('order_id');
            $orderItem = OrderItem::whereIn('order_id', $orders)
                ->where('product_id', $product->product_id)
                ->first();

            // check if the user reviewed the product before
            $review = Review::where('product_id', $product->product_id)->where('user_id', $userId)->first();

            // add the review flags
            $product->customer_reviewed = $review ? true : false;
            $product->is_order_item = $orderItem ? true : false;
            if ($product->is_order_item && !$product->customer_reviewed) {
                $product->can_review = true;
            } else {
                $product->can_review = false;
            }
        }

        return $product;
    }

    public static function addReviewsStatistics(&$product)
    {
        $statistics = Review::where("product_id", $product->product_id)
            ->where('status', 'visible')
            ->groupBy('product_id', 'rating')
            ->select(
                'product_id',
                'rating',
                DB::raw('count(*) as count')
            )->get();

        $totalReviews = Review::where("product_id", $product->product_id)
            ->where('status', 'visible')
            ->count();

        $reviewCounts = [
            'rating_0_count' => 0,
            'rating_0_percentage' => 0.0,
            'rating_1_count' => 0,
            'rating_1_percentage' => 0.0,
            'rating_2_count' => 0,
            'rating_2_percentage' => 0.0,
            'rating_3_count' => 0,
            'rating_3_percentage' => 0.0,
            'rating_4_count' => 0,
            'rating_4_percentage' => 0.0,
            'rating_5_count' => 0,
            'rating_5_percentage' => 0.0,
        ];

        foreach ($statistics as $stat) {
            $reviewCounts['rating_' . $stat->rating . '_count'] = $stat->count;
            $reviewCounts['rating_' . $stat->rating . '_percentage'] = ($totalReviews > 0) ? round(($stat->count / $totalReviews) * 100, 2) : 0.0;
        }

        $product->reviews_statistics = $reviewCounts;

        return $product;
    }
}
