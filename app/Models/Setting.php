<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use App\Exceptions\LogicException;

/**
 * App\Models\Setting
 *
 * @property int $id
 * @property string $key
 * @property string $value
 * @property string $msg 参数说明
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Setting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting query()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereMsg($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereValue($value)
 * @mixin \Eloquent
 */
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

    /**判断参数是否存在
     * @param $key
     * @return mixed
     * @throws
     */
    public function isSettings($key)
    {
        $res = Setting::where('key', $key)->exists();
        if (!$res)
        {
            throw new LogicException('该配置参数信息不存在');
        }
    }

    /**获取充值金额
     * @param string $type
     * @return mixed
     * @throws
     */
    public function getSysPrice($type)
    {
        //组装key
        $key = 'sys_price_' . $type;
        $this->isSettings($key);

        //获取数据
        $priceArr = self::getManySetting($key);

        //升序排序返回
        sort($priceArr);
        return $priceArr;
    }
}
