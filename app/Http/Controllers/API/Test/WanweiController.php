<?php

namespace App\Http\Controllers\API\Test;

use App\Http\Controllers\Controller;
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
        $VideoCard = new VideoCard();
        $res = $VideoCard->getList();
        echo $res;
        die();
        dd($res);
    }
}
