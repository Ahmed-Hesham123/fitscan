<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;

class NotificationService
{
    // ------------------------------------------------------- Generate Notification Data -------------------------------------------------------
    public static function getNotificationData($type, $data)
    {
        switch ($type) {
                // ------------------------------ signup welcome notification ------------------------------
            case 'welcome-user':
                return [
                    "mailData" => [
                        "subject" => app()->isLocale('ar') ? 'مرحبًا بك في موديفيرن!' : 'Welcome to Modifurn!',
                        "view" => "users.welcome_user",
                        "data" => [
                            "name" => $data['user_name'],
                        ],
                    ],

                    "databaseData" => [
                        "title" => json_encode([
                            "ar" => "مرحبًا بك في موديفيرن!",
                            "en" => "Welcome to Modifurn!",
                        ], JSON_UNESCAPED_UNICODE),
                        "body" => json_encode([
                            "ar" => "شكرا لانضمامك إلينا. ابدأ في الاستكشاف والعثور على الاثاث المناسب لاحتياجاتك.",
                            "en" => "Thank you for joining us. Start exploring and find the right furniture for your needs.",
                        ], JSON_UNESCAPED_UNICODE),
                        "context" => "welcome",
                        "img_url" => null,
                        "route_name" => null,
                        "route_params" => [],
                    ],
                ];

                // ------------------------------ inform the user that password was reset ------------------------------
            case 'inform-password-reset':
                return [
                    "mailData" => [
                        "subject" => app()->isLocale('ar') ? 'تم إعادة تعيين كلمة المرور' : 'Password Changed',
                        "view" => "global.inform_password_reset",
                        "data" => [
                            "name" => $data['user_name'],
                            "time" => Carbon::now()->format('Y-m-d h:i:s A T') . ' (' . date_default_timezone_get() . ')',
                            "location" => ClientInfo::getLocation(),
                            "device" => ClientInfo::getAgent(),
                        ],
                    ],
                    "databaseData" => [
                        "title" => json_encode([
                            "ar" => "تم إعادة تعيين كلمة المرور'",
                            "en" => "Password Changed Successfully",
                        ], JSON_UNESCAPED_UNICODE),
                        "body" => json_encode([
                            "en" => "Your password was recently changed. If you did not change your password, please review your account.",
                            "ar" => "تم تغيير كلمة المرور الخاصة بك مؤخرًا. إذا لم تقم بتغيير كلمة المرور الخاصة بك، يرجى مراجعة حسابك.",
                        ], JSON_UNESCAPED_UNICODE),
                        "context" => "warning",
                        "img_url" => null,
                        "route_name" => null,
                        "route_params" => [],
                    ],
                ];

                // ------------------------------ inform the user that order created successfully ------------------------------
            case 'user-order-created':
                return [
                    "mailData" => [
                        "subject" => app()->isLocale('ar') ? 'تم إنشاء الطلب بنجاح' : 'Order Created Successfully',
                        "view" => "users.user_order_created",
                        "data" => $data,
                    ],
                    "databaseData" => [
                        "title" => json_encode([
                            "ar" => "تم إنشاء الطلب بنجاح",
                            "en" => "Order Created Successfully",
                        ], JSON_UNESCAPED_UNICODE),
                        "body" => json_encode([
                            "ar" => "لقد تم إنشاء طلبك بنجاح (Order ID: #{$data->order_id}).",
                            "en" => "Your order has been created successfully (Order ID: #{$data->order_id}).",
                        ], JSON_UNESCAPED_UNICODE),
                        "context" => "success",
                        "img_url" => null,
                        "route_name" => null,
                        "route_params" => [],
                    ],
                ];

                // ------------------------------ inform the staff user that a new order was palaced ------------------------------
            case 'staff-user-new-order':
                return [
                    "mailData" => [
                        "subject" => app()->isLocale('ar') ? "طلب جديد" : 'New Order',
                        "view" => "staff_users.staff_user_new_order",
                        "data" => $data,
                    ],
                    "databaseData" => [
                        "title" => json_encode([
                            "ar" => "طلب جديد",
                            "en" => "New Order",
                        ], JSON_UNESCAPED_UNICODE),
                        "body" => json_encode([
                            "ar" => "لقد طلب أحد المستخدمين طلبًا مؤخرًا (Order ID: #{$data->order_id}).",
                            "en" => "A user has recently placed an order (Order ID: #{$data->order_id}).",
                        ], JSON_UNESCAPED_UNICODE),
                        "context" => "order",
                        "img_url" => null,
                        "route_name" => "order",
                        "route_params" => ["order_id" => $data->order_id],
                    ],
                ];

                // ------------------------------ inform the user that new services added successfully ------------------------------
            case 'user-order-canceled':
                return [
                    "mailData" => [
                        "subject" => app()->isLocale('ar') ? 'تم الغاء الطلب' : 'Order Canceled',
                        "view" => "users.user_order_canceled",
                        "data" => $data,
                    ],
                    "databaseData" => [
                        "title" => json_encode([
                            "ar" => 'تم الغاء الطلب',
                            "en" => 'Order Canceled',
                        ], JSON_UNESCAPED_UNICODE),
                        "body" => json_encode([
                            "ar" => "لقد قمت بإلغاء طلبك (رقم الطلب: {$data['order_id']}).",
                            "en" => "You've canceled your order (Order ID: {$data['order_id']}).",
                        ], JSON_UNESCAPED_UNICODE),
                        "context" => "inform_normal",
                        "img_url" => null,
                        "route_name" => null,
                        "route_params" => [],
                    ],
                ];

                // ------------------------------ inform the staff user that user has canceled order ------------------------------
            case 'staff-user-order-canceled':
                return [
                    "mailData" => [
                        "subject" => app()->isLocale('ar') ? 'تم الغاء طلب' : 'Order Canceled',
                        "view" => "staff_users.staff_user_order_canceled",
                        "data" => $data,
                    ],
                    "databaseData" => [
                        "title" => json_encode([
                            "ar" => 'تم الغاء طلب',
                            "en" => 'Order Canceled',
                        ], JSON_UNESCAPED_UNICODE),
                        "body" => json_encode([
                            "ar" => "قام مستخدم بإلغاء طلبه (رقم الطلب: {$data['order_id']}).",
                            "en" => "A user has canceled his order (Order ID: {$data['order_id']}).",
                        ], JSON_UNESCAPED_UNICODE),
                        "context" => "inform_urgent",
                        "img_url" => null,
                        "route_name" => "order",
                        "route_params" => ["order_id" => $data['order_id']],
                    ],
                ];

                // ------------------------------ inform the user that order cancel was updated ------------------------------
            case 'user-cancel-update':
                return [
                    "mailData" => [
                        "subject" => app()->isLocale('ar') ? 'تحديث في طلب الالغاء' : 'Cancel Request Update',
                        "view" => "users.user_cancel_update",
                        "data" => $data,
                    ],
                    "databaseData" => [
                        "title" => json_encode([
                            "ar" => 'تحديث في طلب الالغاء',
                            "en" => 'Cancel Request Update',
                        ], JSON_UNESCAPED_UNICODE),
                        "body" => json_encode([
                            "ar" => "هناك تحديث بشأن طلب الالغاء الخاص بك (رقم الطلب: {$data['order_id']}).",
                            "en" => "There's an update on your cancel request (Order ID: {$data['order_id']}).",
                        ], JSON_UNESCAPED_UNICODE),
                        "context" => "inform_normal",
                        "img_url" => null,
                        "route_name" => null,
                        "route_params" => [],
                    ],
                ];

                // ------------------------------ inform the user that order was refunded ------------------------------
            case 'user-order-refunded':
                return [
                    "mailData" => [
                        "subject" => app()->isLocale('ar') ? 'تم رد الطلب' : 'Order Refunded',
                        "view" => "users.user_order_refunded",
                        "data" => $data,
                    ],
                    "databaseData" => [
                        "title" => json_encode([
                            "ar" => 'تم رد الطلب',
                            "en" => 'Order Refunded',
                        ], JSON_UNESCAPED_UNICODE),
                        "body" => json_encode([
                            "ar" => "لقد تم إصدار طلب استرداد الأموال الخاص بك (رقم الطلب: {$data['order_id']}).",
                            "en" => "Your refund request has been issued (Order ID: {$data['order_id']}).",
                        ], JSON_UNESCAPED_UNICODE),
                        "context" => "inform_normal",
                        "img_url" => null,
                        "route_name" => null,
                        "route_params" => [],
                    ],
                ];

                // ------------------------------ inform the staff user that user has refunded order------------------------------
            case 'staff-user-order-refunded':
                return [
                    "mailData" => [
                        "subject" => app()->isLocale('ar') ? 'تم رد الطلب' : 'Order Refunded',
                        "view" => "staff_users.staff_user_order_refunded",
                        "data" => $data,
                    ],
                    "databaseData" => [
                        "title" => json_encode([
                            "ar" => 'تم رد الطلب',
                            "en" => 'Order Refunded',
                        ], JSON_UNESCAPED_UNICODE),
                        "body" => json_encode([
                            "ar" => "تم رد طلب مستخدم (رقم الطلب: {$data['order_id']}).",
                            "en" => "A user's order has been refunded (Order ID: {$data['order_id']}).",
                        ], JSON_UNESCAPED_UNICODE),
                        "context" => "inform_urgent",
                        "img_url" => null,
                        "route_name" => "order",
                        "route_params" => ["order_id" => $data['order_id']],
                    ],
                ];



                // ------------------------------ inform the user that his question was answered ------------------------------
            case 'user-question-update':
                return [
                    "mailData" => [
                        "subject" => app()->isLocale('ar') ? 'تمت الإجابة على سؤالك' : 'Your Question Has Been Answered',
                        "view" => "users.user_question_answered",
                        "data" => $data,
                    ],
                    "databaseData" => [
                        "title" => json_encode([
                            "ar" => "تحديث في سؤالك",
                            "en" => "Question Updated",
                        ], JSON_UNESCAPED_UNICODE),
                        "body" => json_encode([
                            "ar" => "تمت الإجابة على سؤالك حول المنتج {$data['product_name']}.",
                            "en" => "Your question about the product {$data['product_name']} has been answered.",
                        ], JSON_UNESCAPED_UNICODE),
                        "context" => "info",
                        "img_url" => null,
                        "route_name" => null,
                        "route_params" => [],
                    ],
                ];


                // ------------------------------ inform the staff user that user has asked a question ------------------------------
            case 'staff-user-new-question':
                return [
                    "mailData" => [
                        "subject" => app()->isLocale('ar') ? 'سؤال جديد من المستخدم' : 'New Question from User',
                        "view" => "staff_users.staff_user_new_question",
                        "data" => $data,
                    ],
                    "databaseData" => [
                        "title" => json_encode([
                            "ar" => "سؤال جديد من المستخدم",
                            "en" => "New Question from User",
                        ], JSON_UNESCAPED_UNICODE),
                        "body" => json_encode([
                            "ar" => "تم طرح سؤال جديد من المستخدم حول المنتج {$data['product_name']}.",
                            "en" => "A new question has been asked by the user about the product {$data['product_name']}.",
                        ], JSON_UNESCAPED_UNICODE),
                        "context" => "info",
                        "img_url" => null,
                        "route_name" => null,
                        "route_params" => [],
                    ],
                ];

                // ------------------------------ inform the staff user that user has asked a question ------------------------------
            case 'staff-user-new-review':
                return [
                    "mailData" => [
                        "subject" => 'New Product Review',
                        "view" => "staff_users.staff_user_new_review",
                        "data" => $data,
                    ],
                    "databaseData" => [
                        "title" => json_encode([
                            "ar" => "new Review",
                            "en" => "New Review",
                        ], JSON_UNESCAPED_UNICODE),
                        "body" => json_encode([
                            "ar" => "New review for {$data['product_name']}.",
                            "en" => "New review for {$data['product_name']}.",
                        ], JSON_UNESCAPED_UNICODE),
                        "context" => "info",
                        "img_url" => null,
                        "route_name" => null,
                        "route_params" => [],
                    ],
                ];


                // ------------------------------ inform the user that order refunded was updated ------------------------------
            case 'user-refund-update':
                return [
                    "mailData" => [
                        "subject" => app()->isLocale('ar') ? 'تحديث في طلب الاسترداد' : 'Refund Request Update',
                        "view" => "users.user_refund_update",
                        "data" => $data,
                    ],
                    "databaseData" => [
                        "title" => json_encode([
                            "ar" => 'تحديث في طلب الاسترداد',
                            "en" => 'Refund Request Update',
                        ], JSON_UNESCAPED_UNICODE),
                        "body" => json_encode([
                            "ar" => "هناك تحديث بشأن طلب استرداد الأموال الخاص بك (رقم الطلب: " . $data['order_id'] . ").",
                            "en" => "There's an update on your refund request (Order ID : " . $data['order_id'] . ").",
                        ], JSON_UNESCAPED_UNICODE),
                        "context" => "inform_normal",
                        "img_url" => null,
                        "route_name" => null,
                        "route_params" => [],
                    ],
                ];


            default:
                return null;
        }
    }

