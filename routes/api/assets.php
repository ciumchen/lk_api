<?php

use Illuminate\Support\Facades\Route;

/********************** 兑换 **********************/

//获取用户 usdt
Route::get('usdt-amount', 'Assets\ConvertController@getUsdtAmount');
//获取兑换金额
Route::get('compute-price', 'Assets\ConvertController@computePrice');
