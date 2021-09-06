<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * App\Models\UserCityData
 *
 * @property int $id
 * @property int|null $uid users表id
 * @property int|null $province_id city_data表--省份id
 * @property int|null $city_id city_data表--城市id
 * @property int|null $district_id city_data表--区id
 * @property string $address 详细地址
 * @property string $lng 经度
 * @property string $lat 纬度
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|UserCityData newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserCityData newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserCityData query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserCityData whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCityData whereCityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCityData whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCityData whereDistrictId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCityData whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCityData whereLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCityData whereLng($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCityData whereProvinceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCityData whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserCityData whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class UserCityData extends Model
{
    use HasFactory;

    protected $table = 'user_city_data';
    protected $fillable = [
        'uid',
        'province',
        'city',
        'district',
        'address',
        'lng',
        'lat',
        'city_data_id',
        'created_at',
        'updated_at',
    ];
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }


    //添加和更新用户省市区记录
    public function addUserCityData($data){
        $userCityData = UserCityData::where('uid',$data['uid'])->first();
        if (empty($userCityData)){
            $userCityData = new UserCityData();
        }
        $userCityData->uid = $data['uid'];
        $userCityData->province_id = $data['province_id'];
        $userCityData->city_id = $data['city_id'];
        $userCityData->district_id = $data['district_id'];
        $userCityData->address = $data['address'];
        $userCityData->lng = $data['lng'];
        $userCityData->lat = $data['lat'];
        return $userCityData->save();

    }


}
