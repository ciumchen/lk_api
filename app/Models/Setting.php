<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    /**
     * @param $key
     * @return mixed
     */
    public static function getSetting($key){

        return Setting::where('key', $key)->value('value');
    }

    /**获取
     * @param $key
     * @return false|string[]
     */
    public static function getManySetting($key){
        $data = self::getSetting($key);

        $data = explode('|', $data);

        return $data;
    }
}
