<?php

Route::group(
    [
        'prefix' => 'api/',
        'middleware' => ['api'],
    ],
    function () {

        Route::get('test', function(){
            return "ok";
        });

        Route::post('test', function (\Request $request) {
            return response()->json(('The test was successful'));
         })->middleware('\Tymon\JWTAuth\Middleware\GetUserFromToken');

        // Route::post('user-login', 'ItRail\AdTacker\Apis\User@login');
        // Route::post('refresh-token', 'ItRail\AdTacker\Apis\User@refreshToken');


        Route::get('get-user', 'ItRail\AdTacker\Apis\User@getUser');
        
        Route::middleware(['jwt.auth'])->group(
            function () {

                
                Route::get('get-products', 'ItRail\AdTacker\Apis\Products@getProducts');

            }
        );
    }
);
