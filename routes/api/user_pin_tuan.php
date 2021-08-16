<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
//购买来拼金
    Route::any('UserBuyLpj', 'User\UserPinTuanController@UserBuyLpj');

    //使用70%usdt补贴金充值来拼金
    Route::any('UserUsdtDhLpj', 'User\UserPinTuanController@UserUsdtDhLpj');

    //使用购物卡兑换代充话费
    Route::any('ShoppingCardDhDchf', 'User\UserPinTuanController@ShoppingCardDhDchf');


});

//支付回调 getLkMemberPayHd
Route::post('getUserBuyLpjHd', 'User\UserPinTuanController@getUserBuyLpjHd');

//查询用户的来拼金
Route::any('getUserDataLpj', 'User\UserPinTuanController@getUserDataLpj');

//查询用户的来拼金充值记录
Route::any('getUserDataLpjLog', 'User\UserPinTuanDataController@getUserDataLpjLog');

//查询用户的来70%usdt可兑换余额
Route::any('getUserDataUsdtYE', 'User\UserPinTuanController@getUserDataUsdtYE');

//查询用户的购物卡余额
Route::any('getUserShoppingCardMoney', 'User\UserPinTuanDataController@getUserShoppingCardMoney');





