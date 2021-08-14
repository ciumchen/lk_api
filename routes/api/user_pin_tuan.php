<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
//购买来拼金
    Route::any('UserBuyLpj', 'User\UserPinTuanController@UserBuyLpj');
});

//支付回调 getLkMemberPayHd
Route::post('getUserBuyLpjHd', 'User\UserPinTuanController@getUserBuyLpjHd');

//查询用户的来拼金
Route::any('getUserDataLpj', 'User\UserPinTuanController@getUserDataLpj');






