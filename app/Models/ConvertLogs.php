<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        $convertLogs->name = $data['name'];
        $convertLogs->price = $data['price'];
        $convertLogs->usdt_amount = $data['usdtAmount'];
        $convertLogs->type = 1;
        $convertLogs->status = 0;
        $convertLogs->created_at = $date;
        $convertLogs->updated_at = $date;
        $convertLogs->save();
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
        $AssetsLogs->amount_before_change = $data['amount'];
        $AssetsLogs->tx_hash = '';
        $AssetsLogs->ip = $data['ip'];
        $AssetsLogs->user_agent = '';
        $AssetsLogs->remark = $data['remark'];
        $AssetsLogs->created_at = $date;
        $AssetsLogs->updated_at = $date;
        $AssetsLogs->save();
    }
}
