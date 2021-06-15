<?php

Route::middleware(['auth:sanctum'])
     ->group(function () {
         //录订单
         Route::post('order', 'Order\OrderController');
         //获取我的订单
         Route::get("get-my-orders", "Order\OrderController@getMyOrders");
         //删除订单
         Route::post("del-order", "Order\OrderController@delOrder");
         //手机充值[斑马]
         Route::post('mobile-order', 'Order\MobileRechargeController@setOrder');
         //手机代充[斑马]
         Route::post('mobile-dl-order', 'Order\MobileRechargeController@setDlOrder');
         /* 视频会员订单生成[斑马] */
         Route::post('video-set-order', 'Order\VideoRechargeController@setOrder');
     });
//我的分享
Route::any("consumer", "Order\MyShareController@Consumer");
Route::any("merchant", "Order\MyShareController@Merchant");
Route::any("team", "Order\MyShareController@Team");
Route::any("mytest", "Order\MyShareController@test");
//当前登录用户的lk
Route::any("getLkCount", "Order\MyShareController@getLkCount");
//获取当前用户今日录单金额总数
Route::any("getTodayLkCount", "Order\MyShareController@getTodayLkCount");
//返回话费数据
Route::any("get-call", "Order\MergeNotifyController@getCall");
//返回油卡数据
Route::any("get-gas", "Order\MergeNotifyController@getGas");
//返回佐兰话费数据
Route::any("get-call-defray", "Order\MergeNotifyController@getCallDefray");
//查询当前用户是邀请人所获得的商家积分记录
Route::any("getInvitePoints", "User\RecordsOfConsumptionController@getInvitePoints");

//获取用户分红积分变动
Route::any("getUserIntegralLogs", "User\RecordsOfConsumptionController@getUserIntegralLogs");

//手机充值回调[斑马]
Route::any('mobile-notify', 'Order\MobileNotifyController@callback');
//手机充值测试[斑马]
Route::any('mobile-recharge', 'Order\MobileRechargeController@rechargeTest');
/* 视频会员查询[斑马] */
Route::any('video-get-list', 'Order\VideoRechargeController@getVideoList');
/* 视频会员充值测试[斑马] */
Route::any('video-recharge', 'Order\VideoRechargeController@rechargeTest');
