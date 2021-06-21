<?php

namespace App\Http\Controllers\API\Test;

use App\Exceptions\LogicException;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
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
}
