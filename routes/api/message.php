<?php

/*Route::middleware(['auth:sanctum'])->group(function () {
    //获取用户消息
    Route::get('get-msg', 'Message\UserMsgController@getMsg');

    //获取系统消息
    Route::get('get-sys-msg', 'Message\UserMsgController@getSysMsg');

    //获取消息小红点
    Route::any('get-reddot', 'Message\UserMsgController@getReddot');

    //删除消息小红点
    Route::any('del-reddot', 'Message\UserMsgController@delReddot');

    //获取系统消息小红点
    Route::any('get-sys-reddot', 'Message\UserMsgController@getSysReddot');

    //删除系统消息小红点
    Route::any('del-sys-reddot', 'Message\UserMsgController@delSysReddot');

    //删除消息
    Route::get('del-msg', 'Message\UserMsgController@delMsg');

    //删除所有消息
    Route::get('del-all-msg', 'Message\UserMsgController@delAllMsg');

});*/

//获取系统消息
Route::get('get-msg', 'Message\UserMsgController@getMsg');

//获取系统消息
Route::get('get-sys-msg', 'Message\UserMsgController@getSysMsg');

//获取系统消息小红点
Route::any('get-sys-reddot', 'Message\UserMsgController@getSysReddot');

//删除系统消息小红点
Route::any('del-sys-reddot', 'Message\UserMsgController@delSysReddot');

//删除消息
Route::get('del-msg', 'Message\UserMsgController@delMsg');

//删除所有消息
Route::get('del-all-msg', 'Message\UserMsgController@delAllMsg');
