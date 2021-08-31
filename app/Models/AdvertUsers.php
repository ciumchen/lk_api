<?php

namespace App\Models;

use App\Exceptions\LogicException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Users;

class AdvertUsers extends Model
{
    use HasFactory;

    protected $table = 'advert_users';

    /**新增用户广告信息
     * @param array $data
     * @return mixed
     * @throws
     */
    public function setUserAdvert (array $data)
    {
        $date = date('Y-m-d H:i:s');

        $advertUsers = new AdvertUsers();
        $advertUsers->uid = $data['uid'];
        $advertUsers->award = $data['award'];
        $advertUsers->package_name = $data['packagename'];
        $advertUsers->type = $data['type'];
        $advertUsers->status = 1;
        $advertUsers->brand = $data['brand'];
        $advertUsers->unique_id = $data['unique_id'];
        $advertUsers->created_at = $date;
        $advertUsers->updated_at = $date;
        $advertUsers->save();

        return $advertUsers;
    }

    /**获取用户广告信息
     * @param array $where
     * @return mixed
     * @throws
     */
    public function getUserAdvert (array $where)
    {
        return AdvertUsers::where($where)->first();
    }

    /**获取用户广告信息
     * @param int $uid
     * @return mixed
     * @throws
     */
    public function getUserAward (int $uid)
    {
        return (new Users())->getUserValue($uid, 'advert_award');
    }

    /**获取用户观看拼团广告次数
     * @param int $uid
     * @return mixed
     * @throws
     */
    public function getGatherAdvert (int $uid)
    {
        return AdvertUsers::where(['uid' => $uid, 'brand' => 2])
                ->count();
    }

    /**格式化输出日期
     * Prepare a date for array / JSON serialization.
     * @param  \DateTimeInterface  $date
     * @return string
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
