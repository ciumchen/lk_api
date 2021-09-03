<?php

namespace App\Models;

use App\Exceptions\LogicException;
use App\Services\UserGatherService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\CardpayPassword
 *
 * @property int $id
 * @property int $uid 用户id
 * @property string $phone 用户手机号
 * @property string $password 密码
 * @property int $type 类型：1 购物卡
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder|CardpayPassword newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CardpayPassword newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CardpayPassword query()
 * @method static \Illuminate\Database\Eloquent\Builder|CardpayPassword whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CardpayPassword whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CardpayPassword wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CardpayPassword wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CardpayPassword whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CardpayPassword whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CardpayPassword whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
