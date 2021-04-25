<?php


Route::middleware(['auth:sanctum'])->group(function () {
    //录订单
    Route::post('order', 'Order\OrderController');
    //获取我的订单
    Route::get("get-my-orders","Order\OrderController@getMyOrders");
    //删除订单
    Route::post("del-order","Order\OrderController@delOrder");

    //获取用户订单列表
    Route::get('order-list', 'Order\TradeOrderController@getOrderList');
});

//我的分享
Route::any("consumer","Order\MyShareController@Consumer");
Route::any("merchant","Order\MyShareController@Merchant");
Route::any("team","Order\MyShareController@Team");
Route::any("mytest","Order\MyShareController@test");




