<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
//购买会员
    Route::post('PurchaseLkMember', 'User\UsersController@PurchaseLkMember');
});

//支付回调 getLkMemberPayHd
Route::post('getLkMemberPayHd', 'User\UsersController@getLkMemberPayHd');

//获取用户邀请直推下级所有用户信息
Route::any('getUserInviteUserData', 'User\getUserInfoController@getUserInviteUserData');






