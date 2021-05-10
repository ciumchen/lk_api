<?php

//test测试接口路由
Route::post('test', 'Test\TestController@test');
Route::post('test2', 'Test\TestController@test2');
Route::post('test3', 'Test\TestController@test3');
Route::post('yttest', 'Test\YuntongController@index');
Route::post('yttest1', 'Test\YuntongController@pay');
Route::post('yttest2', 'Test\YuntongController@order_status');
Route::post('yttest3', 'Test\YuntongController@order_refund');
Route::post('yttest4', 'Test\YuntongController@notify');
Route::post('yttest5', 'Test\YuntongController@index');
Route::post('yttest6', 'Test\YuntongController@index');



