<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Exceptions\LogicException;

class AirTradeLogs extends Model
{
    use HasFactory;

    protected $table = 'air_trade_logs';

    /**写入订单数据
    * @param array $data
    * @throws
    */
    public function setAitTrade(array $data)
    {
        $date = date('Y-m-d H:i:s');

        //组装数据
        $airTradeLogs = new AirTradeLogs();
        $airTradeLogs->seat_code = $data['seatCode'];
        $airTradeLogs->passagers = $data['passagers'];
        $airTradeLogs->item_id = $data['itemId'];
        $airTradeLogs->contact_name = $data['contactName'];
        $airTradeLogs->contact_tel = $data['contactTel'];
        $airTradeLogs->date = $data['date'];
        $airTradeLogs->from = $data['from'];
        $airTradeLogs->to = $data['to'];
        $airTradeLogs->company_code = $data['companyCode'];
        $airTradeLogs->flight_no = $data['flightNo'];
        $airTradeLogs->order_no = $data['orderNo'];
        $airTradeLogs->created_at = $date;
        $airTradeLogs->updated_at = $date;
        $airTradeLogs->save();
    }

    /**获取订单数据
    * @param string $orderNo
    * @return mixed
    * @throws
    */
    public function getAirTradeInfo(string $orderNo)
    {
        $res = (new AirTradeLogs())::where('order_no', $orderNo)->exists();
        if (!$res)
        {
            throw new LogicException('订单数据不存在');
        }

        return (new AirTradeLogs())::where('order_no', $orderNo)->get();
    }
}
