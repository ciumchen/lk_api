<?php

Route::middleware(['auth:sanctum'])->group(function () {
    //获取用户消息
    //Route::get('get-msg', 'Message\UserMsgController@getMsg');

    //获取消息小红点
    Route::any('get-reddot', 'Message\UserMsgController@getReddot');

    //删除消息小红点
    Route::any('del-reddot', 'Message\UserMsgController@delReddot');
});

Route::get('get-msg', 'Message\UserMsgController@getMsg');
