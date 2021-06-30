<?php

namespace App\Services\ShowApi;

use Exception;
use Wanwei\Api\HotelOrder;

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
}
