<?php

namespace App\Http\Controllers\API\Alibaba;

use App\Exceptions\LogicException;
use App\Http\Controllers\Controller;
use App\Http\Requests\RealNameAuthRequest;
use App\Models\BusinessApply;
use App\Models\RealNameAuth;
use App\Models\RealNameAuthLog;
use App\Models\User;
use App\Models\Users;
use App\Models\UserUpdatePhoneLogSd;
use App\Services\Alibaba\AlibabaOcrService;
use App\Services\OssService;
use Illuminate\Http\Request;
use DB;
use Exception;
class RealNameAuthController extends Controller
{

    //用户身份证ocr验证
    public function AlibabaOcrCheckImg(RealNameAuthRequest $request)
    {
        $uid = $request->input('uid');
        $img_just = $request->input('img_just');//正面身份证
        $img_back = $request->input('img_back');//反面身份证 img_back
        $username = $request->input('username');
        $user_num = $request->input('user_num');

        $userInfo = Users::find($uid);
        if ($userInfo==''){
            return response()->json(['code' => 0, 'msg' => '用户不存在！']);
        }

        //验证用户今日ocr认证次数
        $today = date('Ymd',time());
        $userLog = RealNameAuthLog::where('uid',$uid)->where('day',$today)->first();
        if ($userLog==null){
            $data = array('uid'=>$uid,"day"=>$today,"second"=>1);
            RealNameAuthLog::create($data);

        }elseif ($userLog!=null && $userLog->second <=3){
            $userLog->second = $userLog->second+1;
            $userLog->save();

        }elseif ($userLog->second >= 3){
            return response()->json(['code' => 0, 'msg' => '每天只能认证3次！']);
        }


        //上传图片到oss
        $img_just_url = OssService::base64Upload($img_just,'ocr/');
        $img_back_url = OssService::base64Upload($img_back,'ocr/');

        $ossImgUrl = env('OSS_URL').$img_just_url;
//        var_dump($ossImgUrl);
        //身份证ocr验证
        $redata = (new AlibabaOcrService())->getOCR($ossImgUrl);
        if ($redata){
            $reArr = json_decode($redata,true);
            if ($reArr['success']){
                if ($username!=$reArr['name']){
                    return response()->json(['code' => 0, 'msg' => '名字和身份证不一致！']);
                }
                if ($user_num!=$reArr['num']){
                    return response()->json(['code' => 0, 'msg' => '身份证号码和身份证不一致！']);
                }

                $age =  date('Y') - substr($reArr['num'], 6, 4) + (date('md') >= substr($reArr['num'], 10, 4) ? 1 : 0);
                if ($age>=16){
                    //保存用户信息
                    DB::beginTransaction();
                    try {
                        $userInfo->real_name = $reArr['name'];
                        $userInfo->is_auth = 2;
                        $userInfo->save();
                        $userImg = RealNameAuth::where('uid',$uid)->first();
                        if ($userImg==null){
                            $data = array('uid'=>$uid,'name'=>$reArr['name'],"num_id"=>$reArr['num'],'img_just'=>$img_just_url,'img_back'=>$img_back_url,'status'=>1);
                            RealNameAuth::create($data);
                        }else{
                            $userImg->name = $reArr['name'];
                            $userImg->num_id = $reArr['num'];
                            $userImg->img_just = $img_just_url;
                            $userImg->img_back = $img_back_url;
                            $userImg->status = 1;
                            $userImg->save();
                        }
                        DB::commit();
                    } catch (Exception $exception) {
                        DB::rollBack();
                        return response()->json(['code' => 0, 'msg' => '身份证验证失败！']);
                    }
                    return response()->json(['code' => 1, 'msg' => '身份证验证成功！']);

                }else{
                    return response()->json(['code' => 0, 'msg' => '身份证验证未满16周岁！']);
                }

            }else{
                return response()->json(['code' => 0, 'msg' => '身份证验证失败！']);
            }

        }else{
            return response()->json(['code' => 0, 'msg' => '身份证验证失败！']);
        }


    }


}







