<?php

namespace App\Models;

use App\Exceptions\LogicException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GatherGoldLogs extends Model
{
    use HasFactory;

    protected $table = 'gather_gold_logs';

    /**新增用户来拼金记录
     * @param int $gid
     * @param int $guid
     * @param int $uid
     * @return mixed
     * @throws LogicException
     */
    public function setGatherGold (int $gid, int $uid, int $guid)
    {
        $date = date('Y-m-d H:i:s');
        //默认来拼金
        $money = 100;
        //获取拼团状态
        $gatherInfo = (new Gather())->getGatherInfo($gid);

        //组装数据
        $gatherGold = new GatherGoldLogs();
        $gatherGold->gid = $gid;
        $gatherGold->guid = $guid;
        $gatherGold->uid = $uid;
        $gatherGold->money = $money;
        $gatherGold->status = $gatherInfo->status;
        $gatherGold->type = 1;
        $gatherGold->created_at = $date;
        $gatherGold->updated_at = $date;
        $gatherGold->save();
    }

    /**更新用户来拼金信息
     * @param int $gid
     * @param int $status
     * @return mixed
     * @throws LogicException
     */
    public function updGatherGold (int $gid, int $status)
    {
        return GatherGoldLogs::where('gid', $gid)
            ->update(['status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);
    }

    /**更新用户来拼金扣除信息
     * @param array $data
     * @param int $type
     * @return mixed
     * @throws LogicException
     */
    public function updGatherGoldType (array $data, int $type)
    {
        return GatherGoldLogs::whereIn('guid', $data)
            ->update(['type' => $type, 'updated_at' => date('Y-m-d H:i:s')]);
    }
}
