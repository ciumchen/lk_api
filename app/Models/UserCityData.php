<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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
