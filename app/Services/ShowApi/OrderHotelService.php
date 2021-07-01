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
     * @param  string  $page
     * @param  string  $limit
     * @param  string  $cityName
     * @param  string  $inDate
     * @param  string  $outDate
     * @param  string  $sortKey
     * @param  string  $star
     * @param  string  $minPrice
     * @param  string  $maxPrice
     * @param  string  $poiKey
     * @param  string  $poiCode
     * @param  string  $longitude
     * @param  string  $latitude
     * @param  string  $keyWords
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
     * Description:
     *
     * @param  string  $hotelId
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
}
