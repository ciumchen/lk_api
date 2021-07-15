<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\SignIn
 *
 * @property int                             $id
 * @property int                             $uid           用户ID
 * @property int                             $yx_uid        用户在优选商城中的ID
 * @property string                          $sign_date     签到日期格式 YYYY-mm-dd
 * @property int                             $is_add_points 是否已经添加积分
 * @property int                             $total_num     连续登录天数
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|SignIn newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SignIn newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SignIn query()
 * @method static \Illuminate\Database\Eloquent\Builder|SignIn whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SignIn whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SignIn whereIsAddPoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SignIn whereSignDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SignIn whereTotalNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SignIn whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SignIn whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SignIn whereYxUid($value)
 * @mixin \Eloquent
 */
class SignIn extends Model
{
    use HasFactory;
    
    protected $table = 'sign_in';
    
    const IS_ADD_POINTS_DEFAULT = 0; // 字段 is_add_points 默认值
    
    const IS_ADD_POINTS_ADDED   = 1; //字段 is_add_points 已处理
    
    /**
     * Description:
     *
     * @param  int     $uid 用户ID
     * @param  int     $yx_uid
     * @param  string  $sign_date
     * @param  int     $total_num
     *
     * @return $this
     * @throws \Exception
     * @author lidong<947714443@qq.com>
     * @date   2021/7/9 0009
     */
    public function storeSignIn($uid, $yx_uid, $sign_date, $total_num)
    {
        try {
            $this->uid = $uid;
            $this->yx_uid = $yx_uid;
            $this->sign_date = $sign_date;
            $this->is_add_points = self::IS_ADD_POINTS_DEFAULT;
            $this->total_num = $total_num;
            $this->save();
        } catch (\Exception $e) {
            throw $e;
        }
        return $this;
    }
}
