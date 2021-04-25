<?php

//调用支付接口
Route::post('payment', 'Payment\AdaPayController@CreatePay');

//支付回调地址
Route::any('notify', 'Payment\NotifyController@callBack');
