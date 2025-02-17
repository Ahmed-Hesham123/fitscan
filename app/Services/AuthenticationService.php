<?php

namespace App\Services;

use App\Http\Traits\ResponseHandler;
use App\Models\EmailVerificationCode;
use App\Models\PasswordResetToken;
use App\Models\TwoFaCode;
use Exception;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthenticationService
{

    //------------------------------------------------------ Send 2FA Code ------------------------------------------------------
    public static function SendTwoFactorAuthEmail($authenticatableType, $authenticatableID, $email, $userName)
    {
        $exp_time = 5;

        // Check if the user already has a code
        $_2fa_code = TwoFaCode::where('authenticatable_type', $authenticatableType)
            ->where('authenticatable_id', $authenticatableID)
            ->first();

        if ($_2fa_code) {
            // Check if the existing code is expired by at least 1 minute
            if ($_2fa_code->expires_on->isBefore(now()->addMinutes($exp_time - 1))) {
                // Delete the existing code
                $_2fa_code->delete();
            } else {
                return ResponseHandler::successResponse('2fa_redirect', null, 200);
            }
        }
        // Generate a random reset code
        $code = mt_rand(111111, 999999);
        // Save the code to the database
        $expires_on = now()->addMinutes($exp_time); // Set the expiration time to 10 minutes from now
        $_2fa_code = new TwoFaCode([
            'authenticatable_id' => $authenticatableID,
            'authenticatable_type' => $authenticatableType,
            'code' => $code,
            'expires_on' => $expires_on,
        ]);
        $_2fa_code->save();

        // Send the 2fa code to the user's email

        $subject = app()->isLocale('ar') ? 'رمز المصادقة الثنائية' : "Two-Factor Authentication Code";
        $data = [
            'code' => $code,
            'name' => $userName,
            'expiration' => $exp_time,
        ];

        try {
            Mail::send('emails.global.send_2fa_code', ['data' => $data], function ($message) use ($email, $subject) {
                $message->to($email)->subject($subject);
            });
        } catch (Exception $e) {
            // Delete the saved code from database
            $_2fa_code->delete();
            return ResponseHandler::errorResponse(__('messages.error'), 500);
        }

        return ResponseHandler::successResponse('2fa_redirect', null, 200);
    }

    //------------------------------------------------------ Send Reset Password Code Email ------------------------------------------------------
    public static function SendResetPasswordCodeEmail($resetterType, $resetterID, $email, $userName)
    {
        $exp_time = 10;

        // Check if the user already has a reset token
        $reset_token = PasswordResetToken::where('resettable_type', $resetterType)
            ->where('resettable_id', $resetterID)
            ->first();

        if ($reset_token) {
            // Check if the existing token is expired by at least 1 minute
            if ($reset_token->expires_on->isBefore(now()->addMinutes($exp_time - 1))) {
                // Delete the existing token
                $reset_token->delete();
            } else {
                return ResponseHandler::errorResponse(__('messages.token-already-sent'), 400);
            }
        }

        // Generate a random reset token
        $token = Str::random(60);

        // Save the reset token to the database
        $expires_on = now()->addMinutes($exp_time); // Set the expiration time to 10 minutes from now
        $reset_token = new PasswordResetToken([
            'resettable_id' => $resetterID,
            'resettable_type' => $resetterType,
            'token' => $token,
            'expires_on' => $expires_on,
        ]);
        $reset_token->save();

        // Send the reset token to the user's email
        $route = null;
        switch ($resetterType) {
            case 'users':
                $route = env('USER_RESET_PASSWORD_ROUTE');
                break;
            case 'staff_users':
                $route = env('staff_user_RESET_PASSWORD_ROUTE');
                break;
            default:
                break;
        }

        $url = env('RESET_PASSWORD_URL') . $route . $token . '?lang=' . app()->getLocale();
        $subject = app()->isLocale('ar') ? 'اعادة تعيين كلمة المرور' : 'Reset password';
        $data = [
            'url' => $url,
            'name' => $userName,
            'expiration' => $exp_time,
        ];

        try {
            Mail::send('emails.global.send_password_reset_token', ['data' => $data], function ($message) use ($email, $subject) {
                $message->to($email)->subject($subject);
            });
        } catch (Exception $e) {
            // Delete the saved token from database
            $reset_token->delete();
            return ResponseHandler::errorResponse(__('messages.error'), 500);
        }

        return ResponseHandler::successResponse(__('messages.password-reset-email-sent'), null, 200);
    }

    //------------------------------------------------------ Send Email Verification Code ------------------------------------------------------
    public static function SendEmailVerificationCode($userID, $email, $userName)
    {
        $exp_time = 10;

        // Check if the user already has a code
        $verification_code = EmailVerificationCode::where('user_id', $userID)
            ->first();

        if ($verification_code) {
            // Check if the existing code is expired by at least 1 minute
            if ($verification_code->expires_on->isBefore(now()->addMinutes($exp_time - 1))) {
                // Delete the existing code
                $verification_code->delete();
            } else {
                return ResponseHandler::errorResponse(__('messages.code-already-sent'), 400);
            }
        }
        // Generate a random reset code
        $code = mt_rand(111111, 999999);

        // Save the code to the database
        $expires_on = now()->addMinutes($exp_time); // Set the expiration time to 10 minutes from now
        $verification_code = new EmailVerificationCode([
            'user_id' => $userID,
            'code' => $code,
            'expires_on' => $expires_on,
        ]);
        $verification_code->save();

        // Send the verification code to the user's email
        $subject = app()->isLocale('ar') ? 'رمز التحقق من البريد الإلكتروني' : "Email Verification Code";
        $data = [
            'code' => $code,
            'name' => $userName,
            'expiration' => $exp_time,
        ];

        try {
            Mail::send('emails.global.send_verification_email_code', ['data' => $data], function ($message) use ($email, $subject) {
                $message->to($email)->subject($subject);
            });
        } catch (Exception $e) {
            // Delete the saved code from database
            $verification_code->delete();
            return ResponseHandler::errorResponse(__('messages.error'), 500);
        }

        return ResponseHandler::successResponse(__('messages.verify-email-code-sent'), null, 200);
    }
}
