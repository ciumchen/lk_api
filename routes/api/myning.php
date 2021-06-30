<?php

//定时任务--待添加积分
Route::any('orderTest', 'Test\MyNingController@orderTest');
//自动审核录单
Route::any('pushOrder', 'Test\MyNingController@pushOrder');

//添加用户积分和商家积分
Route::any("addUserIntegral", "User\AddIntegralController@addUserIntegral");

//获取用户待添加积分
Route::any("getUserIntegral", "User\getIntegralController@getUserIntegral");

//修改手机号
Route::any("updateUserPhone", 'Test\MyNingController@updateUserPhone');

//获取用户资产
Route::any("getUserAssetInfo", 'Test\MyNingController@getUserAssetInfo');

//解封用户资产账号
Route::any("xfUserAssetFH", 'Test\MyNingController@xfUserAssetFH');

//初始化导入记录
Route::any("initDrOrderLog", 'Test\MyNingController@initDrOrderLog');

Route::any("updateShopOrderName", 'Test\MyNingController@updateShopOrderName');





