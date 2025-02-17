<?php

namespace App\Services;

use App\Http\Traits\ResponseHandler;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingMethod;
use App\Models\TempData;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Stripe\Stripe;

class OrderServices
{
    //--------------------------- handle the checkout request------------------------------------------------
    public static function handleCheckOutRequest($request)
    {
        // make validation rules function
        $validator = Validator::make($request->all(), [
            'shipping_address' => 'required|array',
            'shipping_address.city_id' => 'nullable|numeric',
            'shipping_address.state_id' => 'nullable|numeric',
            'shipping_address.custom_city' => 'nullable|string',
            'shipping_address.first_name' => 'required|string',
            'shipping_address.last_name' => 'required|string',
            'shipping_address.contact_email' => 'required|email',
            'shipping_address.contact_phone' => 'required|string',
            'shipping_address.line_one' => 'required|string',
            'shipping_address.line_two' => 'nullable|string',
            'shipping_address.line_three' => 'nullable|string',
            'shipping_address.details' => 'nullable|string',
            'shipping_address.postal_code' => 'required|string',
            'save_address' => 'required|boolean',
            'shipping_as_billing' => 'nullable|boolean',
            'order_notes' => 'nullable|string',
            'coupons' => 'present|array',
            'coupons.*' => 'string',
            'payment_method_id' => 'required|numeric|exists:payment_methods,payment_method_id',
            'shipping_method_id' => 'required|numeric|exists:shipping_methods,shipping_method_id',
        ]);

        if ($request->input('shipping_as_billing') == 0) {
            $validator->addRules([
                'billing_address' => 'required|array',
                'billing_address.city_id' => 'nullable|numeric',
                'billing_address.state_id' => 'nullable|numeric|exists:states,state_id',
                'billing_address.custom_city' => 'nullable|string',
                'billing_address.first_name' => 'required|string',
                'billing_address.last_name' => 'required|string',
                'billing_address.contact_email' => 'required|email',
                'billing_address.contact_phone' => 'required|string',
                'billing_address.line_one' => 'required|string',
                'billing_address.line_two' => 'nullable|string',
                'billing_address.line_three' => 'nullable|string',
                'billing_address.details' => 'nullable|string',
                'billing_address.postal_code' => 'required|string',
            ]);
            // line three can be a city
            if (!$request->input('billing_address.city_id') && !$request->input('billing_address.line_three')) {
                return ResponseHandler::errorResponse(__('messages.error'), 400);
            }
        }

        //validate the data
        if ($validator->fails()) {
            return ResponseHandler::errorResponse($validator->errors()->first(), 400);
        }
        // line three can be a city
        if (!$request->input('shipping_address.city_id') && !$request->input('shipping_address.line_three')) {
            return ResponseHandler::errorResponse(__('messages.error'), 400);
        }
    }

