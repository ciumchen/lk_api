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
