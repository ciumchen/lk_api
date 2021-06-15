<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Ad
 *
 * @property int $id
 * @property string $name 广告名称
 * @property int $position 位置
 * @property string|null $img_url 图片
 * @property int $status 状态 1显示 2不显示
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Ad newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ad newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ad query()
 * @method static \Illuminate\Database\Eloquent\Builder|Ad whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ad whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ad whereImgUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ad whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ad wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ad whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ad whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Ad extends Model
{
    use HasFactory;
    protected $table = 'ad';

    const SHOW = 1;//显示
    const HIDE = 2;//隐藏
    const INDEX = 1;//首页
    const BUSINESS = 2;//商家页

    /**
     * 是否显示
     * @var string[]
     */
    public static $statusLabel = [
        self::SHOW => '显示',
        self::HIDE => '隐藏',
    ];

    /**
     * 位置
     * @var string[]
     */
    public static $positionLabel = [
        self::INDEX => '首页广告',
        self::BUSINESS => '商家列表页广告',
    ];
}
