<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartProduct;

class CartService
{
    // Add Guest CartTo User Cart
    public static function addGuestCartToUserCart($user)
    {
        $guestCart = Cart::with('cart_products')->find(session('cart_id'));

        $userCart = Cart::where('user_id', $user->user_id)->first();
        if (!$userCart) {
            $userCart = new Cart();
            $userCart->user_id = $user->user_id;
            $userCart->save();
        }

        // Check if guest cart exists
        if ($guestCart) {
            // Loop through each product in the guest cart
            foreach ($guestCart->cart_products as $guestProduct) {
                // Check if the product already exists in the user's cart
                $existingProduct = $userCart->cart_products
                    ->where('product_id', $guestProduct->product_id)
                    ->where('product_variant_id', $guestProduct->product_variant_id)
                    ->first();

                if ($existingProduct) {
                    // If product exists, update quantity
                    $existingProduct->cart_product_quantity += $guestProduct->cart_product_quantity;
                    $existingProduct->save();
                } else {
                    // If product does not exist, Create a new CartProduct instance
                    $newCartProduct = new CartProduct();
                    $newCartProduct->cart_id = $userCart->cart_id; // Assuming 'cart_id' is the correct foreign key
                    $newCartProduct->product_id = $guestProduct->product_id;
                    $newCartProduct->product_variant_id = $guestProduct->product_variant_id;
                    $newCartProduct->cart_product_quantity = $guestProduct->cart_product_quantity;
                    $newCartProduct->save();
                }
            }
            // Delete the guest cart after transferring its contents
            $guestCart->delete();
        }
    }
}
