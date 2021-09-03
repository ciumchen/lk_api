<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * App\Models\RealNameAuth
 *
 * @method static \Illuminate\Database\Eloquent\Builder|RealNameAuth newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RealNameAuth newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RealNameAuth query()
 * @mixin \Eloquent
 * @property int $id
 * @property int|null $uid users表 -- id
 * @property string $name 姓名
 * @property string $num_id 身份证号
 * @property int $status 审核状态：0未审核，1审核通过，2审核不通过
 * @property string|null $img_just 身份证正面
 * @property string|null $img_back 身份证反面
 * @property string|null $remark 备注
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|RealNameAuth whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RealNameAuth whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RealNameAuth whereImgBack($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RealNameAuth whereImgJust($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RealNameAuth whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RealNameAuth whereNumId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RealNameAuth whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RealNameAuth whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RealNameAuth whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RealNameAuth whereUpdatedAt($value)
 */
class RealNameAuth extends Model
{
    use HasFactory;

    protected $table = 'real_name_auth';
    protected $fillable = [
        'uid',
        'name',
        'num_id',
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
