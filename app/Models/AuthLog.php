<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\AuthLog
 *
 * @property int $id
 * @property int $uid 用户id
 * @property string $id_card 身份证号
 * @property string $name 姓名
 * @property string $id_card_img 身份证照片URL
 * @property string $id_card_people_img 手持身份证照片URL
 * @property int $status 1审核中，2审核通过，3审核失败
 * @property string|null $msg 备注
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|AuthLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AuthLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AuthLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|AuthLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuthLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuthLog whereIdCard($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuthLog whereIdCardImg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuthLog whereIdCardPeopleImg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuthLog whereMsg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuthLog whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuthLog whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuthLog whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuthLog whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class AuthLog extends Model
{
    const DEFAULT_STATUS = 1;
    const BY_STATUS = 2;
    const REFUSE_STATUS = 3;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uid',
        'id_card',
        'name',
        'id_card_img',
        'id_card_people_img',
    ];
}
