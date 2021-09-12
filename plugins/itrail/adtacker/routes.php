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

        Route::post('forget-password', 'ItRail\Adtacker\Apis\ApiController@forgetPassword');
        Route::post('get-balance', 'ItRail\Adtacker\Apis\ApiController@getBalance');
        Route::post('home-data', 'ItRail\Adtacker\Apis\ApiController@homeData');
        Route::post('shop-products', 'ItRail\Adtacker\Apis\ApiController@shopProducts');
        Route::post('todays-income', 'ItRail\Adtacker\Apis\ApiController@todaysIncome');
        Route::post('submit-income', 'ItRail\Adtacker\Apis\ApiController@submitIncome');
        Route::post('get-banks', 'ItRail\Adtacker\Apis\ApiController@getBanks');
        Route::post('withdraw-requests', 'ItRail\Adtacker\Apis\ApiController@withdrawRequests');
        Route::post('submit-withdraw-requests', 'ItRail\Adtacker\Apis\ApiController@submitWithdrawRequests');
        Route::post('submit-order', 'ItRail\Adtacker\Apis\ApiController@submitOrder');
        Route::post('get-orders', 'ItRail\Adtacker\Apis\ApiController@getOrders');
        Route::post('transaction-summery', 'ItRail\Adtacker\Apis\ApiController@transactionSummery');
        Route::post('transaction-details', 'ItRail\Adtacker\Apis\ApiController@transactionDetails');
        Route::post('accounts', 'ItRail\Adtacker\Apis\ApiController@accounts');
        Route::post('update-profile', 'ItRail\Adtacker\Apis\ApiController@updateProfile');
        Route::post('update-photo', 'ItRail\Adtacker\Apis\ApiController@updatePhoto');
        Route::post('send-fund', 'ItRail\Adtacker\Apis\ApiController@sendFund');
        Route::post('about-us', 'ItRail\Adtacker\Apis\ApiController@aboutUs');
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
