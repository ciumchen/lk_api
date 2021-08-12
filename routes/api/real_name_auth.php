<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {



});

//用户身份证ocr验证
Route::any('AlibabaOcrCheckImg', 'Alibaba\RealNameAuthController@AlibabaOcrCheckImg');
//查询用户身份证验证状态信息
Route::any('getUserOcrInfo', 'Alibaba\RealNameAuthController@getUserOcrInfo');



