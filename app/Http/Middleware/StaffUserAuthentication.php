<?php

namespace App\Http\Middleware;

use App\Http\Traits\ResponseHandler;
use Closure;
use Exception;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class StaffUserAuthentication
{
    public function handle($request, Closure $next)
    {

        /*
        the token is sent in the cookies in a variable called 'token'
         */

        try {
            // get the token from the request
            $token = $_COOKIE['staff_token'];

            // make sure that it's valid
            Auth::setToken($token)->authenticate();
        } catch (Exception $e) {
            if ($e instanceof TokenExpiredException) {
                return response()->json(['error' => 'token expired'], 401);
            } else {
                return response()->json(['error' => __('messages.invalid-token')], 401);
            }
        }

        // get current staff_user
        $staff_user = Auth::user();

        if (!$staff_user) {
            return ResponseHandler::errorResponse(__('messages.not-found'), 404);
        }

        // check status
        // if ($staff_user->status == false) {
        //     // return response()->json(['error' => __('messages.deactivated-account')], 403);
        // }

        // save staff_user data to use later
        session(['staff_user_id' => $staff_user->staff_user_id]);

        return $next($request);
    }
}