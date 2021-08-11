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

    /**获取用户来拼金信息
     * @param int $uid
     * @param int $page
     * @param int $perPage
     * @return mixed
     * @throws LogicException
     */
    public function getGatherGold (int $uid, int $page, int $perPage)
    {
        return GatherGoldLogs::where(['uid' => $uid])
            ->orderBy('created_at', 'desc')
            ->forPage($page ?? 1, $perPage ?? 10)
            ->get()
            ->each(function ($item) {
                $item->money = (int)$item->money;
                $item->status = self::GATHER_STATUS[$item->status];
                $item->type = self::GATHERGOLD_TYPE[$item->type];
            });
    }

    //拼团状态
    const GATHER_STATUS = [
        0 => '关闭',
        1 => '开启',
        3 => '终止',
    ];

    //来拼金状态
    const GATHERGOLD_TYPE = [
        0 => '退还',
        1 => '扣除',
    ];

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
