<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Exceptions\LogicException;

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
        $convertLogs->status = 0;
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
        $AssetsLogs->assets_name = 'convert';
        $AssetsLogs->uid = $data['uid'];
        $AssetsLogs->operate_type = 'user_convert';
        $AssetsLogs->amount = $data['usdtAmount'];
        $AssetsLogs->amount_before_change = $data['atAmount'];
        //$AssetsLogs->tx_hash = '';
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
        //更新后的金额
        $nowAmount = $data['atAmount'] - $data['usdtAmount'];

        //需要更新信息的用户
        $res = Assets::where(['uid' => $data['uid'], 'assets_type_id' => 3, 'assets_name' => 'usdt'])
                ->update(['amount' => $nowAmount, 'updated_at' => date('Y-m-d H:i:s')]);
        if (!$res)
        {
            throw new LogicException('更新资产金额失败');
        }
    }
}
