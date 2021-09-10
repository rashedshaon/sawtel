<?php namespace ItRail\AdTacker\Apis;

use Validator, Response, Event;
use Carbon\Carbon;
use ItRail\AdTacker\Apis\ApiController;


use Backend\Classes\AuthManager;
// use Tymon\JWTAuth\JWTAuth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Vdomah\JWTAuth\Http\Requests\LoginRequest;
use Vdomah\JWTAuth\Http\Requests\TokenRequest;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;


class User extends ApiController
{
    
    public function getUser(JWTAuth $auth)
    {
        // $user_id = JWTAuth::parseToken()->authenticate()->id;
        dd(JWTAuth::parseToken()->authenticate());

        if (!$user = $auth->user()) {
            return response()->json(['status' => 'error', 'msg' => 'User not found']);
        }

        
        $data = [
            "id" => $user->id,
            "name" => $user->name,
            "phone" => $user->phone,
            "activated_at" => $user->activated_at,
            "last_login" => $user->last_login ? $user->last_login->format('d-m-Y h:i:s A') : '',
            "created_at" => $user->created_at->format('d-m-Y h:i:s A'),
            "updated_at" => $user->updated_at->format('d-m-Y h:i:s A'),
            "is_superuser" => $user->is_superuser,
            "created_by" => $user->created_user->name ?? '',
            "role_id" => $user->role->id,
            "role_name" => $user->role->name,
            "role_code" => $user->role->code,
            "is_system" => $user->is_system,
            "permissions" => $permissions,
            "is_activated" => $user->is_activated,
        ];

        return response()->json(['status' => 'ok', 'data' => $data, 'msg' => 'Successfully found user data']);
    }
}