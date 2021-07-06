<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
 * App\Models\LkshopOrderLog
 *
 * @method static \Illuminate\Database\Eloquent\Builder|LkshopOrderLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LkshopOrderLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LkshopOrderLog query()
 * @mixin \Eloquent
 * @property int $id
 * @property int|null $order_id 商户订单id
 * @property string|null $type 记录类型，mch_order 商户订单
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|LkshopOrderLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LkshopOrderLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LkshopOrderLog whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LkshopOrderLog whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LkshopOrderLog whereUpdatedAt($value)
 */
class LkshopOrderLog extends Model
{

    protected $table = 'lkshop_order_log';


}
