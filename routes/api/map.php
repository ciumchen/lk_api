<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {

});

//测试经纬度获取百度地图信息
Route::post('mapTest', 'Map\BaiduMapApiController@mapTest');






