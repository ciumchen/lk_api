<?php

namespace App\Models;

use App\Exceptions\LogicException;
use App\Services\RegionUserService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegionUser extends Model
{
    use HasFactory;

    /**获取用户区域代理信息
     * @param string $uid
     * @return mixed
     * @throws
     */
    public function getNode(string $uid)
    {
        $res = CityNode::where('uid', $uid)->exists();
        if (!$res)
        {
            throw new LogicException('该用户不属于区域代理');
        }

        //返回用户是市级代理还是区级代理
        return (new RegionUserService())->isRegion($uid);
    }

    /**获取市级代理信息
     * @param string $code
     * @param int $page
     * @param int $perPage
     * @return mixed
     * @throws
     */
    public function getCityNode(string $code, int $page, int $perPage)
    {
        $res = BusinessData::where('city', $code)->whereRaw('district is not null')->exists();
        if (!$res)
        {
            throw new LogicException('该市级代理商家不存在');
        }

        //返回
        return (new RegionUserService())->getCityNode($code, $page, $perPage);
    }

    /**获取区级代理信息
     * @param string $code
     * @param int $page
     * @param int $perPage
     * @return mixed
     * @throws
     */
    public function getDistrictNode(string $code, int $page, int $perPage)
    {
        $res = BusinessData::where('district', $code)->exists();
        if (!$res)
        {
            throw new LogicException('该区级代理商家不存在');
        }

        //返回
        return (new RegionUserService())->getDistrictNode($code, $page, $perPage);
    }

    /**获取区级代理积分记录
     * @param string $code
     * @param int $page
     * @param int $perPage
     * @return mixed
     * @throws
     */
    public function getAssets(string $code, int $page, int $perPage)
    {
        $res = CityNode::where('district', $code)->exists();
        if (!$res)
        {
            throw new LogicException('该区级代理不存在');
        }

        //获取区级代理数据
        $districtInfo = CityNode::where('district', $code)->first();

        //获取数据
        $assetsData = (new RegionUserService())->getAssets($districtInfo, $page, $perPage);
        $assetsData['distName'] = $districtInfo->name;

        //返回
        return $assetsData;
    }
}
