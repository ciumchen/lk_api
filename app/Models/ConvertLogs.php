<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Exceptions\LogicException;
use Illuminate\Support\Facades\DB;

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
        $AssetsLogs->amount = $data['usdtAmount'];
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
        $nowAmount = $assetsLogs->amount_before_change - $assetsLogs->amount;

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
