<?php


Route::middleware(['auth:sanctum'])->group(function () {
    //获取提现信息
    Route::get('get-transfer-info', 'Transfer\TransferController@getTransferInfo');
    //绑定地址
    Route::post('bind-address', 'Transfer\TransferController@bindAddress');
    //提现
    Route::post('transfer', 'Transfer\TransferController');

    //赠送
    Route::any('giveTransfer', 'Transfer\GiveTransferController');


});
