<?php


Route::middleware(['auth:sanctum'])->group(function () {
    //录订单
    Route::post('order', 'Order\OrderController');

    //获取我的订单
    Route::get("get-my-orders","Order\OrderController@getMyOrders");

    //获取我的所有订单
    Route::get("get-order-list","Order\OrderController@getOrdersList");

    //删除订单
    Route::post("del-order","Order\OrderController@delOrder");

});

//我的分享
Route::any("consumer","Order\MyShareController@Consumer");
Route::any("merchant","Order\MyShareController@Merchant");
Route::any("team","Order\MyShareController@Team");
Route::any("mytest","Order\MyShareController@test");

//当前登录用户的lk
Route::any("getLkCount","Order\MyShareController@getLkCount");

//获取当前用户今日录单金额总数
Route::any("getTodayLkCount","Order\MyShareController@getTodayLkCount");

//返回话费数据
Route::any("get-call","Order\MergeNotifyController@getCall");

//返回油卡数据
Route::any("get-gas","Order\MergeNotifyController@getGas");



