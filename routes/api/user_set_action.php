<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {


});

//手动清空用户lk
//Route::any('qkSetUserLkAction', function (){
//    return view('qkUserLk');
//});
Route::any('qkSetUserLkAction', 'User\UserSetActionController@qkSetUserLkAction');
Route::any('jsQkSetUserLk', 'User\UserSetActionController@jsQkSetUserLk');
Route::any('setUserLkdj', 'User\UserSetActionController@setUserLkdj');









