<?php


namespace App\Http\Controllers\API\Business;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;

class GetBusinessController extends Controller
{

    //获取星级商户列表
    public function getStarBusinessList(){

        $re = Redis::set('key1','1231231');
        var_dump($re);
        var_dump(Redis::get('key1'));

    }




}
