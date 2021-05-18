<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResources;
use App\Models\User;
use App\Models\UserData;
use App\Models\VerifyCode;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
class LoginController extends Controller
{
    /**登录用户
     * @param LoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(LoginRequest $request){

        $driverMethod = 'loginBy'.Str::studly($request->driver);

        $user = $this->{$driverMethod}($request);
        //用户状态
        $user->checkStatus();

        $user->userData()->update([
            'last_login' => now(),
            'last_ip' => request_ip(),
        ]);

        //撤销授权
        $user->tokens()->delete();
        return response()->json([
            'code' => 0,
            'token' => $user->createToken($request->header('cli-os')??'h5')->plainTextToken,
            'user' => UserResources::make($user)->jsonSerialize(),
        ]);

    }

    /**
     * 使用手机号和密码登录.
     *
     * @param \App\Http\Requests\API\V1\Auth\LoginRequest $request
     *
     * @return \App\Models\User
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function loginByPassword(LoginRequest $request)
    {
        $user = User::where('phone', $request->phone)->first();

//        Log::debug(data('y-m-d',time()).log, [$request->user]);
//        Log::info(data('y-m-d-h',time()).log, [$request->user]);
//


        if (!optional($user)->verifyPassword($request->password)) {
            throw ValidationException::withMessages(['phone' => ['手机号或密码错误']]);
        }

        return $user;
    }

    /**使用手机号和验证码登录
     * @param LoginRequest $request
     * @return mixed
     * @throws ValidationException
     */
    protected function loginByVerifyCode(LoginRequest $request)
    {
        if (!VerifyCode::check($request->phone, $request->verify_code, VerifyCode::TYPE_LOGIN)) {
            throw ValidationException::withMessages(['verify_code' => ['无效的验证码']]);
        }

        if ($user = User::where('phone', $request->phone)->first()) {
            return $user;
        }

        throw ValidationException::withMessages(['phone' => ['手机号码未注册']]);
    }
}