    //--------------------------- handle the order request------------------------------------------------
    public static function handleOrderRequest($request)
    {
        // make validation rules function
        $validator = Validator::make($request->all(), [
            'save_address' => 'nullable|boolean',
            'shipping_as_billing' => 'nullable|boolean',
            'order_notes' => 'nullable|string',
            'coupons' => 'present|array',
            'coupons.*' => 'string',
            'payment_method_id' => 'nullable|numeric|exists:payment_methods,payment_method_id',
            'shipping_method_id' => 'nullable|numeric|exists:shipping_methods,shipping_method_id',
        ]);

        $validator->addRules([
            'shipping_address' => 'nullable|array',
            'shipping_address.city_id' => 'nullable|numeric',
            'shipping_address.state_id' => 'nullable|numeric',
            'shipping_address.custom_city' => 'nullable|string',
            'shipping_address.first_name' => 'nullable|string',
            'shipping_address.last_name' => 'nullable|string',
            'shipping_address.contact_email' => 'nullable|email',
            'shipping_address.contact_phone' => 'nullable|string',
            'shipping_address.line_one' => 'nullable|string',
            'shipping_address.line_two' => 'nullable|string',
            'shipping_address.line_three' => 'nullable|string',
            'shipping_address.details' => 'nullable|string',
            'shipping_address.postal_code' => 'nullable|string',
        ]);

        $validator->sometimes([
            'shipping_address.state_id',
            'shipping_address.first_name',
            'shipping_address.last_name',
            'shipping_address.contact_email',
            'shipping_address.contact_phone',
            'shipping_address.line_one',
            'shipping_address.postal_code',
        ], 'required', function ($input) {
            return isset($input->shipping_address);
        });

        if ($request->input('shipping_as_billing') == 0) {
            $validator->addRules([
                'billing_address' => 'nullable|array',
                'billing_address.city_id' => 'nullable|numeric',
                'billing_address.state_id' => 'nullable|numeric|exists:states,state_id',
                'billing_address.custom_city' => 'nullable|string',
                'billing_address.first_name' => 'nullable|string',
                'billing_address.last_name' => 'nullable|string',
                'billing_address.contact_email' => 'nullable|email',
                'billing_address.contact_phone' => 'nullable|string',
                'billing_address.line_one' => 'nullable|string',
                'billing_address.line_two' => 'nullable|string',
                'billing_address.line_three' => 'nullable|string',
                'billing_address.details' => 'nullable|string',
                'billing_address.postal_code' => 'nullable|string',
            ]);

            $validator->sometimes([
                'billing_address.state_id',
                'billing_address.first_name',
                'billing_address.last_name',
                'billing_address.contact_email',
                'billing_address.contact_phone',
                'billing_address.line_one',
                'billing_address.postal_code',
            ], 'required', function ($input) {
                return isset($input->billing_address);
            });
        }

        //validate the data
        if ($validator->fails()) {
            return ResponseHandler::errorResponse($validator->errors()->first(), 400);
        }
    }

