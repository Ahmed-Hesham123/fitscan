<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Mail;

class OrderMailsService
{
    //--------------------------- send new order email ------------------------------------------------
    public static function newOrderMail($orderId)
    {
        // sending notifications
        try {
            // get data order with details from other tables
            $orderDetails = Order::with('order_items')
                ->find($orderId);

            // format prices data
            $orderDetails = Numbers::formatDataPrices($orderDetails);

            // send notification to inform the user that the order was created successfully
            $notificationData = NotificationService::getNotificationData('user-new-order', $orderDetails);
            $mailData = $notificationData['mailData'];
            $subject = $mailData['subject'];
            $dataAfterCoupon = $mailData['data'];
            $view = $mailData['view'];

            Mail::send('emails.' . $view, ['data' => $dataAfterCoupon], function ($message) use ($dataAfterCoupon, $subject) {
                $message->to($dataAfterCoupon['customer_email'])->subject($subject);
            });

            // send notification to inform the cp users that there's a new order
            $cp_user_locale = "en";
            // app()->setlocale($cp_user_locale);
            // $cp_users = CpUser::get();
            // $notificationData = NotificationService::getNotificationData('cp-user-new-order', $orderDetails);
            // Notification::send($cp_users, new NotificationSender($notificationData));
        } catch (\Throwable $th) {
        }
    }
}
