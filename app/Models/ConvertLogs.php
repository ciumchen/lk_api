<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Exceptions\LogicException;
use Illuminate\Support\Facades\DB;

/**
 * App\Models\ConvertLogs
 *
 * @property int $id
 * @property int $uid 充值用户id
 * @property string $phone 充值手机号/卡号
 * @property string $user_name 充值姓名
 * @property string $price 充值金额
 * @property string $usdt_amount 兑换金额
 * @property string $order_no 充值订单号
 * @property int $oid order 表 id
 * @property int $type 兑换类型：1 话费；2 美团
 * @property int $status 兑换状态：0 待兑换；1 处理中； 2 成功；3 失败
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder|ConvertLogs newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ConvertLogs newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ConvertLogs query()
 * @method static \Illuminate\Database\Eloquent\Builder|ConvertLogs whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConvertLogs whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConvertLogs whereOid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConvertLogs whereOrderNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConvertLogs wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConvertLogs wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConvertLogs whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConvertLogs whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConvertLogs whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConvertLogs whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConvertLogs whereUsdtAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ConvertLogs whereUserName($value)
 * @mixin \Eloquent
 */
class ConvertLogs extends Model
{
    use HasFactory;
    protected $table = 'convert_logs';

    /**usdt 兑换插入数据
    * @param array $data
    * @return mixed
    * @throws
    */
    public function setConvert(array $data)
    {
        $date = date('Y-m-d H:i:s');
        $convertLogs = new ConvertLogs();
        $convertLogs->uid = $data['uid'];
        $convertLogs->phone = $data['phone'];
        $convertLogs->user_name = $data['user_name'];
        $convertLogs->price = $data['price'];
        $convertLogs->usdt_amount = $data['usdtAmount'];
        $convertLogs->order_no = $data['orderNo'];
        $convertLogs->type = $data['type'];
        $convertLogs->status = 1;
        $convertLogs->created_at = $date;
        $convertLogs->updated_at = $date;
        $res = $convertLogs->save();
        if (!$res)
        {
            throw new LogicException('写入兑换记录失败');
        }
    }

    /**插入资产变动记录数据
    * @param array $data
    * @return mixed
    * @throws
    */
    public function setAssetsLogs(array $data)
    {
        $date = date('Y-m-d H:i:s');
        $AssetsLogs = new AssetsLogs();
        $AssetsLogs->assets_type_id = 3;
        $AssetsLogs->assets_name = 'usdt';
        $AssetsLogs->uid = $data['uid'];
        $AssetsLogs->operate_type = 'user_convert';
        $AssetsLogs->amount = '-' . $data['usdtAmount'];
        $AssetsLogs->amount_before_change = $data['atAmount'];
        $AssetsLogs->ip = $data['ip'];
        $AssetsLogs->user_agent = '';
        $AssetsLogs->remark = $data['remark'];
        $AssetsLogs->order_no = $data['orderNo'];
        $AssetsLogs->created_at = $date;
        $AssetsLogs->updated_at = $date;
        $res = $AssetsLogs->save();
        if (!$res)
        {
            throw new LogicException('写入变动记录失败');
        }
    }

    /**插入资产变动记录数据
    * @param array $data
    * @return mixed
    * @throws
    */
    public function updAssets(array $data)
    {
        $assetsLogs = AssetsLogs::where(['order_no' => $data['orderNo']])
                        ->get(['amount', 'amount_before_change'])
                        ->first();
        //更新后的金额
        $nowAmount = $assetsLogs->amount_before_change + $assetsLogs->amount;

        //需要更新信息的用户
        $res = Assets::where(['uid' => $data['uid'], 'assets_type_id' => 3, 'assets_name' => 'usdt'])
                ->update(['amount' => $nowAmount, 'updated_at' => date('Y-m-d H:i:s')]);
        if (!$res)
        {
            throw new LogicException('更新资产金额失败');
        }
    }

    /**更新oid 字段
     * @param string $orderNo
     * @param string $oid
     * @return mixed
     * @throws
     */
    public function updOid(string $orderNo, string $oid)
    {
        $this->isExist($orderNo);
        ConvertLogs::where(['order_no' => $orderNo])
        ->update(['oid' => $oid, 'updated_at' => date('Y-m-d H:i:s')]);
    }

    /**用户兑换记录列表
     * @param array $data
     * @return mixed
     * @throws
     */
    public function getConvertList(array $data)
    {
        //获取数据
        return DB::table($this->table)
                        ->where(['uid' => $data['uid'], 'status' => 2])
                        ->orderBy('usdt_amount', 'desc')
                        ->orderBy('created_at', 'desc')
                        ->forPage($data['page'], $data['perPage'])
                        ->get(['type', 'created_at', 'usdt_amount'])
                        ->each(function ($item) {
                            $item->type = self::CONVERT_TYPE[$item->type];
                        })
                        ->toArray();
    }

    /**查询数据是否存在
     * @param string $orderNo
     * @return mixed
     * @throws
     */
    public function isExist(string $orderNo)
    {
        $res = ConvertLogs::where(['order_no' => $orderNo])->exists();
        if (!$res)
        {
            throw new LogicException('此兑换数据不存在');
        }
    }

    const CONVERT_TYPE = [
        '1' => '兑换话费',
        '2' => '兑换余额（美团）',
    ];
}
