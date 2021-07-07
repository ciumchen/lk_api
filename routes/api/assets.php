<?php

use Illuminate\Support\Facades\Route;

/********************** 兑换 **********************/

//获取用户 usdt
Route::get('usdt-amount', 'Assets\ConvertController@getUsdtAmount');
//获取兑换金额
Route::get('compute-price', 'Assets\ConvertController@computePrice');
//兑换话费
Route::post('phone-bill', 'Assets\ConvertController@phoneBill');
