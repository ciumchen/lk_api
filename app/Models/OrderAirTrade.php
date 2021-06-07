<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Exceptions\LogicException;

class OrderAirTrade extends Model
{
    use HasFactory;

    protected $table = 'order_air_trade';

    /**新增机票订单数据
    * @param array $data
    * @throws
    */
    public function setAirTrade(array $data)
    {
        DB::table($this->table)->insert($data);
    }

    /**更新机票订单数据
    * @param string $tradeNo
    * @param array $data
    * @throws
    */
    public function updAirTrade(string $tradeNo, array $data)
    {
        $tradeData = (new OrderAirTrade())::where('trade_no', $tradeNo)->exists();
        if (!$tradeData)
        {
            throw new LogicException('机票订单信息不存在');
        }

        (new OrderAirTrade())::where('trade_no', $tradeNo)->whereIn('order_no', $data)->update(['state' => 9, 'order_state' => 10, 'utime' => date('Y-m-d H:i:s')]);
    }
}
