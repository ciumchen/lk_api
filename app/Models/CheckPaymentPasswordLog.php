<?php

namespace App\Models;

use App\Exceptions\LogicException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * App\Models\CheckPaymentPasswordLog
 *
 * @property int $id
 * @property int|null $uid uid
 * @property int $time 校验时间
 * @property int $num 次数
 * @property string|null $remark 备注
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|CheckPaymentPasswordLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CheckPaymentPasswordLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CheckPaymentPasswordLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|CheckPaymentPasswordLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CheckPaymentPasswordLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CheckPaymentPasswordLog whereNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CheckPaymentPasswordLog whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CheckPaymentPasswordLog whereTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CheckPaymentPasswordLog whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CheckPaymentPasswordLog whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CheckPaymentPasswordLog extends Model
{
    use HasFactory;

    protected $table = 'check_payment_password_log';
    protected $fillable = [
        'uid',
        'time',
        'num',
        'remark',
        'created_at',
        'updated_at',
    ];
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }

}
