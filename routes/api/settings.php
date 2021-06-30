<?php
/** 获取配置信息 **/

//充值金额
Route::any('get-sys-price', 'Settings\SysPriceController@getSysPrice');
