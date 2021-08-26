<?php

namespace App\Models;

use App\Exceptions\LogicException;
use App\Services\UserGatherService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CardpayPassword extends Model
{
    use HasFactory;

    protected $table = 'cardpay_password';

    /**判断用户是否设置购物卡密码
     * @param int $uid
     * @return mixed
     * @throws
     */
    public function isSetupPwd(int $uid)
    {
        //获取数据
        $res = $this->userInfo($uid);
        if (!$res)
        {
            return json_encode(['code' => 10000, 'mag' => '用户信息不存在']);
        }

        //获取密码信息
        $userCard = $this->cardPwdInfo($uid);

        //返回
        if (isset($userCard))
        {
            return json_encode(['code' => 200, 'mag' => '用户已设置密码', 'res' => 1]);
        } else
        {
            return json_encode(['code' => 200, 'mag' => '用户未设置密码', 'res' => 0]);
        }
    }

    /**设置购物卡密码
     * @param array $data
     * @return mixed
     * @throws
     */
    public function addCardPwd(array $data)
    {
        //获取用户信息
        $userInfo = $this->userInfo($data['uid']);
        $data['phone'] = $userInfo->phone;

        return (new UserGatherService())->addCardPwd($data);
    }

    /**获取购物卡密码
     * @param array $data
     * @return mixed
     * @throws
     */
    public function provingCardPwd(array $data)
    {
        return (new UserGatherService())->provingCardPwd($data);
    }

    /**修改购物卡密码
     * @param array $data
     * @return mixed
     * @throws
     */
    public function editCardPwd(array $data)
    {
        $cardPassword = CardpayPassword::find($data['id']);
        $cardPassword->password = $data['password'];
        $cardPassword->save();

        return $cardPassword;
    }

    /**新增用户购物卡密码
     * @param array $data
     * @return mixed
     * @throws
     */
    public function setCardPwd(array $data)
    {
        $date = date('Y-m-d H:i:s');

        $cardPwd = new CardpayPassword();
        $cardPwd->uid = $data['uid'];
        $cardPwd->phone = $data['phone'];
        $cardPwd->password = $data['password'];
        $cardPwd->type = $data['type'];
        $cardPwd->created_at = $date;
        $cardPwd->updated_at = $date;
        $cardPwd->save();

        return $cardPwd;
    }

    /**获取用户购物卡密码信息
     * @param int $uid
     * @return mixed
     * @throws
     */
    public function cardPwdInfo(int $uid)
    {
        return CardpayPassword::where(['uid' => $uid, 'type' => 1])->first();
    }

    /**获取用户信息
     * @param int $uid
     * @return mixed
     * @throws
     */
    public function userInfo(int $uid)
    {
        return Users::where(['id' => $uid, 'status' => 1])->first();
    }

    /**获取用户信息
     * @param string $phone
     * @param int $type
     * @return mixed
     * @throws
     */
    public function daySum(string $phone, int $type)
    {
        //每天开始时间
        $stareDate = date('Y-m-d 00:00:00');
        //每天结束时间
        $endDate = date('Y-m-d 23:59:59');

        return VerifyCode::where(['phone' => $phone, 'type' => $type])
            ->where('created_at', '>=', $stareDate)
            ->where('created_at', '<=', $endDate)
            ->count();
    }

    /**格式化输出日期
     * Prepare a date for array / JSON serialization.
     * @param  \DateTimeInterface  $date
     * @return string
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
