<?php

namespace App\Http\Controllers\API\Order;

use App\Exceptions\LogicException;
use App\Http\Controllers\Controller;
use App\Services\ShowApi\OrderHotelService;
use Exception;
use Illuminate\Http\Request;
use Wanwei\Api\RequestBase;

class HotelController extends Controller
{
    protected $service;
    
    public function __construct()
    {
        $this->service = new OrderHotelService();
    }
    
    public function search(Request $request)
    {
        $page = $request->input('page');
        $limit = $request->input('limit');
        $cityName = $request->input('city_name');
        $inDate = $request->input('in_date');
        $outDate = $request->input('out_date');
        $sortKey = $request->input('sort_key');
        $star = $request->input('star');
        $minPrice = $request->input('min_price');
        $maxPrice = $request->input('max_price');
        $poiKey = $request->input('poi_key');
        $poiCode = $request->input('poi_code');
        $longitude = $request->input('longitude');
        $latitude = $request->input('latitude');
        $keyWords = $request->input('keywords');
        if (empty($page) || empty($limit)) {
            throw new LogicException('页码和每页条数必须');
        }
        try {
            $res = $this->service->getPageList(
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
            throw new LogicException($e->getMessage());
        }
        return apiSuccess($res);
    }
}
