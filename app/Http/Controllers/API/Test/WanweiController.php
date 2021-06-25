<?php

namespace App\Http\Controllers\API\Test;

use App\Exceptions\LogicException;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\UserMsgService;
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
    
    public function test7(Request $request)
    {
        $order_id = $request->input('order_id');
        $Order = Order::find($order_id);
        $UserMsgService = new UserMsgService();
        $str = "{
            \"amount\":100.00,
            \"sys_order_id\":\"202106231518194782BCFB7F89B\",
            \"create_time\":\"2021-06-23 15:18:19\",
            \"open_id\":\"2088202878747694\",
            \"sign\":\"E332F01B705972EBBDC09CCE83479701\",
            \"type\":\"payment.success\",
            \"order_id\":\"PY_20210623151818936352\",
            \"app_id\":\"app_2ac357bae1ce441397\",
            \"pay_time\":\"2021-06-23 15:18:40\"
        }";
        $data = json_decode($str, true);
        $data[ 'order_id' ] = 'PY_20210623104713910854';
        try {
            $UserMsgService->sendWanWeiVideoMsg($order_id, $data, $Order);
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Description:酒店搜索
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return string
     * @throws \Exception
     * @author lidong<947714443@qq.com>
     * @date   2021/6/25 0025
     */
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
        if (empty($page) || empty($limit)) {
            throw new LogicException('页码和每页条数必须');
        }
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
            throw new LogicException($e->getMessage());
        }
        return apiSuccess($res);
    }
    
    /**
     * Description:酒店支持城市
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return string
     * @throws \App\Exceptions\LogicException
     * @author lidong<947714443@qq.com>
     * @date   2021/6/25 0025
     */
    public function hotel2(Request $request)
    {
        $all = $request->all();
        try {
            $HotelOrder = new HotelOrder();
            $res = $HotelOrder->getStandByCity();
        } catch (Exception $e) {
            throw new LogicException($e->getMessage());
        }
        return apiSuccess($res);
    }
    
    /**
     * Description:酒店详情
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return string
     * @throws \Exception
     * @author lidong<947714443@qq.com>
     * @date   2021/6/25 0025
     */
    public function hotel3(Request $request)
    {
        $hotelId = $request->input('hotel_id');
        if (empty($hotelId)) {
            throw new LogicException('酒店ID必须');
        }
        try {
            $HotelOrder = new HotelOrder();
            $res = $HotelOrder->getHotelDetails($hotelId);
        } catch (Exception $e) {
            throw new LogicException($e->getMessage());
        }
        return apiSuccess($res);
    }
    
    /**
     * Description:房间信息查询
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return string
     * @throws \App\Exceptions\LogicException
     * @author lidong<947714443@qq.com>
     * @date   2021/6/25 0025
     */
    public function hotel4(Request $request)
    {
        $hotelId = $request->input('hotel_id');
        $inDate = $request->input('in_date');
        $outDate = $request->input('out_date');
        $excludeOta = $request->input('exclude_ota');
        if (empty($hotelId)) {
            throw new LogicException('酒店ID必须');
        }
        try {
            $HotelOrder = new HotelOrder();
            $res = $HotelOrder->getHotelRooms($hotelId, $inDate, $outDate, $excludeOta);
        } catch (Exception $e) {
            throw new LogicException($e->getMessage());
        }
        return apiSuccess($res);
    }
    
    /**
     * Description:获取房间实时价格
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return string
     * @throws \App\Exceptions\LogicException
     * @author lidong<947714443@qq.com>
     * @date   2021/6/25 0025
     */
    public function hotel5(Request $request)
    {
        $hotelId = $request->input('hotel_id');
        $roomId = $request->input('room_id');
        $numberOfRooms = $request->input('num');
        $inDate = $request->input('in_date');
        $outDate = $request->input('out_date');
        $child = $request->input('child');
        $man = $request->input('man');
        $childAges = $request->input('child_ages');
        try {
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
            throw new LogicException($e->getMessage());
        }
        return apiSuccess($res);
    }
}
