<?php

namespace App\Services;

use App\Exceptions\LogicException;
use App\Models\BusinessApply;
use App\Models\UserIdImg;
use App\Models\BusinessData;
use Exception;
use Illuminate\Support\Facades\Storage;
use PDOException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class BusinessService
{

    /**申请成为商家
     *
     * @param $request
     * @param $user
     *
     * @return bool
     * @throws LogicException
     */
    public static function submitApply($request, $user)
    {
        $imgUrl = '';
        $imgUrl2 = '';
        try {
            $imgUrl = OssService::base64Upload($request->img);
            $imgUrl2 = OssService::base64Upload($request->img2);
            BusinessApply::create([
                'phone' => $request->phone,
                'uid'   => $user->id,
                'name'  => $request->name,
                //                'address' => $request->address,
                //                'work' => $request->work,
                'img'   => $imgUrl,
                'img2'  => $imgUrl2,
            ]);
            return true;
        } catch (PDOException $e) {
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

    //新申请商家
    public static function newSubmitApply($request, $user)
    {
        try {
            DB::beginTransaction();
            $uid = $user->id;
            //上传修改图片
            $updateImg = 0;
            $user_updateImg = 0;
            $reImg[ 'img' ] = $request->img;
            $reImg[ 'img2' ] = $request->img2;
            $reImg[ 'img_details1' ] = $request->img_details1;
            $reImg[ 'img_details2' ] = $request->img_details2;
            $reImg[ 'img_details3' ] = $request->img_details3;
            $reImg2[ 'img_just' ] = $request->img_just;
            $reImg2[ 'img_back' ] = $request->img_back;
//            $reImg2[ 'img_hold' ] = $request->img_hold;
            $imgArrData = [];
            $businessApplyModel = new BusinessApply();
            $businessDataModel = new BusinessData();
            $userIdImgModel = new UserIdImg();
            foreach ($reImg as $k => $v) {//申请商家
                if ($v != '') {
                    $reossimg = OssService::base64Upload($v);
                    $updateImg = 1;
                    $imgArrData[] = $reossimg;
                    $businessApplyModel->$k = $reossimg;
//                    Log::info("oss图片申请表log---申请表上传:" . $reossimg);
                }
            }
            $userIdImgData = $userIdImgModel->where('uid',$uid)->first();
            if ($userIdImgData == ''){
                $userIdImgData = new UserIdImg();
            }
            foreach ($reImg2 as $k => $v) {//身份证
                if ($v != '') {
                    $reossimg = OssService::base64Upload($v);
                    $userIdImgData->$k = $reossimg;
                    $user_updateImg = 1;
                    $imgArrData[] = $reossimg;
//                    Log::info("oss图片身份证表log---身份证表上传:" . $reossimg);
                }
            }
            //修改商家申请表

            if ($updateImg == 1) {
                $businessApplyModel->phone = $request->contact_number;
                $businessApplyModel->address = $request->address;
                $businessApplyModel->uid = $uid;
                $businessApplyModel->name = $request->name;
                $businessApplyModel->work = $request->main_business;
                $businessApplyModel->save();

                //插入商家信息
                $userBusinessData = $businessDataModel->where('uid',$uid)->first();
                if ($userBusinessData == ''){
                    $userBusinessData = new BusinessData();
                }
                $userBusinessData->contact_number = $request->contact_number;
                $userBusinessData->address = $request->address;
                $userBusinessData->uid = $uid;
                $userBusinessData->status = 2;
                $userBusinessData->name = $request->name;
                $userBusinessData->main_business = $request->main_business;
                $userBusinessData->province = $request->province;
                $userBusinessData->city = $request->city;
                $userBusinessData->district = $request->district;
                $userBusinessData->category_id = $request->category_id;
                $userBusinessData->run_time = $request->start_time.'-'.$request->end_time;
                $userBusinessData->business_apply_id = $businessApplyModel->id;
                $userBusinessData->save();

            }
            //修改商家身份证表图片
            if ($user_updateImg == 1) {
                $userIdImgData->uid = $uid;
                $userIdImgData->business_apply_id = $businessApplyModel->id;
                $userIdImgData->save();
            }


            DB::commit();
            return true;
            } catch (PDOException $e) {
                foreach ($imgArrData as $k => $v) {
                    if (isset($v))
                        Storage::disk('oss')->delete($v);
                }
            DB::rollBack();
                report($e);
                throw new LogicException('申请商家失败，请重试');
            } catch (Exception $e) {
                foreach ($imgArrData as $k => $v) {
                    if (isset($v))
                        Storage::disk('oss')->delete($v);
                }
            DB::rollBack();
                throw $e;
            }

    }

    /**修改商家信息
     *
     * @param $request
     * @param $user
     *
     * @return bool
     * @throws LogicException
     */
    public static function updateBusiness($request, $user)
    {
        try {
            $businessData = $user->businessData()->first();
            //查询商户申请表信息
            $business_applyDB = new BusinessApply();
            $business_apply_data = $business_applyDB->where('id', $businessData->business_apply_id)->first();
            //照片可以上传为空，为空就不修改图片
            $businessApplyData[ 'id' ] = $business_apply_data->id;
            $userIdImgData[ 'uid' ] = $business_apply_data->uid;
            $userIdImgData[ 'business_apply_id' ] = $business_apply_data->id;
            //上传修改图片
            $updateImg = 0;
            $user_updateImg = 0;
            $reImg[ 'img' ] = $request->img;
            $reImg[ 'img2' ] = $request->img2;
            $reImg[ 'img_details1' ] = $request->img_details1;
            $reImg[ 'img_details2' ] = $request->img_details2;
            $reImg[ 'img_details3' ] = $request->img_details3;
            $reImg2[ 'img_just' ] = $request->img_just;
            $reImg2[ 'img_back' ] = $request->img_back;
            $reImg2[ 'img_hold' ] = $request->img_hold;
            $imgArrData = [];
            foreach ($reImg as $k => $v) {
                if ($v != '') {
                    $reossimg = OssService::base64Upload($v);
                    $businessApplyData[ $k ] = $reossimg;
                    $updateImg = 1;
                    $imgArrData[] = $reossimg;
                    Log::info("oss图片申请表log---申请表上传:" . $reossimg);
                }
            }
            foreach ($reImg2 as $k => $v) {
                if ($v != '') {
                    $reossimg = OssService::base64Upload($v);
                    $userIdImgData[ $k ] = $reossimg;
                    $user_updateImg = 1;
                    $imgArrData[] = $reossimg;
                    Log::info("oss图片身份证表log---身份证表上传:" . $reossimg);
                }
            }
            Log::info("oss图片申请表log:", $businessApplyData);
            Log::info("oss图片身份证表log:", $userIdImgData);
            //修改商家申请表
            if ($updateImg == 1) {
                $BusinessApply = new BusinessApply();
                foreach ($businessApplyData as $k => $v) {
//                    $BusinessApply->$k = $v;
                    $businessApplyData[ $k ] = $v;
                }
//                $BusinessApply->id = $business_apply_data->id;
//                $re = $BusinessApply->save();
                $re = DB::table('business_apply')->where('id', $business_apply_data->id)->update($businessApplyData);
                if ($re) {
                    Log::info("oss图片申请表修改成功");
                } else {
                    Log::info("oss图片申请表修改失败");
                }
            }
            //修改商家身份证表图片
            if ($user_updateImg == 1) {
                $res = DB::table('user_id_img')
                         ->where('uid', $userIdImgData[ 'uid' ])
                         ->where('business_apply_id', $userIdImgData[ 'business_apply_id' ])
                         ->first();
                if ($res) {
                    $userIdImgData[ 'id' ] = $res->id;
                }
                $UserIdImg = new UserIdImg();
                foreach ($userIdImgData as $k => $v) {
                    $UserIdImg->$k = $v;
                }
                $re = $UserIdImg->save();
//                if ($res){//有记录就更新记录
//                    $re = DB::table('user_id_img')->where('id',$res->id)->update($userIdImgData);
//                    if ($re){
//                        Log::info("oss图片身份证信息表修改成功");
//                    }else{
//                        Log::info("oss图片身份证信息表修改失败");
//                    }
//                }else{//没有记录就插入数据
//                    $re = DB::table('user_id_img')->insert($userIdImgData);
//                    if ($re){
//                        Log::info("oss图片身份证信息表插入成功");
//                    }else{
//                        Log::info("oss图片身份证信息表插入失败");
//                    }
//                }
            }
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
            $businessData->run_time = $request->start_time . '-' . $request->end_time;
            $businessData->save();
            return true;
        } catch (PDOException $e) {
            foreach ($imgArrData as $k => $v) {
                if (isset($v))
                    Storage::disk('oss')->delete($v);
            }
            report($e);
            throw new LogicException('修改失败，请重试');
        } catch (Exception $e) {
            foreach ($imgArrData as $k => $v) {
                if (isset($v))
                    Storage::disk('oss')->delete($v);
            }
            throw $e;
        }
    }
}