    //--------------------------- generate new order data ------------------------------------------------
    public static function makeOrder(Request $request, $translate = false, $checkout = false)
    {

        //handle the order request
        if ($checkout == false) {
            $validation = OrderServices::handleOrderRequest($request);
            if ($validation instanceof JsonResponse) {
                return $validation;
            }
        } else {
            $validation = OrderServices::handleCheckoutRequest($request);
            if ($validation instanceof JsonResponse) {
                return $validation;
            }
        }
        //get the cart
        $cart = Cart::where('cart_id', session('cart_id'))->with('cart_products')->first();

        if (count($cart->cart_products) == 0) {
            return ResponseHandler::errorResponse(__('messages.cart-is-empty'), 400);
        }

        //get the products after the flash sales discounts
        $productsAfterFlashSalesDiscount = DiscountService::handelProductFlashSales($cart->cart_products, $translate);

        // calculate order total
        $orderSubtotal = OrderServices::calculateOrderTotal($productsAfterFlashSalesDiscount, $cart->cart_products)["total"];

        // add the new address
        // shippingAddress
        $shippingAddress = null;
        if ($request->shipping_address) {
            $shippingAddress = OrderServices::createOrderAddress($request->input('shipping_address'), "order_shipping_address");
        }

        // billingAddress
        $billingAddress = null;
        if ($request->billing_address || ($request->input('shipping_as_billing') && $shippingAddress)) {
            if ($request->input('shipping_as_billing') && $shippingAddress) {
                $billingAddress =  clone $shippingAddress;
                $billingAddress->address_type = "order_billing_address";
            } else if ($request->billing_address && !$request->input('shipping_as_billing')) {
                $billingAddress = OrderServices::createOrderAddress($request->input('billing_address'), "order_billing_address");
            }
        }

        // check if the order is free shipping
        $isFreeShipping = false;
        foreach ($productsAfterFlashSalesDiscount as $product) {
            if ($product->if_free_shipping) {
                $isFreeShipping = true;
                break;
            }
        }

        // calculate shipping charges
        if ($request->shipping_method_id) {
            if (!$isFreeShipping) {
                $shippingValue = OrderServices::calculateOrderShipping($request->shipping_method_id);
            } else {
                $shippingValue = 0;
            }
            $data['order_shipping'] = $shippingValue;
        }


        // initialize the handle coupons function data
        $data['products'] = $productsAfterFlashSalesDiscount;
        $data['order_subtotal'] = $orderSubtotal;
        $data['order_discount'] = false;
        $data['shipping_discount'] = false;
        $data['product_discount'] = false;

        // apply the coupon discounts

        $dataAfterCoupon = DiscountService::handelCoupons($request->coupons, $data);
        if ($dataAfterCoupon instanceof JsonResponse) {
            return $dataAfterCoupon;
        }

        // Calculate taxes
        $totalOrderTaxValue = 0;
        if ($request->shipping_address) {
            // $totalOrderTaxValue = TaxService::calculateTaxUsingStripe($request->shipping_address, $dataAfterCoupon['products']);
            $totalOrderTaxValue = TaxService::calculateTax($request->shipping_address, $dataAfterCoupon['products']);
            if ($totalOrderTaxValue instanceof JsonResponse) {
                return $totalOrderTaxValue;
            }
        }

        //create instance of order
        $order = new Order();

        if ($request->payment_method_id) {
            $paymentMethod = PaymentMethod::find($request->input('payment_method_id'));
            $order->payment_method_id = $paymentMethod->payment_method_id;
            // add payment method name
            $order->payment_method_name = $paymentMethod->payment_method_name;
        }
        if ($request->shipping_method_id) {
            $shippingMethod = ShippingMethod::find($request->input('shipping_method_id'));
            $order->shipping_method_id = $paymentMethod->payment_method_id;
            // add shipping method name
            $order->shipping_method_name = $shippingMethod->shipping_method_name;
        }

        $order->order_uuid = Str::uuid()->toString();
        $order->user_id = session('user_id');
        $order->order_notes = $request->input('order_notes');
        $order->subtotal = $orderSubtotal;
        $order->coupons_discount_value = $dataAfterCoupon['coupons_discount'];
        $order->tax_value = $totalOrderTaxValue;
        $order->shipping_value = $dataAfterCoupon['order_shipping'] ?? null;
        $order->total = ($orderSubtotal + ($dataAfterCoupon['order_shipping'] ?? 0) + ($order->tax_value ?? 0)) - ($dataAfterCoupon['coupons_discount'] ?? 0);

        $order->order_status = 'pending';



        // create instance of the order items
        $orderItems = OrderServices::createOrderItemsInstance($dataAfterCoupon['products'], $translate);
        if ($orderItems instanceof JsonResponse) {
            return $orderItems;
        }

        // initialize order temp data
        $orderData = [
            'order' => $order,
            'shipping_address' => $shippingAddress,
            'billing_address' => $billingAddress,
            'order_items' => $orderItems,
            'order_coupons' =>  $dataAfterCoupon['order_coupons'],
            "stripe_tax_calculation_id" =>  session("stripe_tax_calculation_id")
        ];

        return $orderData;
    }

    //--------------------------- create the address----------------------------------------
    public static function createOrderAddress(array $address, $addressType)
    {
        $newAddress = new Address();
        $newAddress->address_type = $addressType;
        if (array_key_exists('city_id', $address)) {
            $newAddress->city_id = $address['city_id'];
        }
        $newAddress->state_id = $address['state_id'];
        $newAddress->first_name = $address['first_name'];
        $newAddress->last_name = $address['last_name'];
        $newAddress->contact_email = $address['contact_email'];
        $newAddress->contact_phone = $address['contact_phone'];
        $newAddress->line_one = $address['line_one'];
        $newAddress->line_two = $address['line_two'];
        $newAddress->line_three = $address['line_three'];
        $newAddress->details = $address['details'];
        $newAddress->postal_code = $address['postal_code'];

        return $newAddress;
    }

    //--------------------------- calculate Order Total ----------------------------------------
    public static function calculateOrderShipping($shipping_method_id)
    {
        $shippingMethod = ShippingMethod::find($shipping_method_id);
        $fees = $shippingMethod->fees;
        return $fees;
    }

