<?php
//调用支付接口
use Illuminate\Support\Facades\Route;

Route::post('payment', 'Payment\AdaPayController@CreatePay');
//支付回调地址
Route::any('notify', 'Payment\NotifyController@callBack');
//支付失败再次支付
Route::post('again-pay', 'Payment\AdaPayController@againPay');
/******************************** 云通支付 ********************************/
//云通支付接口
Route::post('yun-pay', 'Payment\YuntongPayController@createPay');
//云通二次支付
Route::post('yun-pay-again', 'Payment\YuntongPayController@againPay');
//云通支付回调
Route::post('yun-notify', 'Payment\YuntongNotifyController@callBack');
/****** *******/
//机票支付
Route::post('air-pay', 'Payment\YuntongPayController@airPay');
//机票回调支付
Route::post('air-notify', 'Payment\YuntongNotifyController@airPayNotify');
//机票二次支付
Route::post('air-again-pay', 'Payment\YuntongPayController@airAgainPay');
/****** *******/
//斑马接口订单支付
Route::post('bm-pay', 'Payment\YuntongPayController@bmPay');
//斑马接口订单回调
Route::any('bm-pay-notify', 'Payment\YuntongNotifyController@bmPayCallback');
