<?php

namespace App\Models;

use App\Exceptions\LogicException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * App\Models\Gather
 *
 * @property int $id
 * @property int $type 拼团类型
 * @property int $status 状态：0 开团中；1 开奖中；3 已终止
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property int $scaler 拼团人数
 * @method static \Illuminate\Database\Eloquent\Builder|Gather newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Gather newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Gather query()
 * @method static \Illuminate\Database\Eloquent\Builder|Gather whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Gather whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Gather whereScaler($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Gather whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Gather whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Gather whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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

    /**更新拼团人数
     * @param int $gid
     * @return mixed
     * @throws LogicException
     */
    public function updUsersNum (int $gid)
    {
        Gather::where(['id' => $gid])->increment('scaler');
    }

    /**获取拼团人数
     * @param int $gid
     * @return mixed
     * @throws LogicException
     */
    public function getUsersNum (int $gid)
    {
        try {
            DB::beginTransaction();
            $scaler = Gather::where(['id' => $gid])->lockForUpdate()->value('scaler');
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $scaler;
    }

    /**获取拼团信息
     * @param int $uid
     * @return mixed
     * @throws LogicException
     */
    public function getGatherList (int $uid)
    {
        //开团开始时间
        $startTimeRatio = Setting::getSetting('gather_start_time') ?? 10;
        //开团结束时间
        $endTimeRatio = Setting::getSetting('gather_end_time') ?? 19;
        $nowTiem = time();
        $startTime = strtotime(date("Y-m-d $startTimeRatio:00:00"));
        $endTime = strtotime(date("Y-m-d $endTimeRatio:00:00"));

        if ($startTime >= $nowTiem || $endTime <= $nowTiem)
        {
            return json_encode(['code' => 10000, 'msg' => '暂未开团，敬请期待！']);
        }

        //设置结束拼团时间戳
        $diffTime = 72 * 3600;
        //拼团总人数
        $userRatio = Setting::getSetting('gather_users_ number') ?? 100;
        $data = [
            'diffTime'  => $diffTime,
            'userRatio' => $userRatio,
            'uid'       => $uid,
        ];
        //返回
        $gatherData = DB::table('gather as g')
            ->leftJoin('gather_users as gu', 'g.id', 'gu.gid')
            ->where(['g.status' => 0])
            ->select(DB::raw('count(gu.id) as userTotal, g.id, g.status, g.type, g.created_at'))
            ->groupBy('g.id')
            ->get()
            ->each(function ($item) use ($data){
                //$item->surplusTime = (strtotime($item->created_at) + $diffTime - time()) / 3600;
                $item->surplusTime = intdiv(strtotime($item->created_at) + $data['diffTime'] - time(), 3600);
                $item->userRatio = (int)$data['userRatio'];
                $item->type = self::GATHER_TYPE[$item->type] ?? '';
                $item->userCount = (new GatherUsers())->getUserOneSum($item->id, $data['uid']);
                unset($item->created_at);
            });

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

    const GATHER_TYPE = [
        1 => '100元来客购物卡',
        2 => '美团',
        3 => '油卡',
        4 => '录单',
    ];
}
