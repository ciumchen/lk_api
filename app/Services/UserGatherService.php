<?php

namespace App\Services;

use App\Exceptions\LogicException;
use App\Models\CardpayPassword;
use App\Models\CheckPaymentPasswordLog;
use App\Models\VerifyCode;
use Illuminate\Support\Facades\DB;

class UserGatherService
{
    /**设置购物卡密码
     * @param array $data
     * @return mixed
     * @throws
     */
    public function addCardPwd(array $data)
    {
        //校验密码
        $data['type'] = VerifyCode::TYPE_GATHER_CARD;
        $this->checkPwd($data);
        $data['type'] = 1;

        //获取密码信息
        $userCard = (new CardpayPassword())->cardPwdInfo($data['uid']);
        if ($userCard)
        {
            return json_encode(['code' => 10000, 'mag' => '用户密码信息已存在']);
        }

        //新增密码记录
        $userCard = (new CardpayPassword())->setCardPwd($data);
        if ($userCard)
        {
            return json_encode(['code' => 200, 'mag' => '密码设置成功']);
        }else
        {
            return json_encode(['code' => 10000, 'mag' => '密码设置失败，请重新设置']);
        }
    }

    /**验证购物卡密码
     * @param array $data
     * @return mixed
     * @throws
     */
    public function provingCardPwd(array $data)
    {
        //校验密码
        if (strlen($data['password']) != 64)
        {
            return json_encode(['code' => 10000, 'mag' => '密码不合法，请重新输入']);
        }

        //获取密码信息
        $userCard = (new CardpayPassword())->cardPwdInfo($data['uid']);
        if (!$userCard)
        {
            return json_encode(['code' => 10000, 'mag' => '用户密码信息不存在']);
        }

        $cardInfo = (new CardpayPassword())->cardPwdInfo($data['uid']);
        if ($cardInfo->password != $data['password'])
        {
            return json_encode(['code' => 10000, 'mag' => '密码输入不正确，请重新输入']);
        }
    }

    /**修改购物卡密码
     * @param array $data
     * @return mixed
     * @throws
     */
    public function editCardPwd(array $data)
    {
        $data['type'] = VerifyCode::TYPE_GATHER_AGAINCARD;
        //获取密码信息
        $userCard = (new CardpayPassword())->cardPwdInfo($data['uid']);
        if (!$userCard)
        {
            return json_encode(['code' => 10000, 'mag' => '用户密码信息不存在']);
        }

        //获取用户信息
        $userInfo = (new CardpayPassword())->userInfo($data['uid']);
        $data['phone'] = $userInfo->phone;

        //校验密码
        $this->checkPwd($data);

        //组装数据
        $cardInfo = (new CardpayPassword())->cardPwdInfo($data['uid']);
        $data['id'] = $cardInfo->id;

        //修改用户密码
        $saveCard = (new CardpayPassword())->editCardPwd($data);
        if ($saveCard)
        {
            return json_encode(['code' => 200, 'mag' => '密码修改成功']);
        }else
        {
            return json_encode(['code' => 10000, 'mag' => '密码修改失败，请重新设置']);
        }
    }

    /**校验购物卡密码
     * @param array $data
     * @return mixed
     * @throws
     */
    public function checkPwd(array $data)
    {
        //判断手机验证码
        if (!VerifyCode::updateUserPhonCheck($data['phone'], $data['verifyCode'], $data['type']))
        {
            throw new LogicException('无效的验证码');
        }

        //判断两次密码是否一致
        if ($data['password'] != $data['confirmPassword'])
        {
            throw new LogicException('两次输入密码不一致');
        }

        //验证密码长度
        if (strlen($data['password']) != 64)
        {
            throw new LogicException('密码不合法，请重新输入');
        }
    }

    /**校验购物卡密码
     * @param array $data
     * @return mixed
     * @throws
     */
    public function codeSum(array $data)
    {
        $data['type'] = VerifyCode::TYPE_GATHER_AGAINCARD;
        //获取用户信息
        $userInfo = (new CardpayPassword())->userInfo($data['uid']);
        $data['phone'] = $userInfo->phone;

        //验证用户当天修改密码次数
        $userSum = (new CardpayPassword())->daySum($data['phone'], $data['type']);
        if ($userSum >= 1)
        {
            throw new LogicException('每天只能修改一次密码');
            //return json_encode(['code' => 10000, 'mag' => '每天只能修改一次密码']);
        }

        return $userSum;
    }

    //校验支付密码
    public function checkProvingCardPwd(array $data)
    {
        //校验密码
        if (strlen($data['password']) != 64)
        {
            return json_encode(['code' => 0, 'msg' => '密码不合法，请重新输入']);
        }

        //获取密码信息
        $userCard = (new CardpayPassword())->cardPwdInfo($data['uid']);
        if (!$userCard)
        {
            return json_encode(['code' => 0, 'msg' => '用户密码信息不存在']);
        }

        $cardInfo = (new CardpayPassword())->cardPwdInfo($data['uid']);
        if ($cardInfo->password != $data['password'])
        {
            return json_encode(['code' => 0, 'msg' => '密码输入不正确，请重新输入']);
        }

        $checkInfo = CheckPaymentPasswordLog::where('uid',$data['uid'])->first();
        if ($checkInfo==null){
            $checkData = new CheckPaymentPasswordLog();
            $checkData->uid = $data['uid'];
            $checkData->time = time();
            $checkData->num = 1;
            $checkData->save();
        }else{
            if ((time()-$checkInfo->time)<30){
                return json_encode(['code' => 0, 'msg' => '购物卡30秒内只能支付1次']);
            }else{
                $checkInfo->time = time();
                $checkInfo->save();
            }
        }
        return 200;
    }


}
