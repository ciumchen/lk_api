<?php
//test测试接口路由
Route::post('test', 'Test\TestController@test');
Route::post('test2', 'Test\TestController@test2');
Route::post('test3', 'Test\TestController@test3');
Route::any('orderTest', 'Test\TestController@orderTest');
Route::any('yttest', 'Test\YuntongController@index');
Route::post('yttest1', 'Test\YuntongController@pay');
Route::post('yttest2', 'Test\YuntongController@order_status');
Route::post('yttest3', 'Test\YuntongController@order_refund');
Route::any('yttest4', 'Test\YuntongController@notify');
Route::any('yttest5', 'Test\YuntongController@order');
Route::post('yttest6', 'Test\YuntongController@index');
Route::any('qrtest', 'Test\QrcodeController@index');
