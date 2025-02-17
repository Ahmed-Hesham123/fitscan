<?php

namespace App\Services;

use App\Http\Traits\ResponseHandler;
use App\Models\City;
use App\Models\State;
use App\Models\Tax;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\StripeClient;
use Stripe\TaxRate;

class TaxService
{
    //-------------------------------- Calculate Taxes Using Stripe --------------------------------------
    public static function calculateTaxUsingStripe($shippingAddress, &$products)
    {
        // ***
        // ------------ Calculate Taxes using Stripe ------------ 
        // **
        $stripe = new StripeClient(env('STRIPE_API_SECRET'));

        // Create a new line items array for the products
        $lineItems = [];
        foreach ($products as $product) {
            $lineItems[] = [
                'amount' => $product['price_after_discount']  * 100, // Example amount in cents ($10.00)
                'quantity' => $product->cart_product_quantity, // Used in Calculation 
                'reference' => isset($product->product_name["en"]) ? $product->product_name["en"] : $product->product_name, // Optional reference for the item,
            ];
        }

        try {
            // Create a new tax calculation for the shipping address
            $taxCalculation = $stripe->tax->calculations->create([
                'currency' => 'usd',
                'line_items' => $lineItems,
                'customer_details' => [
                    'address' => [
                        'line1' => $shippingAddress['line_one'],
                        'line2' => $shippingAddress['line_two'],
                        'city' => isset($shippingAddress['city_id']) ? City::find($shippingAddress['city_id'])->city_name : $shippingAddress['line_three'],
                        'state' =>  State::find($shippingAddress['state_id'])->state_name,
                        'postal_code' => $shippingAddress['postal_code'],
                        'country' => "US",
                    ],
                    'address_source' => 'shipping',
                ],
                'expand' => ['line_items']
            ]);
        } catch (\Exception $e) {
            Log::error('Stripe API Error: ' . $e->getMessage());
            return ResponseHandler::errorResponse("Invalid Postal Code", 410);
        }

        $taxCalculationId = $taxCalculation->id;
        $lineItemsTaxes = $taxCalculation->line_items->data;

        // save tax id to save it in temp data later
        session(['stripe_tax_calculation_id' => $taxCalculationId]);


        //-------------------------------- Calculate Taxes Using Local Database --------------------------------------

        // get the total tax value & tax value for each product
        $totalOrderTaxValue = 0;
        foreach ($products as $index => $product) {
            // Get the corresponding tax value from Stripe
            $productTax = $lineItemsTaxes[$index]['amount_tax'] / 100; // Convert from cents to dollars

            // Add the tax value to the product array
            $products[$index]['tax_value'] = $productTax;

            // Sum up the total tax value for the order
            $totalOrderTaxValue += $productTax;
        }

        return $totalOrderTaxValue;
    }

    //-------------------------------- Save Tax In Stripe --------------------------------------
    public static function saveTaxInStripe($stripe_tax_calculation_id, $orderUUID)
    {
        // ***
        // ------------ Save Tax In Stripe ------------ 
        // **
        $stripe = new StripeClient(env('STRIPE_API_SECRET'));

        try {
            $transaction = $stripe->tax->transactions->createFromCalculation([
                'calculation' => $stripe_tax_calculation_id,
                'reference' => 'Order ID: ' . $orderUUID,
            ]);
        } catch (\Exception $e) {
            Log::error('Stripe API Error: ' . $e->getMessage());
        }
    }

    //-------------------------------- Calculate Taxes Using Local Database --------------------------------------
    public static function calculateTax($shippingAddress, &$products)
    {
        // get the tax rate
        $taxRate = self::getTaxRateByCityId($shippingAddress['state_id']);
        if ($taxRate instanceof JsonResponse) {
            return $taxRate;
        }

        // get the total tax value & tax value for each product
        $totalOrderTaxValue = 0;
        foreach ($products as $product) {
            $product['tax_value'] = round(($taxRate / 100) * $product['price_after_discount'] * $product['cart_quantity'], 2);
            $totalOrderTaxValue += $product['tax_value'];
        }

        return $totalOrderTaxValue;
    }

    //-------------------------------- get tax rate by city id --------------------------------------
    public static function getTaxRateByCityId($stateId)
    {
        $state = State::find($stateId);
        $tax = Tax::find($state->tax_id);
        if (!$tax) {
            return ResponseHandler::errorResponse(__('messages.error'), 500);
        }
        return $tax->rate;
    }
}
