<?php

/*
 * POST /v1/verify-codes 发送短信验证码
 */
Route::post('verify-codes', 'VerifyCodesController@store');
Route::post('verify-codes', 'VerifyCodesController@store');
//获取让利比列
Route::get("get-ratio","Order\OrderController@getRatio");
//获取商家分类
Route::get("get-business-category","Business\BusinessController@getBusinessCategory");
//获取广告
Route::get("get-ad","Business\BusinessController@getAd");
//获取商家列表
Route::get("get-business-list","Business\BusinessController@getBusinessList");
//获取商家详情
Route::get("get-business-info","Business\BusinessController@getBusinessInfo");


//获取星级商户列表
Route::get("getStarBusinessList","Business\GetBusinessController@getStarBusinessList");




