<?php

namespace App\Http\Controllers\API\Intercom;

use App\Exceptions\LogicException;
use App\Http\Controllers\Controller;
use App\Services\SignInService;
use Illuminate\Http\Request;

class SignInController extends Controller
{
    /**
     * Description:签到
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return string
     * @throws \App\Exceptions\LogicException
     * @throws \Throwable
     * @author lidong<947714443@qq.com>
     * @date   2021/7/13 0013
     */
    public function signIn(Request $request)
    {
        $yx_uid = $request->input('yx_uid');
        try {
            $SignInService = new SignInService();
            $res = $SignInService->yxSignIn($yx_uid);
            if (is_array($res)) {
                $msg = "连续签到{$res['days']}天，获得{$res['points']}积分";
            } else {
                $msg = '签到成功';
            }
        } catch (\Exception $e) {
            throw new LogicException($e->getMessage());
        }
        return apiSuccess($res, $msg);
    }
}
