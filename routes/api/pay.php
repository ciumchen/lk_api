<?php

Route::middleware(['auth:sanctum'])->group(function () {


});
//调用支付接口
Route::post('payment', 'Payment\AdaPayController@CreatePay');

//支付回调地址
Route::any('notify', 'Payment\NotifyController@callBack');

//支付失败再次支付
Route::post('again-pay', 'Payment\AdaPayController@againPay');
