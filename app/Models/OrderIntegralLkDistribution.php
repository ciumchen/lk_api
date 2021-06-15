<?php

namespace App\Models;

use App\Http\Controllers\API\Message\UserMsgController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Exceptions\LogicException;

/**
 * App\Models\OrderIntegralLkDistribution
 *
 * @property int $id
 * @property int|null $day 控制录单日期
 * @property int|null $switch 释放开关默认0表示未释放,1表示释放
 * @property string|null $count_lk lk统计
 * @property string|null $count_profit_price 录单累计实际让利金额
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|OrderIntegralLkDistribution newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderIntegralLkDistribution newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderIntegralLkDistribution query()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderIntegralLkDistribution whereCountLk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderIntegralLkDistribution whereCountProfitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderIntegralLkDistribution whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderIntegralLkDistribution whereDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderIntegralLkDistribution whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderIntegralLkDistribution whereSwitch($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderIntegralLkDistribution whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OrderIntegralLkDistribution extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'order_integral_lk_distribution';



}
