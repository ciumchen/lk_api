<?php

use Illuminate\Support\Facades\Route;

/*Route::middleware(['auth:sanctum'])->group(function () {
    //获取积分记录
    Route::get('log-list', 'Shop\GetIntegralController@logsList');

    //获取排队积分记录
    Route::get('line-list', 'Shop\GetIntegralController@lineList');
});*/

//测试用户身份证
Route::any('AlibabaTest', 'Alibaba\RealNameAuthController@AlibabaTest');
