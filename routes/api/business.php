<?php


Route::middleware(['auth:sanctum'])->group(function () {
    //申请商家
    Route::post('apply-business', 'User\UserController@applyBusiness');


    //获取商家可修改信息
    Route::get('get-business-data', 'Business\BusinessController@getBusinessData');
    //修改商家
    Route::post('update-business', 'Business\BusinessController@updateBusinessData');



});
Route::get('get-business', 'Business\BusinessController@getBusiness');
