<?php


Route::middleware(['auth:sanctum'])->group(function () {
    //申请商家
    Route::post('apply-business', 'User\UserController@applyBusiness');
    Route::get('get-business', 'Business\BusinessController@getBusiness');

    //获取商家可修改信息
    Route::get('get-business-data', 'Business\BusinessController@getBusinessData');
    //修改商家
    Route::post('update-business', 'Business\BusinessController@updateBusinessData');

//新申请商家接口
    Route::post('newApplyBusiness', 'User\UserController@newApplyBusiness');

});
//获取商家分类
Route::post('getBusinessFl', 'Business\BusinessController@getBusinessFl');
