<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseHandler;
use App\Models\Address;
use App\Models\PasswordResetToken;
use App\Models\User;
use App\Notifications\NotificationSender;
use App\Services\AuthenticationService;
use App\Services\CartService;
use App\Services\NotificationService;
use App\Services\WishlistService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PublicAuthController extends Controller
{
    // ------------------------------------------------------------ login ------------------------------------------------------------
    public function login(Request $request)
    {
        // make validation rules
        $validator = Validator::make($request->all(), [
            'email' => 'required|regex:/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/',
            'password' => 'required',
        ]);

        // validate received data
        if ($validator->fails()) {
            return ResponseHandler::errorResponse($validator->errors()->first(), 400);
        }

        // get data from request
        $email = $request->get('email');
        $password = $request->get('password');

        // get user
        $user = User::where('email', $email)->first();

        if (!$user) {
            return ResponseHandler::errorResponse(__('messages.email-wrong'), 401);
        }

        // check password
        if (!Hash::check($password, $user->password)) {
            return ResponseHandler::errorResponse(__('messages.password-wrong'), 401);
        }

        // check status
        if ($user->status == false) {
            return ResponseHandler::errorResponse(__('messages.deactivated-account'), 403);
        }

        // if two factor authentication enabled
        if ($user->two_factor_auth == true) {
            $response = AuthenticationService::SendTwoFactorAuthEmail('users', $user->user_id, $user->email, $user->first_name);
            return $response;
        }

        // two factor authentication not enabled

        // add guest cart and wishlist
        CartService::addGuestCartToUserCart($user);
        WishlistService::addGuestWishlistToUserWishlist($user);

        // generate token
        $token = auth()->login($user);
        return response()
            ->json(["token" => $token], 200)
            ->withCookie(cookie('token', $token, env('JWT_REFRESH_TTL'), '/', null, true, true, false, 'None'));
    }


    // ------------------------------------------------------------ signup ------------------------------------------------------------
    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|unique:users,email|regex:/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/',
            'password' => 'required|string|min:8|max:50|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_])[A-Za-z\d\W_]{8,50}$/',
            'phone' => ['required', 'unique:users,phone', 'string'],
        ]);

        // Validate received data
        if ($validator->fails()) {
            return ResponseHandler::errorResponse($validator->errors()->first(), 400);
        }

        // Create a new User instance
        $user = new User;
        $user->first_name = $request->input('first_name');
        $user->last_name = $request->input('last_name');
        $user->email = $request->input('email');
        $user->password = bcrypt($request->input('password'));
        $user->phone = $request->input('phone');

        // Save the new User record
        $user->save();


        // try {
        //     // send welcome notification
        //     $notificationData = NotificationService::getNotificationData('welcome-user', ['user_name' => $user->first_name]);
        //     $user->notify(new NotificationSender($notificationData));
        // } catch (Exception $e) {
        //     // return $e->getMessage();
        // }



        // generate token
        $token = auth()->login($user);
        return response()
            ->json(["token" => $token], 200)
            ->withCookie(cookie('token', $token, env('JWT_REFRESH_TTL'), '/', null, true, true, false, 'None'));
    }

    // ------------------------------------------------------------ refresh token ------------------------------------------------------------
    public function refreshToken()
    {
        $new_token = null;
        try {
            // refresh token
            $new_token = Auth::refresh();
        } catch (Exception $e) {
            return ResponseHandler::errorResponse(__('messages.invalid-token'), 401);
        }

        return response()
            ->json(["token" => $new_token], 200)
            ->withCookie(cookie('token', $new_token, env('JWT_REFRESH_TTL'), '/', null, true, true, false, 'None'));
    }

    // ------------------------------------------------------------ logout ------------------------------------------------------------
    public function logout()
    {
        try {
            // invalidate current token if it is valid
            Auth::logout();
        } catch (Exception $e) {
        }

        return ResponseHandler::successResponse(__('messages.logout'), null, 200);
    }

    //--------------------------------- send token to email to reset password ---------------------------------------------
    public function SendResetPasswordEmail(Request $request)
    {
        // make validation rules
        $validator = Validator::make($request->all(), [
            'email' => 'required|regex:/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/',
        ]);

        // validate received data
        if ($validator->fails()) {
            return ResponseHandler::errorResponse($validator->errors()->first(), 400);
        }

        // Find the user with the given email
        $email = $request->input('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            return ResponseHandler::errorResponse(__('messages.email-wrong'), 404);
        }

        $response = AuthenticationService::SendResetPasswordCodeEmail('users', $user->user_id, $user->email, $user->first_name);
        return $response;
    }

    //-------------------------------------------- check user reset token -------------------------------------------------------
    public function CheckResetPasswordToken(Request $request)
    {
        // make validation rules
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
        ]);

        // validate received data
        if ($validator->fails()) {
            return ResponseHandler::errorResponse($validator->errors()->first(), 400);
        }

        $token = $request->input('token');
        // check if the token is 60 char manually to return invalid token
        if (strlen($token) != 60) {
            return ResponseHandler::errorResponse(__('messages.link-invalid'), 422);
        }

        // get the token from the database
        $reset_token = PasswordResetToken::where('resettable_type', 'users')->where('token', $token)->first();

        // check if the token exist
        if (!$reset_token) {
            return ResponseHandler::errorResponse(__('messages.link-invalid'), 422);
        }

        // check if the token is expired
        if ($reset_token->expires_on->isBefore(now())) {
            return ResponseHandler::errorResponse(__('messages.link-invalid'), 422);
        }

        return ResponseHandler::successResponse(null, [], 200);
    }

    //-------------------------------------------- Reset user password -------------------------------------------------------
    public function ResetPassword(Request $request)
    {
        // make validation rules
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'new_password' => 'required|string|min:8|max:50|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_])[A-Za-z\d\W_]{8,50}$/',
        ]);

        // validate received data
        if ($validator->fails()) {
            return ResponseHandler::errorResponse($validator->errors()->first(), 400);
        }

        $token = $request->input('token');
        // check if the token is 60 char manually to return invalid token
        if (strlen($token) != 60) {
            return ResponseHandler::errorResponse(__('messages.link-invalid'), 422);
        }

        // get the token from the database
        $reset_token = PasswordResetToken::where('resettable_type', 'users')->where('token', $request->input('token'))->first();

        // check if the token exist
        if (!$reset_token) {
            return ResponseHandler::errorResponse(__('messages.link-invalid'), 422);
        }

        // check if the token is expired
        if ($reset_token->expires_on->isBefore(now())) {
            return ResponseHandler::errorResponse(__('messages.link-invalid'), 422);
        }
        // get the user
        $user = User::find($reset_token->resettable_id);
        if (!$user) {
            return ResponseHandler::successResponse(__('messages.error'), 422);
        }

        // update the User record with the new password
        $user->password = bcrypt($request->input('new_password'));
        $user->save();

        // delete the token
        $reset_token->delete();

        // send notification to inform the user that the password changed
        // try {
        //     $notificationData = NotificationService::getNotificationData('inform-password-reset', ['user_name' => $user->first_name]);
        //     $user->notify(new NotificationSender($notificationData));
        //     NotificationService::sendFirebaseNotification($notificationData["databaseData"], "user-$user->user_id");
        // } catch (\Throwable $th) {
        // }

        return ResponseHandler::successResponse(__('messages.updated'), null, 200);
    }

    // ------------------------------------------------------------ me ------------------------------------------------------------
    public function me()
    {
        // get current user
        $user = User::selectRaw('user_id, CONCAT(first_name, " ", last_name) AS user_name, user_img, locale, user_type, timezone_id')
            ->find(session('user_id'));

        return ResponseHandler::successResponse(null, ['user' => $user], 200);
    }

    // ------------------------------------------------------------ guards ------------------------------------------------------------
    public function isAuthenticated()
    {
        return response(null, 200);
    }
}
