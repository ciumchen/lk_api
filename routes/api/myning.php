<?php

//定时任务--待添加积分
Route::any('orderTest', 'Test\MyNingController@orderTest');
//自动审核录单
//Route::any('pushOrder', 'Test\MyNingController@pushOrder');

//添加用户积分和商家积分
Route::any("addUserIntegral", "User\AddIntegralController@addUserIntegral");

//获取消费者和商家排队积分记录
Route::any("getUserIntegral", "User\getIntegralController@getUserIntegral");

//修改手机号
Route::any("updateUserPhone", 'Test\MyNingController@updateUserPhone');

//获取用户资产
Route::any("getUserAssetInfoAndLog", 'Test\MyNingController@getUserAssetInfoAndLog');

//解封用户资产账号
Route::any("xfUserAssetFH", 'Test\MyNingController@xfUserAssetFH');

//商城初始化导入记录
Route::any("initDrOrderLog", 'Test\MyNingController@initDrOrderLog');

Route::any("updateShopOrderName", 'Test\MyNingController@updateShopOrderName');
//修改导入订单的类型
Route::any("updateShopDrLog", 'Test\MyNingController@updateShopDrLog');
//查询导入时间
Route::any("getAddOrderTime", 'Test\MyNingController@getAddOrderTime');

//修改导入时间
Route::any("setShopOrderTime", 'Test\MyNingController@setShopOrderTime');

//扣除用户积分
Route::any("kcUserShopJf", 'Test\MyNingController@kcUserShopJf');

//清空商城卡单处理
//Route::any("setShopKdOrderId", 'Test\MyNingController@setShopKdOrderId');

//初始化修改用户手机号记录
//Route::any("clearUserPhoneUpdateLog", 'Test\MyNingController@clearUserPhoneUpdateLog');

//批量修改商家信息表审核状态
Route::any("plUpdateBussStutas", 'Test\MyNingController@plUpdateBussStutas');

//同商城用户的uid
Route::any("updateLkShopUserId", 'Test\MyNingController@updateLkShopUserId');

//修改用户商家身份
Route::any("updateUserInfoRole", 'Test\MyNingController@updateUserInfoRole');

//修改商家申请后没有插入商家表的记录
Route::any("insertUserBuinssData", 'Test\MyNingController@insertUserBuinssData');

//手动扣除用户积分
Route::any("myning-test", 'Test\MyNingController@myningtest');
//test模板测试
Route::any("getTable", 'Test\MyNingController@getTable');


//修改用户申请商家审核状态UpdateBusinessApply
Route::any("UpdateBusinessApply", 'Test\MyNingController@UpdateBusinessApply');


//修改手机号用户的邀请人
Route::any("updateUserYqrUid", 'Test\MyNingController@updateUserYqrUid');

//手动处理修改购物卡兑换代充没有添加积分的记录
Route::any("sdUpdateDchfjf", 'Test\MyNingController@sdUpdateDchfjf');


//************************************************************************
////扣除用户积分  152087
//Route::any("kcUserJf", 'Test\MyNingController@kcUserJf');
//
////修改导入让利金额
//Route::any("xgcount_profit_price", 'Test\MyNingController@xgcount_profit_price');


//扣除lk
//Route::any("del_kcuserLk", 'Test\MyNingController@del_kcuserLk');

//扣除lk
//Route::any("del_sh_kcuserLk", 'Test\MyNingController@del_sh_kcuserLk');
//************************************************************************

////批量修改非商家的用户的商家身份为1
//Route::any("getUserOnShUpdate", 'Test\MyNingController@getUserOnShUpdate');

//批量生成图片记录
Route::any("plInsertUserImages", 'Test\MyNingController@plInsertUserImages');

//批量修改商家门头照
Route::any("plUpdateUserShimg2", 'Test\MyNingController@plUpdateUserShimg2');


//购买会员支付完成添加积分测试
Route::any("addJfTestOrder", 'User\UsersController@addJfTestOrder');


//修改用户会员状态
Route::any("setUserInfoMemberStatus", 'Test\MyNingController@setUserInfoMemberStatus');

//测试昨日分红记录查询
Route::any("getYesterdayRecharge", 'Test\MyNingController@getYesterdayRecharge');

//测试昨日分红记录查询
Route::any("getYesterdayRecharge", 'Test\MyNingModifyController@setSdkcUserJf');




