<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Exceptions\LogicException;

class PayLogs extends Model
{
    protected $table = 'pay_logs';

    /**交易数据写入
     * @param array $data
     * @return bool
     */
    public function setPay(array $data)
    {
        return DB::table($this->table)->insert($data);
    }

    /**获取机票订单信息
     * @param string $orderNo
     * @return mixed
     * @throws
     */
    public function payInfo(string $orderNo)
    {
        $res = DB::table($this->table)->where('order_no', $orderNo)->exists();
        if (!$res)
        {
            throw new LogicException('订单信息不存在');
        }

        return DB::table($this->table)->where(['order_no' => $orderNo, 'status' => 'succeeded'])->get('order_no');
    }
}
