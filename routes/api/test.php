<?php

use Illuminate\Support\Facades\Route;

//test测试接口路由
Route::post('test', 'Test\TestController@test');
Route::post('test2', 'Test\TestController@test2');
Route::post('test3', 'Test\TestController@test3');
Route::any('yttest', 'Test\YuntongController@index');
Route::post('yttest1', 'Test\YuntongController@pay');
Route::post('yttest2', 'Test\YuntongController@order_status');
Route::post('yttest3', 'Test\YuntongController@order_refund');
Route::any('yttest4', 'Test\YuntongController@notify');
Route::any('yttest5', 'Test\YuntongController@order');
Route::post('yttest6', 'Test\YuntongController@index');
Route::any('qrtest', 'Test\QrcodeController@index');
Route::any('bm-test', 'Test\BmApiController@index');
Route::any('bm-test1', 'Test\BmApiController@goodsAttrList');
Route::any('bm-test2', 'Test\BmApiController@getInfo');
Route::any('bm-test3', 'Test\BmApiController@utilityRecharge');
Route::any('bm-test4', 'Test\BmApiController@mobileGetInfo');
Route::any('bm-test5', 'Test\BmApiController@mobilePayBill');
Route::any('bm-test6', 'Test\BmApiController@demo');
Route::any('air', 'Test\BmApiController@airList');
Route::any('item', 'Test\BmApiController@itemsList');
Route::any('lines', 'Test\BmApiController@linesList');
Route::any(
    'dong/{action}',
    function (Illuminate\Http\Request $request, \App\Http\Controllers\API\Test\DongController $index, $action) {
        return $index->$action($request);
    }
);
Route::any(
    'ww/{action}',
    function (Illuminate\Http\Request $request, \App\Http\Controllers\API\Test\WanweiController $index, $action) {
        return $index->$action($request);
    }
);

Route::any('gather', 'Test\TestController@gatherTest');
//Route::any('gold', 'Test\TestController@updGold');

//Route::any('order-status', 'Test\TestController@updOrderStatus');
Route::any('sign', 'Test\TestController@getSign');
