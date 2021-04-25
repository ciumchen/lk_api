<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

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

}
