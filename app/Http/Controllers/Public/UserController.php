<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseHandler;
use App\Models\Address;
use App\Models\Timezone;
use App\Models\User;
use App\Notifications\NotificationSender;
use App\Services\ChangedAttributes;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    // ------------------------------------------------------------ Get User Info ------------------------------------------------------------
    public function getUserInfo()
    {
        $user = self::getUserData();
        return ResponseHandler::successResponse(null, ['user' => $user], 200);
    }

    // ------------------------------------------------------------ update user info ------------------------------------------------------------
    public function updateInfo(Request $request)
    {
        // Make validation rules
        $validator = Validator::make($request->all(), [
            'first_name' => 'string',
            'last_name' => 'string',
            'email' => 'email',
            'timezone_id' => 'numeric',
            'phone' => ['string'],
        ]);

        // Validate received data
        if ($validator->fails()) {
            return ResponseHandler::errorResponse($validator->errors()->first(), 400);
        }

        // get the user
        $user = User::find(session('user_id'));

        // Check if the email is being changed and if it exists for any other users
        $newEmail = $request->input('email', $user->email);
        if ($newEmail !== $user->email && User::where('email', $newEmail)->exists()) {
            return ResponseHandler::errorResponse(__('messages.email-exist'), 400);
        }

        $user->first_name = $request->input('first_name', $user->first_name);
        $user->last_name = $request->input('last_name', $user->last_name);
        $user->email = $newEmail;
        $user->phone = $request->input('phone', $user->phone);
        $user->timezone_id = $request->input('timezone_id', $user->timezone_id);

        // get changed values
        $changedValues = ChangedAttributes::getChangedAttributes($user);

        // check if email changed
        if (isset($changedValues['email'])) {
            $user->email_verified_at = null;
        }

        // check if there's changes or no
        if (count($changedValues) == 0) {
            return ResponseHandler::successResponse(__('messages.nothing-updated'), ['change' => false], 200);
        }

        // Save the User record
        $user->save();

        $user = self::getUserData();

        return ResponseHandler::successResponse(__('messages.updated'), ['user' => $user, 'change' => true], 200);
    }

    //-------------------------------------------- Get User Addresses -----------------------------------------------------
    // public function getAddresses()
    // {
    //     // get address
    //     $addresses = Address::join('regions', 'addresses.region_id', '=', 'regions.region_id')
    //         ->select(
    //             'address_id',
    //             'city',
    //             'details',
    //             'addresses.region_id',
    //             'region_name_' . app()->getLocale() . ' as region_name',
    //         )
    //         ->where('user_id', session('user_id'))->get();

    //     return response()->json(['addresses' => $addresses], 200);
    // }

    //---------------------------------------------- Get Address ----------------------------------------------------
    // public function getAddress(Request $request, $addressId)
    // {
    //     // Make validation rules
    //     $validator = Validator::make(['address_id' => $addressId], [
    //         'address_id' => 'required|numeric',
    //     ]);

    //     // Validate received data
    //     if ($validator->fails()) {
    //         return ResponseHandler::errorResponse($validator->errors()->first(), 400);
    //     }

    //     // get address
    //     $address = Address::join('regions', 'addresses.region_id', '=', 'regions.region_id')
    //         ->select(
    //             'address_id',
    //             'city',
    //             'details',
    //             'addresses.region_id',
    //             'region_name_' . app()->getLocale() . ' as region_name',
    //         )
    //         ->where('user_id', session('user_id'))
    //         ->find($addressId);

    //     if (!$address) {
    //         return ResponseHandler::errorResponse(__('messages.not-found'), 404);
    //     }

    //     return response()->json(['address' => $address], 200);
    // }

    //---------------------------------------------- Update Address ----------------------------------------------------
    // public function updateAddress(Request $request, $addressId)
    // {
    //     // Make validation rules
    //     $validator = Validator::make(array_merge($request->all(), ['address_id' => $addressId]), [
    //         'address_id' => 'required|numeric',
    //         'region_id' => 'required|numeric|exists:regions,region_id',
    //         'city' => 'required|string',
    //         'details' => 'required|string',
    //     ]);

    //     // Validate received data
    //     if ($validator->fails()) {
    //         return ResponseHandler::errorResponse($validator->errors()->first(), 400);
    //     }

    //     // get address
    //     $address = Address::where('user_id', session('user_id'))->find($addressId);

    //     if (!$address) {
    //         return ResponseHandler::errorResponse(__('messages.not-found'), 404);
    //     }

    //     // update address record
    //     $address->region_id = $request->input('region_id');
    //     $address->city = $request->input('city');
    //     $address->details = $request->input('details');

    //     // get changed values
    //     $changedValues = ChangedAttributes::getChangedAttributes($address);

    //     // check if there's changes or no
    //     if (count($changedValues) == 0) {
    //         return ResponseHandler::successResponse(__('messages.nothing-updated'), ['change' => false], 200);
    //     }

    //     // Save the record
    //     $address->save();

    //     return ResponseHandler::successResponse(__('messages.updated'), ['change' => true], 200);
    // }

    //-------------------------------------------- Delete Address -------------------------------------------------------
    // public function deleteAddress(Request $request, $addressId)
    // {
    //     // make validation rules
    //     $validator = Validator::make(['address_id' => $addressId], [
    //         'address_id' => 'required|numeric',
    //     ]);

    //     // validate received data
    //     if ($validator->fails()) {
    //         return ResponseHandler::errorResponse($validator->errors()->first(), 400);
    //     }

    //     // get the address
    //     $address = Address::find($addressId);
    //     if (!$address) {
    //         return ResponseHandler::errorResponse(__('messages.not-found'), 404);
    //     }

    //     $address->delete();

    //     return ResponseHandler::successResponse(__('messages.deleted'), 200);
    // }

    //-------------------------------------------- Change user Password -----------------------------------------------------
    public function changePassword(Request $request)
    {
        // get the user
        $user = User::find(session('user_id'));

        // make validation rules
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:8|max:50|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_])[A-Za-z\d\W_]{8,50}$/',
        ]);

        // validate received data
        if ($validator->fails()) {
            return ResponseHandler::errorResponse($validator->errors()->first(), 400);
        }

        $old_password = $request->input('old_password');
        $new_password = $request->input('new_password');

        // check password
        if (!Hash::check($old_password, $user->password)) {
            return ResponseHandler::errorResponse(__('messages.password-wrong'), 422);
        }

        // check if new password is as same as old one
        if ($new_password == $old_password) {
            return ResponseHandler::errorResponse(__('messages.password-same'), 400);
        }

        // Update the User record with the new password
        $user->password = bcrypt($new_password);
        $user->save();

        // sent notification to inform the user that the password changed
        // try {
        //     $notificationData = NotificationService::getNotificationData('inform-password-reset', ['user_name' => $user->first_name]);
        //     $user->notify(new NotificationSender($notificationData));
        //     NotificationService::sendFirebaseNotification($notificationData["databaseData"], "user-$user->user_id");
        // } catch (\Throwable $th) {
        // }

        return ResponseHandler::successResponse(__('messages.updated-password'), 200);
    }

    // ------------------------------------------------------------ Get Timezones ------------------------------------------------------------
    // public function getTimezones()
    // {
    //     return ResponseHandler::successResponse(null, ["timezones" => Timezone::get()]);
    // }

    // ------------------------------------------------------------ Helper Fun ------------------------------------------------------------
    public function getUserData()
    {
        // get current user
        $user = User::selectRaw('user_id, first_name, last_name,CONCAT(first_name, " ", last_name) AS user_name, email, phone, locale, timezone_id')
            ->find(session('user_id'));

        return $user;
    }
}
