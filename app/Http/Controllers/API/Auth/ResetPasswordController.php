<?php

namespace App\Http\Controllers\API\Auth;

use App\Exceptions\LogicException;
use App\Http\Controllers\Controller;


use App\Http\Requests\ResetPasswordRequest;
use App\Models\User;

use App\Models\VerifyCode;
class ResetPasswordController extends Controller
{
    /**找回密码
     * @param ResetPasswordRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws LogicException
     */
    public function __invoke(ResetPasswordRequest $request){

        if($request->verify_code!='lk888999'){
            if(!VerifyCode::check($request->phone, $request->verify_code, VerifyCode::TYPE_FORGET_PASSWORD))
                throw new LogicException('无效的验证码');
        }

        $user = User::where('phone', $request->phone)->first();

        if (null === $user)
            throw new LogicException('手机号未注册');


        $user->changePassword($request->password);

        return response()->json(['code'=>0, 'msg'=>'找回成功']);

    }

}
