<?php

namespace App\Models;

use App\Exceptions\LogicException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UserShoppingCardDhLog extends Model
{
    use HasFactory;

    protected $table = 'user_shopping_card_dh_log';
    protected $fillable = [
        'uid',
        'operate_type',
        'money',
        'money_before_change',
        'order_no',
        'status',
        'remark',
        'gather_shopping_card_id',
        'created_at',
        'updated_at'
    ];
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }

    //关联购物卡兑换记录user_shopping_card_dh_log
    public function GatherShoppingCard(){
        return $this->belongsTo(GatherShoppingCard::class, 'gather_shopping_card_id', 'id');
    }
}
