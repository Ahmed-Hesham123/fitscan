<?php

namespace App\Services;

use App\Models\Wishlist;
use App\Models\WishlistProduct;

class WishlistService
{
    // Add Guest WishlistTo User Wishlist
    public static function addGuestWishlistToUserWishlist($user)
    {
        $guestWishlist = Wishlist::with('wishlist_products')->find(session('wishlist_id'));

        $userWishlist = Wishlist::where('user_id', $user->user_id)->first();
        if (!$userWishlist) {
            $wishlist = new Wishlist();
            $wishlist->user_id = $user->user_id;
            $wishlist->save();
        }

        // Check if guest wishlist exists
        if ($guestWishlist) {
            // Loop through each product in the guest wishlist
            foreach ($guestWishlist->wishlist_products as $guestProduct) {
                // Check if the product already exists in the user's wishlist
                $existingProduct = $userWishlist->wishlist_products
                    ->where('product_id', $guestProduct->product_id)
                    ->where('product_variant_id', $guestProduct->product_variant_id)
                    ->first();

                if ($existingProduct) {
                    // If product exists, update quantity
                    // $existingProduct->wishlist_product_quantity += $guestProduct->wishlist_product_quantity;
                    $existingProduct->save();
                } else {
                    // If product does not exist, Create a new WishlistProduct instance
                    $newWishlistProduct = new WishlistProduct();
                    $newWishlistProduct->wishlist_id = $userWishlist->wishlist_id; // Assuming 'wishlist_id' is the correct foreign key
                    $newWishlistProduct->product_id = $guestProduct->product_id;
                    $newWishlistProduct->product_variant_id = $guestProduct->product_variant_id;
                    $newWishlistProduct->save();
                }
            }
            // Delete the guest wishlist after transferring its contents
            $guestWishlist->delete();
        }
    }
}
