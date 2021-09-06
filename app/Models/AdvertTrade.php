<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvertTrade extends Model
{
    use HasFactory;

    protected $table = 'advert_trade';

    /**新增广告录单订单
     * @param array $data
     * @return $this
     * @throws \Exception
     */
    public function setAdvertTrade(array $data)
    {
        try {
            $this->uid = $data['uid'];
            $this->oid = $data['oid'];
            $this->business_uid = $data['business_uid'];
            $this->profit_ratio = $data['profit_ratio'];
            $this->price = $data['price'];
            $this->profit_price = $data['profit_price'];
            $this->status = $data['status'];
            $this->order_no = $data['order_no'];
            $this->save();
        } catch (Exception $e) {
            throw $e;
        }
        return $this;
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
