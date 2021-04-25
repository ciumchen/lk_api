<?php


Route::middleware(['auth:sanctum'])->group(function () {
    //申请商家
    Route::post('apply-business', 'User\UserController@applyBusiness');
    //获取商家申请状态
    Route::get('get-apply-business', 'Business\BusinessController@getApplyBusiness');

    //实名认证
    Route::post('real-name', 'User\UserController@realName');
    //获取用户信息
    Route::get('get-user', 'User\UserController@getUser');

    //获取资产记录
    Route::get('get-assets-logs', 'User\AssetsController@getAssetsLogs');

    //获取积分记录
    Route::get('get-integral-log', 'User\UserController@getMyIntegralLog');

});
//新增消费者统计和新增商家统计
Route::any("addConsumer","User\CountUserController@addConsumer");
Route::any("addMerchant","User\CountUserController@addMerchant");

//消费者-待激励统计//消费者-今日分配//消费者-昨日lk总数//消费者-昨日分配
Route::any("consumerCount","User\CountUserController@consumerCount");
//商户-待激励统计//商户-今日分配//商户-昨日lk总数//商户-昨日分配
Route::any("merchantCount","User\CountUserController@merchantCount");




