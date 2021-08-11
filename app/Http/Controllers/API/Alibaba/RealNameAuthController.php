<?php

namespace App\Http\Controllers\API\Alibaba;

use App\Http\Controllers\Controller;
use App\Models\BusinessApply;
use App\Services\Alibaba\AlibabaOcrService;
use Illuminate\Http\Request;

class RealNameAuthController extends Controller
{

    //test测试
    public function AlibabaTest(Request $request)
    {
        $file = $request->input('file');
//        dd($file);
        $redata = (new AlibabaOcrService())->getOCR($file);

        if ($redata){
            return response()->json(['code' => 1, 'msg' => json_decode($redata,true)]);
        }else{
            return response()->json(['code' => 0, 'msg' => '身份证验证失败！']);
        }



//        $uid = $request->input('uid');
//        $status = $request->input('status');
//        $re = BusinessApply::where('uid', $uid)->update(array('status' => $status));
//        if ($re) {
//            echo "修改成功";
//        } else {
//            echo "修改失败";
//        }
    }


}







