<?php

namespace App\Services;

use App\Exceptions\LogicException;
use App\Models\AdvertUsers;
use App\Models\GatherTrade;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Users;
use Illuminate\Support\Facades\DB;

class AdvertIsementService
{
    const CHANNEL_ID = 20030;
    /**新增用户广告收入记录
     * @param array $data
     * @return mixed
     * @throws
     */
    public function addUsereIncome (array $data)
    {
        //验证签名
        $param = $data['award'] . $data['packagename'] . $data['type'] . $data['uid'] . $data['unique_id'];
        $checkSign = strtolower(md5($param . md5(self::CHANNEL_ID)));
//        dd($checkSign);

        //判断签名是否一致
        if ($checkSign != $data['sign'])
        {
            return json_encode(['status' => 10000, 'msg' => '签名不合法，非法操作！']);
        }

        //数据是否已存在
        $where = [
            'unique_id' => $data['unique_id'],
        ];
        $userAdvertInfo = (new AdvertUsers())->getUserAdvert($where);
        if ($userAdvertInfo)
        {
            return json_encode(['status' => 10000, 'msg' => '已发放广告奖励！']);
        }

        DB::beginTransaction();
        try {
            //新增用户广告记录
            (new AdvertUsers())->setUserAdvert($data);
            //更新用户广告奖励
            (new Users())->updAdvertAward($data['uid'], $data['award']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw new LogicException('发放广告奖励失败！');
        }
        DB::commit();

        return json_encode(['status' => 1, 'msg' => '发放广告奖励成功！']);
    }
}
