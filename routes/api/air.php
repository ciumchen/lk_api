<?php
/** 机票充值 **/

//机场站点
Route::any('air-list', 'Airticket\StationsListController@airList');

//标准商品列表
Route::any('get-items', 'Airticket\ItemsListController@getItems');

//航线列表
Route::any('lines-list', 'Airticket\LinesListController@linesList');
    
//机票订单
Route::any('order-pay', 'Airticket\OrderPayBillController@orderPay');

//机票退订
Route::any('air-refund', 'Airticket\OrderRefundController@airRefund');