    //--------------------------- calculate Order Total ----------------------------------------
    public static function calculateOrderTotal($products, $cartProducts)
    {
        $subtotal = 0;
        $discount = 0;
        $total = 0;
        //get the current order total
        foreach ($products as &$product) {
            $cartProduct = collect($cartProducts)->where('product_id', $product['product_id'])->first();
            $product['cart_quantity'] = $cartProduct->cart_product_quantity;
            // calculate subtotal
            $subtotal += $product['price'] * $cartProduct->cart_product_quantity;
            // calculate discount value
            $discount += $product['discount_value'] * $cartProduct->cart_product_quantity;
            // calculate total
            $total += $product['price_after_discount'] * $cartProduct->cart_product_quantity;
        }

        $cartSummary = [
            "subtotal" => $subtotal,
            "discount" => $discount,
            "total" => $total,
        ];

        return $cartSummary;
    }

    //------------------------------ create instances of order items -------------------------------------
    public static function createOrderItemsInstance($products, $translate)
    {
        $locale = app()->getLocale();
        $orderItems = [];
        foreach ($products as $product) {
            // check if valid quantity
            $availableProductStock = $product['local_stock'] + $product['online_stock'];
            if ($availableProductStock <= 0) {
                return ResponseHandler::errorResponse(__('messages.out-of-stock', ['product_name' => $product['product_name']]), 400);
            }

            if ($product['cart_quantity'] > $availableProductStock) {
                return ResponseHandler::errorResponse(__('messages.product-invalid-quantity', ['product_name' => $product['product_name'], 'max' => $availableProductStock, 'in_cart' => $product['cart_quantity']]), 400);
            }

            // get the quantity
            $localQuantity = 0;
            $onlineQuantity = 0;
            if ($product['cart_quantity'] <= $product['local_stock']) {
                $localQuantity = $product['cart_quantity'];
                $onlineQuantity = 0;
            } else {
                $localQuantity = $product['local_stock'];
                $onlineQuantity = $product['cart_quantity'] - $localQuantity;
            }

            // create the instance of the order items
            $orderItem = new OrderItem();
            $orderItem->product_id = $product['product_id'];
            $orderItem->product_variant_id = $product['product_variant_id'];
            $orderItem->price = $product['price'];
            $orderItem->price_after_discount = $product['price_after_discount'];
            $orderItem->product_name = $product['product_name'];
            $orderItem->discount_value = $product['price'] - $product['price_after_discount'];
            $orderItem->tax_value = $product['tax_value'];
            $orderItem->local_quantity = $localQuantity;
            $orderItem->online_quantity = $onlineQuantity;
            $orderItem->subtotal = round(($product['price_after_discount']) * $product['cart_quantity'], 2);

            // check if the product has variant
            if ($product['product_variant_id']) {
                $orderItem->attributes = ProductsService::getVariantAttributes($product['product_variant_id']);
            } else {
                $orderItem->attributes = null;
            }

            // translate product name
            if ($translate == true) {
                $orderItem->attributes = $orderItem->attributes ? $orderItem->attributes[$locale] : null;
            } else {
                $orderItem->product_name = json_encode($orderItem->product_name);
                $orderItem->attributes = json_encode($orderItem->attributes);
            }

            // add the order item to the array
            $orderItems[] = $orderItem;
        }
        return $orderItems;
    }

    // --------------------------------------------------------- Get Temp Order Data ----------------------------------------------------------
    public static function getTempOrderData($tempId)
    {
        // get order data
        $tempData = TempData::find($tempId);
        if (!$tempData) {
            return response()->json(['error' => __('messages.error')], 400);
        }

        // decode body data
        $tempData->data = json_decode($tempData->data, true);

        return $tempData;
    }

    // ------------------ Delete cart and reduce products quantity ------------------
    public static function deleteCartAndProductQuantity($cartId, $orderItems)
    {
        // delete cart
        $cart = Cart::find($cartId);
        if ($cart) {
            $cart->delete();
        }

        // reduce products quantity
        foreach ($orderItems as $order_item) {
            if ($order_item['product_variant_id']) {
                $product = ProductVariant::find($order_item['product_variant_id']);
            } else {
                $product = Product::find($order_item['product_id']);
            }

            $product->disableLogging(); // Disable logging before making changes

            $product->local_stock -= $order_item['local_quantity'];
            $product->online_stock -= $order_item['online_quantity'];
            $product->save();

            $product->enableLogging(); // Re-enable logging after changes are made
        }
    }
}
