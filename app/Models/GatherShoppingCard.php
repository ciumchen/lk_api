<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Exceptions\LogicException;

/**
 * App\Models\GatherShoppingCard
 *
 * @property int $id
 * @property int $gid gather表 id
 * @property int $uid 用户id
 * @property int $guid gather_users表 id
 * @property string $money 购物卡金额
 * @property int $status 是否允许使用：0 否；1 是
 * @property \Illuminate\Support\Carbon|null $created_at 创建时间
 * @property \Illuminate\Support\Carbon|null $updated_at 更新时间
 * @property int $type 操作类型：1购物卡余额添加，2购物卡余额扣除
 * @property string $name 操作类型名称
 * @property-read \App\Models\UserShoppingCardDhLog|null $gwkDhLog
 * @method static \Illuminate\Database\Eloquent\Builder|GatherShoppingCard newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GatherShoppingCard newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GatherShoppingCard query()
 * @method static \Illuminate\Database\Eloquent\Builder|GatherShoppingCard whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GatherShoppingCard whereGid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GatherShoppingCard whereGuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GatherShoppingCard whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GatherShoppingCard whereMoney($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GatherShoppingCard whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GatherShoppingCard whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GatherShoppingCard whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GatherShoppingCard whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GatherShoppingCard whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class GatherShoppingCard extends Model
{
    use HasFactory;

    protected $table = 'gather_shopping_card';

    protected $fillable = [
        'gid',
        'uid',
        'guid',
        'money',
        'status',
        'type',
        'name',
        'created_at',
        'updated_at',
    ];
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }

    /**新增拼团
     * @param array $data
     * @return mixed
     * @throws LogicException
     */
    public function setGatherShoppingCard (array $data)
    {
        return GatherShoppingCard::insert($data);
    }

    //关联购物卡兑换记录user_shopping_card_dh_log
    public function gwkDhLog(){
        return $this->hasOne(UserShoppingCardDhLog::class, 'gather_shopping_card_id', 'id');
    }


}
