<?php

use Illuminate\Support\Facades\Route;

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
    //修改邀请人
    Route::post('change-invite', 'User\UserController@changeInviteUid');
    //修改头像
    Route::post('change-avatar', 'User\UserController@changeUserAvatar');
    //修改个性签名
    Route::post('change-sign', 'User\UserController@changeUserSign');
    //修改性别
    Route::post('change-sex', 'User\UserController@changeUserSex');
    //修改邀请人
    Route::post('change-birth', 'User\UserController@changeUserBirth');
    //修改密码
    Route::post('change-pass', 'User\UserController@changeUserPassword');
    //修改姓名
    Route::post('change-real-name', 'User\UserController@changeRealName');
    //修改用户手机号
    Route::post('update-user-phone-one', 'User\UserController@updateUserPhoneOne');
    Route::post('update-user-phone-two', 'User\UserController@updateUserPhoneTwo');
    //批量充值订单详情
    Route::get('my-batch-details', 'User\UserOrderController@batchMobileOrderDetails');
});
//新增消费者统计和新增商家统计
Route::any("addConsumer", "User\CountUserController@addConsumer");
Route::any("addMerchant", "User\CountUserController@addMerchant");
//消费者-待激励统计//消费者-今日分配//消费者-昨日lk总数//消费者-昨日分配
Route::any("consumerCount", "User\CountUserController@consumerCount");
//商户-待激励统计//商户-今日分配//商户-昨日lk总数//商户-昨日分配
Route::any("merchantCount", "User\CountUserController@merchantCount");
//获取用户的消费记录
Route::any("getUserOrderJl", "User\RecordsOfConsumptionController@getUserOrderJl");
//获取用户的资产记录
Route::any("getUserAssets", "User\RecordsOfConsumptionController@getUserAssets");
//获取用户的冻结资产记录
Route::any("getUserFreeze", "User\RecordsOfConsumptionController@getUserFreeze");
//获取用户分享积分
Route::any("getUserAssetsFxJf", "User\RecordsOfConsumptionController@getUserAssetsFxJf");
//用户的公益贡献接口
Route::any("getUoserGYGX", "User\RecordsOfConsumptionController@getUoserGYGX");

/********************************* 我的分享 *********************************/
//是否为团长
Route::any("is-manage", "User\MyShareController@isManage");
//团员数据
Route::any("user-share", "User\MyShareController@userShare");
//商家数据
Route::any("shop-share", "User\MyShareController@shopShare");
//团员资产记录
Route::any("users-assets", "User\MyShareController@usersAssets");
//团长资产记录
Route::any("heads-assets", "User\MyShareController@headsAssets");
//团队资产记录
Route::any("team-assets", "User\MyShareController@teamAssets");
//团员、团队资产总奖励
Route::any("profit-total", "User\MyShareController@profitTotal");
//获取用户积分和lk
Route::any("getUserLkIntegral", "User\getUserInfoController@getUserLkIntegral");

//获取用户消费积分lk百分百比
Route::any("getUserIntegralbfb", "User\getUserInfoController@getUserIntegralbfb");

//获取用户商家积分lk百分百比
Route::any("getUserShIntegralbfb", "User\getUserInfoController@getUserShIntegralbfb");

//获取用户资产
Route::any("getUserAssetInfo", "User\getUserInfoController@getUserAssetInfo");











