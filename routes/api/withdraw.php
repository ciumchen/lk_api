<?php

use Illuminate\Support\Facades\Route;

///////////////
// 需要登录验证//
///////////////
Route::middleware(['auth:sanctum'])->group(
    function () {
        Route::post('set-withdraw-order', 'Withdraw\WithdrawController@payUser');
        Route::post('alipay-withdraw', 'Withdraw\Withdraw@payUser');
    }
);
