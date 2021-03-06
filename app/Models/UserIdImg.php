<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
/**
 * App\Models\UserIdImg
 *
 * @property int $id
 * @property int $uid 商户uid
 * @property int $business_apply_id business_apply表的id
 * @property string|null $img_just 身份证正面照
 * @property string|null $img_back 身份证反面照
 * @property string|null $img_hold 身份证手持照
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|UserIdImg newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserIdImg newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserIdImg query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserIdImg whereBusinessApplyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserIdImg whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserIdImg whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserIdImg whereImgBack($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserIdImg whereImgHold($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserIdImg whereImgJust($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserIdImg whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserIdImg whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class UserIdImg extends Model
{
    protected $table = 'user_id_img';
    protected $fillable = [
        'uid',
        'business_apply_id',
        'img_just',
        'img_back',
        'img_hold',
        'created_at',
        'updated_at',
    ];
}
