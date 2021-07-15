<?php

namespace App\Services\ShowApi;

use App\Exceptions\LogicException;
use Exception;
use Wanwei\Api\HotelOrder;
use Wanwei\Api\RequestBase;

class OrderHotelService
{
    /**
     * Description:
     *
     * @param string $page      页码
     * @param string $limit     每页条数，最大30条
     * @param string $cityName  城市
     * @param string $inDate    入住时间，格式为：YYYY-MM-DD（默认2天后）
     * @param string $outDate   离开时间，格式为：YYYY-MM-DD（默认3天后）
     * @param string $sortKey   排序规则(默认recommend.推荐值排序)
     *                          recommend:推荐值降序
     *                          satisfaction :口碑
     *                          price-asc:起价升序
     *                          price-desc:起价降序
     * @param string $star      星级 【多个以逗号:‘,’分隔】
     *                          TWO:二星级,
     *                          THREE:三星级,
     *                          FOUR:四星级,
     *                          FIVE:五星级,
     *                          BUDGET:经济型,
     *                          CONFORT:舒适型,
     *                          HIGHEND:高档型,
     *                          LUXURY:豪华型
     * @param string $minPrice  房价最低价
     * @param string $maxPrice  房价最高价
     * @param string $poiKey    区域关键字 可以使用关键字搜索中的 displayName（poiKey、poiCode、longitude、latitude四个值需结合使用）
     * @param string $poiCode   经纬度对应的编号 （poiKey、poiCode、longitude、latitude四个值需结合使用）
     *                          poi类型值：
     *                          1-城市，
     *                          2-行政区，
     *                          3-商圈，
     *                          4-景点，
     *                          7-酒店，
     *                          12-机场，
     *                          13-地铁，
     *                          14-火车站
     * @param string $longitude 经度（poiKey、poiCode、longitude、latitude四个值需结合使用））
     * @param string $latitude  维度（poiKey、poiCode、longitude、latitude四个值需结合使用））
     * @param string $keyWords  搜索关键词
     *
     * @return mixed
     * @throws \Exception
     * @author lidong<947714443@qq.com>
     * @date   2021/6/30 0030
     */
    public function getPageList(
        $page,
        $limit,
        $cityName,
        $inDate,
        $outDate,
        $sortKey,
        $star,
        $minPrice,
        $maxPrice,
        $poiKey,
        $poiCode,
        $longitude,
        $latitude,
        $keyWords
    ) {
        try {
            $HotelOrder = new HotelOrder();
            $res = $HotelOrder->hotelSearch(
                $page,
                $limit,
                $cityName,
                $inDate,
                $outDate,
                $sortKey,
                $star,
                $minPrice,
                $maxPrice,
                $poiKey,
                $poiCode,
                $longitude,
                $latitude,
                $keyWords
            );
        } catch (Exception $e) {
            throw $e;
        }
        return $res;
    }
    
    /**
     * Description:获取支持的城市
     *
     * @return mixed
     * @throws \Exception
     * @author lidong<947714443@qq.com>
     * @date   2021/7/1 0001
     */
    public function getStandCity()
    {
        try {
            $HotelOrder = new HotelOrder();
            $res = $HotelOrder->getStandByCity();
        } catch (Exception $e) {
            throw $e;
        }
        return $res;
    }
    
    /**
     * Description:酒店详情[房间列表]
     *
     * @param string $hotelId 酒店id
     *
     * @return mixed
     * @throws \Exception
     * @author lidong<947714443@qq.com>
     * @date   2021/7/1 0001
     */
    public function getHotelRoomList($hotelId)
    {
        try {
            $HotelOrder = new HotelOrder();
            $res = $HotelOrder->getHotelDetails($hotelId);
        } catch (Exception $e) {
            throw $e;
        }
        return $res;
    }
    
    /**
     * Description:查询房间信息
     *
     * @param int         $hotelId    酒店id
     * @param string|null $inDate     入住时间，格式为：YYYY-MM-DD（默认2天后）
     * @param string|null $outDate    离开时间，格式为：YYYY-MM-DD（默认3天后）
     * @param string|null $excludeOta 排除禁止OTA裸售的数据，默认true
     *
     * @return mixed
     * @throws \Exception
     * @author lidong<947714443@qq.com>
     * @date   2021/7/15 0015
     */
    public function searchRoomInfo($hotelId, $inDate = null, $outDate = null, $excludeOta = null)
    {
        try {
            if (empty($hotelId)) {
                throw new LogicException('酒店ID必须');
            }
            $HotelOrder = new HotelOrder();
            $res = $HotelOrder->getHotelRooms($hotelId, $inDate, $outDate, $excludeOta);
        } catch (Exception $e) {
            throw $e;
        }
        return $res;
    }
    
    public function getRoomPriceInfo($hotelId, $roomId, $numberOfRooms, $inDate, $outDate, $child, $man, $childAges)
    {
        try {
            $this->checkGetRoomPrice();
            $HotelOrder = new HotelOrder();
            $res = $HotelOrder->getRoomPrice(
                $hotelId,
                $roomId,
                $numberOfRooms,
                $inDate,
                $outDate,
                $child,
                $man,
                $childAges
            );
        } catch (Exception $e) {
            throw $e;
        }
        return $res;
    }
    
    /** 数据验证 **/
    /**
     * Description:验证查询房间信息参数
     *
     * @throws \Exception
     * @author lidong<947714443@qq.com>
     * @date   2021/7/15 0015
     */
    protected function checkGetRoomPrice($hotelId, $roomId, $numberOfRooms, $inDate, $outDate, $child, $man, $childAges)
    {
        try {
            if (empty($hotelId)) {
                throw new Exception('酒店ID必须');
            }
            if (empty($roomId)) {
                throw new Exception('房型ID必须');
            }
            if (empty($numberOfRooms)) {
                throw new Exception('预订房间数必须');
            }
        } catch (Exception $e) {
            throw $e;
        }
    }
}
