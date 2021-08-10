<?php

namespace App\Models;

use App\Exceptions\LogicException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Gather extends Model
{
    use HasFactory;

    protected $table = 'gather';

    /**新增拼团
     * @param int $type
     * @return mixed
     * @throws LogicException
     */
    public function setGather (int $type)
    {
        $date = date('Y-m-d H:i:s');

        //组装数据
        $gather = new Gather();
        $gather->status = 0;
        $gather->type = $type;
        $gather->created_at = $date;
        $gather->updated_at = $date;
        $gather->save();

        return $gather;
    }

    /**获取拼团信息
     * @param Request $request
     * @return mixed
     * @throws LogicException
     */
    public function getGatherList ()
    {
        //返回
        $gatherData = DB::table('gather as g')
            ->leftJoin('gather_users as gu', 'g.id', 'gu.gid')
            ->where(['g.status' => 0])
            ->select(DB::raw('count(gu.id) as userTotal, g.id, g.status'))
            ->groupBy('g.id')
            ->get();

        return json_decode($gatherData, 1);
    }

    /**获取拼团信息
     * @param int $gid
     * @return mixed
     * @throws LogicException
     */
    public function getGatherInfo (int $gid)
    {
        return Gather::where(['id' => $gid])->first();
    }

    /**更新拼团信息
     * @param int $gid
     * @param int $status
     * @return mixed
     * @throws LogicException
     */
    public function updGather (int $gid, int $status)
    {
        return Gather::where('id', $gid)
                ->update(['status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);
    }

    /**获取拼团用户总数
     * @param int $gid
     * @return mixed
     * @throws LogicException
     */
    public function getGatherUserSum (int $gid)
    {
        return GatherUsers::where(['gid' => $gid])->count();
    }

    /**获取未开启的拼团信息
     * @param int $status
     * @return mixed
     * @throws LogicException
     */
    public function getNoOpen (int $status)
    {
        return Gather::where(['status' => $status])->get(['id', 'created_at']);
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
