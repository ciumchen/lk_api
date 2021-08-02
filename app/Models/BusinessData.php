<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * App\Models\BusinessData
 *
 * @property int $id
 * @property int $uid uid
 * @property string|null $banners 商家头图
 * @property string|null $contact_number 联系方式
 * @property string|null $address 商家详细地址
 * @property int|null $province 省
 * @property int|null $city 市
 * @property int|null $district 区
 * @property string|null $lt 经度
 * @property string|null $lg 纬度
 * @property int $category_id 店铺类别
 * @property int $status 1正常，2休息，3已关店,4店铺已被封禁
 * @property string|null $run_time 营业时间
 * @property string|null $content 商家内容介绍
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $name 商店名称
 * @property string $main_business 主营业务
 * @property int $is_recommend 是否推荐，0不推荐，1推荐
 * @property int $sort 排序，数字越大越靠前
 * @property string $limit_price 单日录单限额
 * @property int $state 商户单独设置今日限额开关，默认0，0表示关闭，1表示开启
 * @property int $business_apply_id business_apply表的id
 * @property-read \App\Models\BusinessApply $businessApply
 * @property-read \App\Models\BusinessCategory|null $category
 * @property-read \App\Models\CityData|null $cityLabel
 * @property-read \App\Models\CityData|null $districtLabel
 * @property-read \App\Models\CityData|null $provinceLabel
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessData newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessData newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessData query()
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessData whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessData whereBanners($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessData whereBusinessApplyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessData whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessData whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessData whereContactNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessData whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessData whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessData whereDistrict($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessData whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessData whereIsRecommend($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessData whereLg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessData whereLimitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessData whereLt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessData whereMainBusiness($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessData whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessData whereProvince($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessData whereRunTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessData whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessData whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessData whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessData whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessData whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int|null $is_status 审核状态，1审核中，2审核通过，3审核失败
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessData whereIsStatus($value)
 */
class BusinessData extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'business_data';

    const STATUS_DEFAULT = 1;//正常
    const STATUS_CLOSED = 2;//休息
    const STATUS_DELETED = 3;//已关店
    const STATUS_BANNED = 4;//已封禁


    /**类别
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function category(){

        return $this->hasOne(BusinessCategory::class, 'id', 'category_id');
    }

    /**
     * 省份
     */
    public function provinceLabel()
    {
        return $this->belongsTo(CityData::class, 'province','code');
    }

    /**
     * 城市
     */
    public function cityLabel()
    {
        return $this->belongsTo(CityData::class, 'city','code');
    }

    /**
     * 地区
     */
    public function districtLabel()
    {
        return $this->belongsTo(CityData::class, 'district','code');
    }

    public function businessApply()
    {
        return $this->belongsTo(BusinessApply::class, 'business_apply_id','id');
    }
}
