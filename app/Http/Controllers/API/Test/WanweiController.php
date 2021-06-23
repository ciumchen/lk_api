<?php

namespace App\Http\Controllers\API\Test;

use App\Exceptions\LogicException;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Exception;
use Illuminate\Http\Request;
use Wanwei\Api\HotelOrder;
use Wanwei\Api\RequestBase;
use Wanwei\Api\VideoCard;
use Wanwei\Http\ShowapiRequest;

class WanweiController extends Controller
{
    
    //
    public function test()
    {
        $url = "https://route.showapi.com/xxxx-xxxxx";//调用URL,根据情况改变此值
        $showapi_appid = "xxxxxxx";
        $showapi_sign = "xxxxxxxxxxx";
        $req = new ShowapiRequest($url, $showapi_appid, $showapi_sign);
        dd($req);
    }
    
    public function test2(Request $request)
    {
        $RequestBase = new RequestBase();
        $res = $RequestBase->getShowApi('');
        dd($res);
    }
    
    public function test3(Request $request)
    {
        try {
            $VideoCard = new VideoCard();
            $res = $VideoCard->getList();
        } catch (Exception $e) {
            throw new LogicException($e->getMessage());
        }
        return apiSuccess($res);
    }
    
    public function test4(Request $request)
    {
        $genusId = $request->input('genus_id');
        $order_no = $request->input('order_no');
        try {
            $VideoCard = new VideoCard();
            $res = $VideoCard->getVideoCard($genusId, $order_no);
        } catch (Exception $e) {
            throw new LogicException($e->getMessage());
        }
        return apiSuccess($res);
    }
    
    public function test5(Request $request)
    {
        $order_no = $request->input('order_no');
        try {
            $VideoCard = new VideoCard();
            $res = $VideoCard->getVideoOrder($order_no);
        } catch (Exception $e) {
            throw new LogicException($e->getMessage());
        }
        return apiSuccess($res);
    }
    
    public function hotel1(Request $request)
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
            throw new Exception($e->getMessage());
        }
        return apiSuccess($res);
    }
    
    public function test6(Request $request)
    {
        $order_id = $request->input('order_id');
        $OrderSSS = Order::find($order_id);
        
        $OrderService = new OrderService();
        $des = $OrderService->getDescription($order_id, $OrderSSS);
        dump($des);
        switch ($des) {
            case 'HF':
                $info = $OrderSSS->trade->toArray();
                break;
            case 'VC':
                $info = $OrderSSS->video->toArray();
                break;
            default:
                $info = [];
        }
        dump($info);
    }
}
