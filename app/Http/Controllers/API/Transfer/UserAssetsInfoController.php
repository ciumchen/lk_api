<?php

namespace App\Http\Controllers\API\Transfer;

use App\Exceptions\LogicException;
use App\Http\Controllers\Controller;
use App\Models\Assets;
use App\Models\Setting;
use Illuminate\Http\Request;

class UserAssetsInfoController extends Controller
{
    public function getUserAssetsInfo(Request $request){

        return response()->json(['code'=>0, 'msg'=>'系统维护中，请等待!']);


        $uid = $request->input('uid');
        $zhbl = Setting::where('key','usdt_iets_subscription_ratio')->value('value');
        if ($zhbl != '' && strstr($zhbl,'|') != false) {
            $bldateArr = explode('|', $zhbl);
            $assData['usdtBl'] = $bldateArr[0];
            $assData['ietsBl'] = $bldateArr[1];
            $assData['zhkg'] = Setting::where('key','set_zh_stite')->value('value');
        }else{
            throw new LogicException('usdt兑换iets的比例参数错误');
        }

        $assre = Assets::where('uid',$uid)->get(['uid','assets_type_id','assets_name','amount']);
        if($assre){
            $assData['data'] = $assre->toArray();
        }
        return response()->json(['code'=>1, 'msg'=>$assData]);
    }
}
