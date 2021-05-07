<?php


namespace App\Http\Controllers\API\Business;

use App\Http\Controllers\Controller;
use App\Http\Resources\BusinessDataResources;
use Illuminate\Support\Facades\Redis;
use App\Models\BusinessData;
class GetBusinessController extends Controller
{

    //获取星级商户列表
    public function getStarBusinessList(){

//        $re = Redis::set('key1','1231231');
//        var_dump($re);
//        var_dump(Redis::get('key1'));

        $data = (new BusinessData())->where("status", 1)->orderBy('is_recommend', 'desc')->orderBy('sort', 'desc')->latest('id')->get();


        return response()->json(['code'=>0, 'msg'=>'获取成功', 'data' => $data]);



















    }




}
