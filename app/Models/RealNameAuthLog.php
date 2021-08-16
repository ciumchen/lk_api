<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * App\Models\RealNameAuthLog
 *
 * @property int $id
 * @property int|null $uid users表 -- id
 * @property int|null $day 修改日期
 * @property int|null $second 次数
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|RealNameAuthLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RealNameAuthLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RealNameAuthLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|RealNameAuthLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RealNameAuthLog whereDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RealNameAuthLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RealNameAuthLog whereSecond($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RealNameAuthLog whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RealNameAuthLog whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class RealNameAuthLog extends Model
{
    use HasFactory;

    protected $table = 'real_name_auth_log';
    protected $fillable = [
        'uid',
        'day',
        'second',
        'status',
        'img_just',
        'img_back',
        'remark',
        'created_at',
        'updated_at',
    ];
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
}
