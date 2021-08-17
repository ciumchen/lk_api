<?php

namespace App\Models;

use App\Exceptions\LogicException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class GatherUsers extends Model
{
    use HasFactory;

    protected $table = 'gather_users';

    /**新增用户来拼金记录
     * @param int $gid
     * @param int $guid
     * @param int $uid
     * @return mixed
     * @throws LogicException
     */
    public function setGatherUsers (int $gid, int $uid)
    {
        $date = date('Y-m-d H:i:s');

        //组装数据
        $gatherUsers = new GatherUsers();
        $gatherUsers->gid = $gid;
        $gatherUsers->uid = $uid;
        $gatherUsers->status = 1;
        $gatherUsers->type = 0;
        $gatherUsers->created_at = $date;
        $gatherUsers->updated_at = $date;
        $gatherUsers->save();

        return $gatherUsers;
    }

    /**获取用户拼团信息
     * @param int $uid
     * @return mixed
     * @throws LogicException
     */
    public function getUserAllInfo (int $uid)
    {
        return GatherUsers::where(['uid' => $uid])->get();
    }

    /**获取用户拼团信息
     * @param int $uid
     * @return mixed
     * @throws LogicException
     */
    public function getGatherUserList (int $gid)
    {
        return GatherUsers::where(['gid' => $gid])->get();
    }

    /**获取多个用户拼团信息
     * @param array $data
     * @return mixed
     * @throws LogicException
     */
    public function getGatherUserArr (array $data)
    {
        return GatherUsers::whereIn('id', $data)->get(['id', 'gid', 'uid']);
    }

    /**获取用户单个拼团信息
     * @param int $gid
     * @param int $uid
     * @return mixed
     * @throws LogicException
     */
    public function getGatherUserInfo (int $gid, int $uid)
    {
        return GatherUsers::where(['gid' => $gid, 'uid' => $uid])->get();
    }

    /**更新用户参团获奖信息
     * @param array $data
     * @return mixed
     * @throws LogicException
     */
    public function updGatherUserType (array $data)
    {
        return GatherUsers::whereIn('id', $data)->update(['type' => 1, 'created_at' => date('Y-m-d H:i:s')]);
    }

    /**更新用户拼团信息
     * @param int $id
     * @return mixed
     * @throws LogicException
     */
    public function updGatherUser (int $id)
    {
        $gatherUser = GatherUsers::find($id);
        $gatherUser->type = 1;
        $gatherUser->save();
    }

    /**获取用户当天拼团参团总数
     * @param int $uid
     * @return mixed
     * @throws LogicException
     */
    public function getUserAllSum (int $uid)
    {
        //每天开始时间
        $stareDate = date('Y-m-d 00:00:00');
        //每天结束时间
        $endDate = date('Y-m-d 23:59:59');
        return GatherUsers::where(['uid' => $uid])
                ->where('created_at', '>=', $stareDate)
                ->where('created_at', '<=', $endDate)
                ->count();
    }

    /**获取用户当天单个拼团参团总数
     * @param int $gid
     * @param int $uid
     * @return mixed
     * @throws LogicException
     */
    public function getGatherUserSum (int $gid, int $uid)
    {
        //每天开始时间
        $stareDate = date('Y-m-d 00:00:00');
        //每天结束时间
        $endDate = date('Y-m-d 23:59:59');
        return GatherUsers::where(['gid' => $gid, 'uid' => $uid])
                ->where('created_at', '>=', $stareDate)
                ->where('created_at', '<=', $endDate)
                ->count();
    }

    /**获取用户拼团及中奖信息
     * @param array $data
     * @return mixed
     * @throws LogicException
     */
    public function getGatherInfo (array $data)
    {
        //设置结束拼团时间戳
        $diffTime = 72 * 3600;
        //拼团总人数
        $userRatio = Setting::getSetting('gather_users_ number') ?? 100;
        $param = [
            'diffTime'  => $diffTime,
            'userRatio' => $userRatio,
        ];
        //$status = $data['status'] == 0 ? true : false;

        //获取拼团未开始、已开始及已终止信息
        $gatherList = DB::table('gather as g')
            ->join('gather_users as gu', 'g.id', 'gu.gid')
            /*->when($status, function ($query) {
                return $query->where(['g.status' => 0]);
            }, function ($query) {
                return $query->whereIn('g.status', [1, 3]);
            })*/
            ->where(['gu.uid' => $data['uid']])
            ->orderBy('gu.created_at', 'desc')
            ->groupBy('g.id')
            ->forPage($data['page'] ?? 1, $data['perPage'] ?? 10)
            ->select(DB::raw('count(gu.id) as total, g.id as gid, g.created_at, g.status, g.type, g.status, gu.type as guType'))
            ->get()
            ->each(function ($item) use ($param) {
                if (!$item->status)
                {
                    $item->onStatus = '已参与';
                } else
                {
                    $item->onStatus = self::USER_TYPE[$item->guType];
                }
                $item->userSum = count(json_decode($this->getGatherUserList($item->gid), 1));
                $item->type = self::GATHER_TYPE[$item->type] ?? '';
                $item->surplusTime = $item->status != 0 ? '已结束' : intdiv(strtotime($item->created_at) + $param['diffTime'] - time(), 3600);
                unset($item->created_at, $item->guType);
            });

        return json_decode($gatherList, 1);
    }

    /**获取拼团中奖信息
     * @param int $gid
     * @return mixed
     * @throws LogicException
     */
    public function getGatherLottery (int $gid)
    {
        //获取拼团获奖用户
        $userLottery = GatherUsers::where(['gid' => $gid, 'type' => 1])->get();
        $uids = array_column(json_decode($userLottery, 1), 'uid');

        //获取用户信息
        $userData = Users::whereIn('id', $uids)->get(['id', 'avatar', 'phone'])
            ->each(function ($item) {
                $item->phone = substr_replace($item->phone, '****', 3, 4);
                $item->content = '100元来客购物卡';
            });

        return json_decode($userData, 1);
    }

    /**获取用户来拼金额度
     * @param int $uid
     * @return mixed
     * @throws LogicException
     */
    public function getUserGold (int $uid)
    {
        return Users::where(['id' => $uid, 'status' => 1])->value('balance_tuan');
    }

    /**获取用户来拼金额度
     * @param array $uidData
     * @return mixed
     * @throws LogicException
     */
    public function getUsersGold (array $uidData)
    {
        $userGoldList = Users::whereIn('id', $uidData)->get(['id', 'balance_tuan']);
        return json_decode($userGoldList, 1);
    }

    /**获取用户拼团来拼金总额
     * @param int $uid
     * @return mixed
     * @throws LogicException
     */
    public function getAdvanceGold (int $uid)
    {
        //获取用户来拼金总数
        $userGoldSum = $this->getUserGold($uid);
        //获取用户来拼金扣减总数
        $minusSum = (new GatherGoldLogs())->minusUserGold($uid);

        //用户可提现总额
        $diffGold = bcsub($userGoldSum, $minusSum, 2);
        //返回
        return [
            'userGoldSum' => $userGoldSum,
            'minusSum'    => sprintf('%.2f', $minusSum),
            'diffGold'    => $diffGold,
        ];
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

    const USER_TYPE = [
        0 => '未中奖',
        1 => '已中奖',
    ];

    const GATHER_TYPE = [
        1 => '话费',
        2 => '美团',
        3 => '油卡',
        4 => '录单',
    ];
}
