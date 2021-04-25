<?php

namespace App\Http\Controllers\API;

use App\Exceptions\LogicException;

use App\Http\Controllers\Controller;
use App\Http\Requests\VerifyCodesRequest;
use App\Models\VerifyCode;


class VerifyCodesController extends Controller
{
    /**发送验证码
     * @param VerifyCodesRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws LogicException
     */
    public function store(VerifyCodesRequest $request)
    {
        $code = app()->environment('prod', 'production')
                    ? (string) mt_rand(100000, 999999)
                    : '123456';

        $types = array_flip(VerifyCode::$typeLabels);

        $type = $types[$request->type];
        $phone = $request->phone;


//        if (in_array($type, [
//            VerifyCode::TYPE_CHANGE_PASSWORD,
//            VerifyCode::TYPE_GIVING_AWAY_BALANCES,
//            VerifyCode::TYPE_DESTROY_ACCOUNT,
//            VerifyCode::TYPE_WITHDRAW_TO_WALLET,
//            VerifyCode::TYPE_CHANGE_ADDRESS,
//        ])) {
//            if (null === $this->guard()->user()) {
//                throw new LogicException('账号未登录');
//            }
//            $phone = $this->guard()->user()->phone;
//        }

        VerifyCode::send($phone, $code, $type);

        return response()->json(['code'=>0, 'msg'=>'获取成功']);
    }
}
