<?php

namespace App\Services;

use App\Exceptions\LogicException;
use App\Models\BusinessApply;
use App\Models\BusinessData;
use Exception;
use Illuminate\Support\Facades\Storage;
use PDOException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
class BusinessService
{
    /**申请成为商家
     * @param $request
     * @param $user
     * @return bool
     * @throws LogicException
     */
    public static function submitApply($request, $user){
        $imgUrl ='';
        $imgUrl2 ='';
        try{
            $imgUrl = OssService::base64Upload($request->img);
            $imgUrl2 = OssService::base64Upload($request->img2);
            BusinessApply::create([
                'phone' => $request->phone,
                'uid' => $user->id,
                'name' => $request->name,
//                'address' => $request->address,
//                'work' => $request->work,
                'img' => $imgUrl,
                'img2' => $imgUrl2
            ]);
            return true;
        }catch (PDOException $e) {
            Storage::disk('oss')->delete($imgUrl);
            Storage::disk('oss')->delete($imgUrl2);
            report($e);
            throw new LogicException('申请失败，请重试');
        } catch (Exception $e) {
            Storage::disk('oss')->delete($imgUrl);
            Storage::disk('oss')->delete($imgUrl2);
            throw $e;
        }
    }

    /**修改商家信息
     * @param $request
     * @param $user
     * @return bool
     * @throws LogicException
     */
    public static function updateBusiness($request,$user){
        try{

            $businessData = $user->businessData()->first();

            //查询商户申请表信息
            $business_applyDB = new BusinessApply();
            $business_apply_data = $business_applyDB->where('id',$businessData->business_apply_id)->first();

            //照片可以上传为空，为空就不修改图片
            $businessApplyData['id'] = $business_apply_data->id;
            $userIdImgData['uid'] = $business_apply_data->uid;
            $userIdImgData['business_apply_id'] = $business_apply_data->id;

            //上传修改图片
            $updateImg = 0;
            $user_updateImg = 0;
            $reImg['img'] = $request->img;
            $reImg['img2'] = $request->img2;
            $reImg['img_details1'] = $request->img_details1;
            $reImg['img_details2'] = $request->img_details2;
            $reImg['img_details3'] = $request->img_details3;

            $reImg2['img_just'] = $request->img_just;
            $reImg2['img_back'] = $request->img_back;
            $reImg2['img_hold'] = $request->img_hold;

            $imgArrData = array();

            foreach ($reImg as $k=>$v){
                if ($v!='') {
                    $reossimg = OssService::base64Upload($v);
                    $businessApplyData[$k] = $reossimg;
                    $updateImg = 1;
                    $imgArrData[] = $reossimg;
                    Log::info("oss图片申请表log---上传:".$reossimg);
                }
            }
            foreach ($reImg2 as $k=>$v){
                if ($v!='') {
                    $reossimg = OssService::base64Upload($v);
                    $userIdImgData[$k] = $reossimg;
                    $user_updateImg = 1;
                    $imgArrData[] = $reossimg;
                    Log::info("oss图片身份证表log---上传:".$reossimg);
                }
            }

            Log::info("oss图片申请表log:",$businessApplyData);
            Log::info("oss图片身份证表log:",$userIdImgData);
            //修改商家申请表
            if ($updateImg==1){
                $re = DB::table('business_apply')->where('id',$businessApplyData['id'])->update($businessApplyData);
                if ($re){
                    Log::info("oss图片申请表修改成功");
                }else{
                    Log::info("oss图片申请表修改失败");
                }

            }
            Log::info("修改商家信息log:1111111111111111111111111111111================================");
            //修改商家身份证表图片
            if ($user_updateImg==1){
                $res = DB::table('user_id_img')->where('uid',$userIdImgData['uid'])->where('business_apply_id',$userIdImgData['business_apply_id'])->first();
                if ($res){//有记录就更新记录
                    $re = DB::table('user_id_img')->where('id',$res->id)->update($userIdImgData);
                    if ($re){
                        Log::info("oss图片身份证信息表修改成功");
                    }else{
                        Log::info("oss图片身份证信息表修改失败");
                    }
                }else{//没有记录就插入数据
                    $re = DB::table('user_id_img')->insert($userIdImgData);
                    if ($re){
                        Log::info("oss图片身份证信息表插入成功");
                    }else{
                        Log::info("oss图片身份证信息表插入失败");
                    }
                }

            }
            Log::info("修改商家信息log:22222222222222222222222222222222=====================================");
            //修改商家信息表
            $businessData->contact_number = $request->contact_number;
            $businessData->address = $request->address;
            $businessData->category_id = $request->category_id;
            $businessData->province = $request->province;
            $businessData->city = $request->city;
            $businessData->district = $request->district;
            $businessData->status = 1;
            $businessData->name = $request->name;
            $businessData->main_business = $request->main_business;
            $businessData->run_time = $request->start_time.'-'.$request->end_time;
            $businessData->save();
            Log::info("修改商家信息log:33333333333333333333333333333333333=====================================");
            return true;
        }catch (PDOException $e) {
            foreach ($imgArrData as $k=>$v){
                if(isset($v))
                    Storage::disk('oss')->delete($v);
            }
            report($e);
            throw new LogicException('修改失败，请重试');
        } catch (Exception $e) {
            foreach ($imgArrData as $k=>$v){
                if(isset($v))
                    Storage::disk('oss')->delete($v);
            }
            throw $e;
        }
    }
}
