<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderMobileRecharge
 *
 * @property int    id
 * @property string mobile
 * @property float  money
 * @property int    order_id
 * @property string order_no
 * @property string created_at
 * @property string updated_at
 * @property string trade_no
 * @property string status
 * @property string pay_status
 * @property string goods_title
 * @property int    uid
 * @package App\Models
 */
class OrderMobileRecharge extends Model
{
    
    use HasFactory;
    
    protected $table = 'order_mobile_recharge';
}
