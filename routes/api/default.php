<?php

/*
 * POST /v1/verify-codes 发送短信验证码
 */
Route::post('verify-codes', 'VerifyCodesController@store');
//获取让利比列
Route::get("get-ratio","Order\OrderController@getRatio");
//获取商家分类
Route::get("get-business-category","Business\BusinessController@getBusinessCategory");
//获取广告
Route::get("get-ad","Business\BusinessController@getAd");
//获取商家列表-旧
Route::any("get-business-list","Business\BusinessController@getBusinessList");
//获取商家详情
Route::get("get-business-info","Business\BusinessController@getBusinessInfo");


//获取星级商户列表-新
Route::any("getStarBusinessList","Business\GetBusinessController@getStarBusinessList");

//获取所有商户列表-商家页-分类筛选搜索
Route::any("getAllBusinessList","Business\GetBusinessController@getAllBusinessList");

//获取用户的商家积分和已返回商家积分
Route::any("getUserJf","Business\GetBusinessController@getUserJf");






