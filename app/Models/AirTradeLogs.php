<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Exceptions\LogicException;

/**
 * App\Models\AirTradeLogs
 *
 * @property int $id
 * @property string|null $seat_code 舱位编码
 * @property string|null $passagers 乘客信息
 * @property string|null $item_id 飞机票标准商品编号
 * @property string|null $contact_name 订票联系人
 * @property string|null $contact_tel 联系电话
 * @property string|null $date 出发日期
 * @property string|null $from 起飞站点(机场)三字码
 * @property string|null $to 抵达站点(机场)三字码
 * @property string|null $company_code 航空公司编码
 * @property string|null $flight_no 航班号
 * @property string|null $order_no order 表 -- order_no
 * @property string|null $pay_channel 支付方式
 * @property string|null $price 支付金额
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @method static \Illuminate\Database\Eloquent\Builder|AirTradeLogs newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AirTradeLogs newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AirTradeLogs query()
 * @method static \Illuminate\Database\Eloquent\Builder|AirTradeLogs whereCompanyCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AirTradeLogs whereContactName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AirTradeLogs whereContactTel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AirTradeLogs whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AirTradeLogs whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AirTradeLogs whereFlightNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AirTradeLogs whereFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AirTradeLogs whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AirTradeLogs whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AirTradeLogs whereOrderNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AirTradeLogs wherePassagers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AirTradeLogs wherePayChannel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AirTradeLogs wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AirTradeLogs whereSeatCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AirTradeLogs whereTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AirTradeLogs whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
        dd($data);
        //组装数据
        $airTradeLogs = new AirTradeLogs();
        $airTradeLogs->pay_channel = $data['payChannel'];
        $airTradeLogs->price = $data['price'];
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

        return (new AirTradeLogs())::where('order_no', $orderNo)->get()->first();
    }
}
