<?php

use Illuminate\Support\Facades\Route;

///////////////
// 需要登录验证//
///////////////
Route::middleware(['auth:sanctum'])->group(
    function () {
        Route::post('set-tuan-withdraw-order', 'Withdraw\WithdrawController@setTuanOrder');
        Route::post('alipay-withdraw', 'Withdraw\AlipayWithdrawController@payUser');
    }
);
