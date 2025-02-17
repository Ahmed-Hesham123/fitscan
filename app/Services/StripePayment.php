<?php

namespace App\Services;

use App\Http\Traits\ResponseHandler;
use App\Models\City;
use App\Models\State;
use App\Models\TempData;
use Illuminate\Http\JsonResponse;
use Stripe\Stripe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Stripe\Checkout\Session;
use Illuminate\Support\Str;
use Stripe\PaymentIntent;

class StripePayment
{

    //--------------------------------------------- create the payment url ----------------------------------
    public static function generateStripePaymentForm($tempData, $type)
    {
        // handle stripe auth
        Stripe::setApiKey(env('STRIPE_API_SECRET'));
        $ID = Str::uuid();
        try {
            // init the payment methods based on the order amount
            if (round($tempData['order']['total'] * 100) >= 50) {
                $paymentMethods = ['card', 'affirm', 'klarna'];
            } else {
                $paymentMethods = ['card'];
            }

            // Set up session data
            $sessionData = [
                'payment_method_types' => $paymentMethods,
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'Order Checkout',
                        ],
                        'unit_amount' => round($tempData['order']['total'] * 100), // Amount in cents
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment', // Use 'payment' mode for payment form
                'ui_mode' => 'embedded',
                'return_url' => env('APP_URL') . "/api/public/payment/" . $type . "/payment-callback?session_id={CHECKOUT_SESSION_ID}",
                'metadata' => [
                    "temp_id" => $ID,
                ],
            ];

            // $paymentIntent = PaymentIntent::create([
            //     'amount' => $tempData['order']['total'] * 100, // Amount in cents
            //     'currency' => 'usd',
            //     'description' => app()->isLocale('ar') ? "دفع الطلب" : "Order Checkout",
            //     'metadata' => [
            //         'temp_id' => $ID,
            //     ],
            //     'automatic_payment_methods' => [
            //         'enabled' => true,
            //     ],
            // ]);

            // Create the Checkout session
            $session = Session::create($sessionData);

            // set up form data
            $formData = [
                'amount' => $tempData['order']['total'] * 100,
                'currency' => 'USD',
                'language' => app()->getLocale(),
                'description' =>  __("views.order-payment.order-checkout"),
                'callback_url' => env('APP_URL') . "/api/public/payment/" . $type . "/payment-callback?session_id=$session->id", // this is the callback url
                "temp_id" => $ID,
                'clientSecret' => $session->client_secret
            ];

            // Save the session ID or URL as needed
            $tempData['stripe_session_id'] = $session->id;

            $tempData['form_data'] = $formData;

            $tempOrder = new TempData();
            $tempOrder->id = $ID;
            $tempOrder->data = json_encode($tempData);
            $tempOrder->payable_type = $type;
            $tempOrder->save();

            // Return the Checkout session URL
            $paymentFormUrl = env('APP_URL') . "/api/public/payment/form/" . $ID;

            return $paymentFormUrl;
        } catch (\Exception $e) {
            // Handle any errors, such as Stripe API exceptions
            return ResponseHandler::errorResponse($e->getMessage(), 500);
        }
    }


    // ------------------------------------------------------- Fetch Stripe Payment -------------------------------------------------------
    public static function getStripePayment(Request $request)
    {
        // Make validation rules
        $validator = Validator::make($request->all(), [
            "session_id" => "required|string",
        ]);

        // Validate received data
        if ($validator->fails()) {
            return response()->json(['error' => __('messages.error')], 400);
        }

        $sessionId = $request->input('session_id');
        // Set Stripe API key
        Stripe::setApiKey(env('STRIPE_API_SECRET'));

        $session = null;
        $paymentIntent = null;
        $paymentMethod = null;
        try {
            // Retrieve the checkout session
            $session = \Stripe\Checkout\Session::retrieve($sessionId);

            // Get the payment intent ID from the session
            $paymentIntentId = $session->payment_intent;
            $paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntentId);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        // Check if this payment doesn't exist
        if (!$paymentIntent) {
            return response()->json(['error' => __('messages.error')], 400);
        }

        // Check payment status
        if ($paymentIntent->status != "succeeded") {
            $tempId = $session->metadata->temp_id;
            $tempOrderData = OrderServices::getTempOrderData($tempId);
            if ($tempOrderData instanceof JsonResponse) {
                return $tempOrderData;
            }

            $lang = $tempOrderData['lang'];
            app()->setlocale($lang);

            // Return order payment fail view
            // $data = [
            //     "url" => env('APP_URL') . "/api/public/payment/form/" . $tempId,
            // ];

            // return view('order-fail')->with('data', $data);
            return view('order-fail');
        }

        return $session;
    }
}
