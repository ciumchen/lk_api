<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
//购买来拼金
    Route::any('UserBuyLpj', 'User\UserPinTuanController@UserBuyLpj');

    //使用70%usdt补贴金充值来拼金
    Route::any('UserUsdtDhLpj', 'User\UserPinTuanController@UserUsdtDhLpj');

    //使用购物卡兑换话费直充和代充
    Route::any('ShoppingCardDhDefault', 'User\UserPinTuanController@ShoppingCardDhDefault');

    //购物卡生成订单后调用支付,购物卡兑换话费支付回调
    Route::any('gwkDhCallback', 'User\UserPinTuanController@gwkDhCallback');

    //使用购物卡兑换美团卡
    Route::any('ShoppingCardDhMt', 'User\UserPinTuanController@ShoppingCardDhMt');

    //生成美团订单后确认兑换,不需要调用支付
    Route::any('gwkDhCallbackNoZf', 'User\UserPinTuanController@gwkDhCallbackNoZf');


    //购物卡赠送接口
    Route::any('UserGiftShoppingCard', 'User\UserPinTuanDataController@UserGiftShoppingCard');

});

//支付回调 getLkMemberPayHd
Route::post('getUserBuyLpjHd', 'User\UserPinTuanController@getUserBuyLpjHd');

//购物卡兑换话费支付回调 getLkMemberPayHd
Route::post('gwkDhHfHd', 'User\UserPinTuanController@gwkDhHfHd');

//查询用户的来拼金
Route::any('getUserDataLpj', 'User\UserPinTuanController@getUserDataLpj');

//查询用户的来拼金充值记录
Route::any('getUserDataLpjLog', 'User\UserPinTuanDataController@getUserDataLpjLog');

//查询用户的来70%usdt可兑换余额
Route::any('getUserDataUsdtYE', 'User\UserPinTuanController@getUserDataUsdtYE');

//查询用户的购物卡余额
Route::any('getUserShoppingCardMoney', 'User\UserPinTuanDataController@getUserShoppingCardMoney');

//查询购物卡拼团中奖记录
Route::any('selectGwkPintuanLog', 'User\UserGwkData@selectGwkPintuanLog');
//查询购物卡记录
Route::any('selectGwkXfLog', 'User\UserGwkData@selectGwkXfLog');