    // ------------------------------------------------------- Send Firebase Notification To topic -------------------------------------------------------
    public static function sendFirebaseNotification($data, $to)
    {
        $notification_en = ['title' => $data['title_en'], 'body' => $data['body_en'], 'image' => $data['img_url']];
        $notification_ar = ['title' => $data['title_ar'], 'body' => $data['body_ar'], 'image' => $data['img_url']];

        $topic_en = "/topics/$to-notifications-en";
        $topic_ar = "/topics/$to-notifications-ar";

        // prepare notification api data
        $headers = [
            'Authorization: key=' . env('FCM_SERVER_KEY'),
            'Content-Type: application/json',
        ];

        $payloads = [
            'content_available' => true,
            "mutable_content" => true,
            'priority' => 'high',
            'data' => [
                "image" => $data['img_url'],
            ],
        ];

        // ------ add click actions ------
        // staff-users click action:
        if (Str::contains($to, "staff-user")) {
            $notification_en["click_action"] = env("ADMIN_FRONTEND_URL");
            $notification_ar["click_action"] = env("ADMIN_FRONTEND_URL");
        }

        // send english notification
        $payloads['notification'] = $notification_en;
        $payloads['to'] = $topic_en;
        self::callFcmAPI($headers, $payloads);

        // send arabic notification
        $payloads['notification'] = $notification_ar;
        $payloads['to'] = $topic_ar;
        self::callFcmAPI($headers, $payloads);
    }

    public static function callFcmAPI($headers, $payloads)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, env('FCM_ENDPOINT'));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payloads));
        curl_exec($ch);
        curl_close($ch);
    }
}
