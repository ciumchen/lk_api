<?php

use App\Http\Controllers\API\ThirdAuth\AlipayAuthController;
use Illuminate\Support\Facades\Route;

///////////////
// 需要登录验证//
///////////////
Route::middleware(['auth:sanctum'])->group(
    function () {
        Route::any('alipay-auth', 'ThirdAuth\AlipayAuthController@callback');
        // 获取H5授权链接
        Route::any('alipay-auth-h5', 'ThirdAuth\AlipayAuthController@getAuthUrlH5');
        // 用戶綁定
        Route::post('alipay-bind', 'ThirdAuth\AlipayAuthController@userBinding');
    }
);
/** ************************************************************************** **/
/////////////////
// 不需要登录验证//
////////////////
Route::any('alipay-notify', 'ThirdAuth\AlipayNotifyController@authNotify');
// 同步回调返回地址
Route::get('alipay-after-auth/{uid}', function (
    Illuminate\Http\Request $request,
    AlipayAuthController $Controller,
    $uid
) {
    return $Controller->AlipayAfterAuth($request, $uid);
})->name('h5-auth');
