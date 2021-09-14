<?php

use RainLab\User\Models\User as UserModel;
use Vdomah\JWTAuth\Models\Settings;

Route::group(['prefix' => 'api'], function() {

    Route::post('login', function (Request $request) {
        if (Settings::get('is_login_disabled'))
            App::abort(404, 'Page not found');

        $login_fields = Settings::get('login_fields', ['email', 'password']);

        $credentials = Input::only($login_fields);

        try {
            // verify the credentials and create a token for the user
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(["status" => "error", "data" => [],  "msg" => "invalid_credentials"], 401);
            }
        } catch (JWTException $e) {
            // something went wrong
            return response()->json(["status" => "error", "data" => [],  "msg" => "could_not_create_token"], 500);
        }

        $userModel = JWTAuth::authenticate($token);

        if ($userModel->methodExists('getAuthApiSigninAttributes')) {
            $user = $userModel->getAuthApiSigninAttributes();
        } else {
            $user = [
                'id' => $userModel->id,
                'name' => $userModel->name,
                'phone' => $userModel->phone,
                'email' => $userModel->email,
                'login' => $userModel->login,
                'nid' => $userModel->nid,
                'address' => $userModel->address,
                'referrer' => $userModel->referrer->name,
                'is_activated' => $userModel->is_activated,
            ];
        }

        // if no errors are encountered we can return a JWT
        return response()->json(["status" => "ok", "data" => $user, "token" => $token, "msg" => "Successfully Found Data"]);
    });

    Route::post('refresh', function (Request $request) {
        if (Settings::get('is_refresh_disabled'))
            App::abort(404, 'Page not found');

        $token = Request::get('token');

        try {
            // attempt to refresh the JWT
            if (!$token = JWTAuth::refresh($token)) {
                return response()->json(["status" => "error", "data" => [],  "msg" => "could_not_refresh_token"], 401);
            }
        } catch (Exception $e) {
            // something went wrong
            return response()->json(["status" => "error", "data" => [],  "msg" => "could_not_refresh_token"], 500);
        }

        // if no errors are encountered we can return a new JWT
        return response()->json(["status" => "ok", "token" => $token, "msg" => "Successfully Found Data"]);
    });

    Route::post('invalidate', function (Request $request) {
        if (Settings::get('is_invalidate_disabled'))
            App::abort(404, 'Page not found');

        $token = Request::get('token');

        try {
            // invalidate the token
            JWTAuth::invalidate($token);
        } catch (Exception $e) {
            // something went wrong
            return response()->json(["status" => "error", "data" => [],  "msg" => "could_not_invalidate_token"], 500);
        }

        // if no errors we can return a message to indicate that the token was invalidated
        return response()->json(["status" => "ok", "data" => [], "msg" => "Successfully Invalidated"]);
    });

    Route::post('signup', function (Request $request) {
        if (Settings::get('is_signup_disabled'))
            App::abort(404, 'Page not found');

        $login_fields = Settings::get('signup_fields', ['email', 'password', 'password_confirmation']);
        $credentials = Input::only($login_fields);

        // dd($credentials);

        try {
            // $userModel = \Backend\Models\User::create($credentials);
            $credentials['is_activated'] = 1;
            $userModel = BackendAuth::register($credentials);

            if ($userModel->methodExists('getAuthApiSignupAttributes')) {
                $user = $userModel->getAuthApiSignupAttributes();
            } else {
                $user = [
                    'id' => $userModel->id,
                    'name' => $userModel->name,
                    'phone' => $userModel->phone,
                    'email' => $userModel->email,
                    'login' => $userModel->login,
                    'nid' => $userModel->nid,
                    'address' => $userModel->address,
                    'referrer' => $userModel->referrer->name,
                    'is_activated' => $userModel->is_activated,
                ];
            }
        } catch (Exception $e) {
            return Response::json(["status" => "error", "msg" => $e->getMessage()], 401);
        }

        $token = JWTAuth::fromUser($userModel);

        return Response::json(["status" => "ok", "data" => $user, "token" => $token, "msg" => "Successfully Found Data"]);
    });
});