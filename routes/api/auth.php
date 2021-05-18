<?php

/**
 * POST /v1/login 登录.
 */
Route::post('login', 'Auth\LoginController');
/*
 * POST register 注册.
 */
Route::post('register', 'Auth\RegisterController');

/*
 * POST /v1/password/reset 重置找回密码.
 */
Route::post('reset-password', 'Auth\ResetPasswordController');
//
///*
// * GET /v1/geetest 极验证初始化响应参数
// */
//Route::get('geetest', 'GeeTestController@index');
//
Route::middleware(['auth:sanctum'])->group(function () {
    /*
     * POST /v1/logout 登出.
     */
    Route::post('logout', function(\Illuminate\Http\Request $request){
        if($request->user()->tokens()->delete())
            return response()->json(['code'=>0, 'msg'=>'退出成功']);
        else
            return response()->json(['code'=>5564, 'msg'=>'退出失败']);
    });
});
