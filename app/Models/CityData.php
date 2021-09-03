<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\CityData
 *
 * @property int $id
 * @property string $name 城市名称
 * @property int $code 城市编码
 * @property int $p_code 上级code
 * @method static \Illuminate\Database\Eloquent\Builder|CityData newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CityData newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CityData query()
 * @method static \Illuminate\Database\Eloquent\Builder|CityData whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CityData whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CityData whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CityData wherePCode($value)
 * @mixin \Eloquent
 * @property int $pid
 * @property int $level
 * @property string $pid_route
 * @method static \Illuminate\Database\Eloquent\Builder|CityData whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CityData wherePid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CityData wherePidRoute($value)
 */
class CityData extends Model
{
    use HasFactory;
    protected $table = 'city_data';
}
