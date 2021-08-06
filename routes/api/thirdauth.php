<?php

use Illuminate\Support\Facades\Route;

///////////////
// 需要登录验证//
///////////////
Route::middleware(['auth:sanctum'])->group(
    function () {
        Route::any('alipay-auth', 'ThirdAuth\AlipayAuthController@callback');
    }
);
/////////////////
// 不需要登录验证//
////////////////
Route::any('alipay-notify', 'ThirdAuth\AlipayNotifyController@authNotify');
Route::any('alipay-after-auth', 'ThirdAuth\AlipayAuthController@AlipayAfterAuth');


