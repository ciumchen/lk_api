<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {


});

//手动清空用户lk
//Route::any('qkSetUserLkAction', function (){
//    return view('qkUserLk');
//});
//清空页面首页
Route::any('qkSetUserLkAction', 'User\UserSetActionController@qkSetUserLkAction');
Route::any('jsQkSetUserLk', 'User\UserSetActionController@jsQkSetUserLk');
Route::any('setUserLkdj', 'User\UserSetActionController@setUserLkdj');


//清空单个用户lk和积分 qkSetOneUserLkAction
Route::any('qkSetOneUserLkAction', 'User\UserSetActionController@qkSetOneUserLkAction');
Route::any('jsQkSetOneUserLk', 'User\UserSetActionController@jsQkSetOneUserLk');
Route::any('qkSetOneUserLkAction', 'User\UserSetActionController@qkSetOneUserLkAction');

//添加排队积分
Route::any('addPdUserjf', 'User\UserSetActionController@addPdUserjf');
Route::any('setPdUserOrderNo', 'User\UserSetActionController@setPdUserOrderNo');
Route::any('qkSetOneUserLkAction', 'User\UserSetActionController@qkSetOneUserLkAction');



