<?php


Route::middleware(['auth:sanctum'])->group(function () {
    //获取提现信息
    Route::get('get-transfer-info', 'Transfer\TransferController@getTransferInfo');
    //绑定地址
    Route::post('bind-address', 'Transfer\TransferController@bindAddress');
    //提现
    Route::post('transfer', 'Transfer\TransferController');

    //usdt赠送
    Route::any('giveTransfer', 'Transfer\GiveTransferController');

    //usdt兑换iets
    Route::any('AssetConversion', 'Transfer\AssetConversionController');

    //获取用户的资产
    Route::any('getUserAssetsInfo', 'Transfer\UserAssetsInfoController@getUserAssetsInfo');
    //iets提现
    Route::any('ietsWithdrawal', 'Transfer\IetsWithdrawalController');
    //iets赠送
    Route::any('giveIets', 'Transfer\GiveIetsController');


});
