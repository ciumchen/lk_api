<?php

namespace App\Models;

use App\Exceptions\LogicException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GatherTrade extends Model
{
    use HasFactory;

    protected $table = 'gather_trade';

    /**新增拼团未中奖用户录单记录
     * @param array $data
     * @return mixed
     * @throws LogicException
     */
    public function setGatherTrade (array $data)
    {
        return GatherTrade::insert($data);
    }

    /**更新拼团未中奖用户录单记录
     * @param array $where
     * @param array $data
     * @return mixed
     * @throws LogicException
     */
    public function updGatherTrade (array $where, array $data)
    {
        return GatherTrade::where($where)
                ->update($data);
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
