<?php

namespace App\Services;

use App\Exceptions\LogicException;
use App\Models\BusinessApply;
use App\Models\BusinessData;
use Exception;
use Illuminate\Support\Facades\Storage;
use PDOException;
use Illuminate\Support\Facades\Log;
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
            $businessApplyData['id'] = $business_apply_data->business_apply_id;
            $userIdImgData['uid'] = $business_apply_data->uid;
            $userIdImgData['business_apply_id'] = $business_apply_data->business_apply_id;

            //上传修改图片
            $updateImg = 0;
            $user_updateImg = 0;
            if ($request->img!='') {
                $imgUrl1 = OssService::base64Upload($request->img);
                $businessApplyData['img'] = $imgUrl1;
                $updateImg = 1;
            }
            if ($request->img2!='') {
                $imgUrl2 = OssService::base64Upload($request->img2);
                $businessApplyData['img2'] = $imgUrl2;
                $updateImg = 1;
            }


            if ($request->img_just!='') {
                $imgUrl3 = OssService::base64Upload($request->img_just);
                $userIdImgData['img_just'] = $imgUrl3;
                $user_updateImg = 1;
            }
            if ($request->img_back!='') {
                $imgUrl4 = OssService::base64Upload($request->img_back);
                $userIdImgData['img_back'] = $imgUrl4;
                $user_updateImg = 1;
            }
            if ($request->img_hold!='') {
                $imgUrl5 = OssService::base64Upload($request->img_hold);
                $userIdImgData['img_hold'] = $imgUrl5;
                $user_updateImg = 1;
            }


            if ($request->img_details1!='') {
                $imgUrl6 = OssService::base64Upload($request->img_details1);
                $businessApplyData['img_details1'] = $imgUrl6;
                $updateImg = 1;
            }
            if ($request->img_details2!='') {
                $imgUrl7 = OssService::base64Upload($request->img_details2);
                $businessApplyData['img_details2'] = $imgUrl7;
                $updateImg = 1;
            }
            if ($request->img_details3!='') {
                $imgUrl8 = OssService::base64Upload($request->img_details3);
                $businessApplyData['img_details3'] = $imgUrl8;
                $updateImg = 1;
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
            return true;
        }catch (PDOException $e) {
            if(isset($imgUrl1))
                Storage::disk('oss')->delete($imgUrl1);
            if(isset($imgUrl2))
                Storage::disk('oss')->delete($imgUrl2);
            if(isset($imgUrl3))
                Storage::disk('oss')->delete($imgUrl3);
            if(isset($imgUrl4))
                Storage::disk('oss')->delete($imgUrl4);
            if(isset($imgUrl5))
                Storage::disk('oss')->delete($imgUrl5);
            if(isset($imgUrl6))
                Storage::disk('oss')->delete($imgUrl6);
            if(isset($imgUrl7))
                Storage::disk('oss')->delete($imgUrl7);
            if(isset($imgUrl8))
                Storage::disk('oss')->delete($imgUrl8);
            report($e);
            throw new LogicException('修改失败，请重试');
        } catch (Exception $e) {
            if(isset($imgUrl1))
                Storage::disk('oss')->delete($imgUrl1);
            if(isset($imgUrl2))
                Storage::disk('oss')->delete($imgUrl2);
            if(isset($imgUrl3))
                Storage::disk('oss')->delete($imgUrl3);
            if(isset($imgUrl4))
                Storage::disk('oss')->delete($imgUrl4);
            if(isset($imgUrl5))
                Storage::disk('oss')->delete($imgUrl5);
            if(isset($imgUrl6))
                Storage::disk('oss')->delete($imgUrl6);
            if(isset($imgUrl7))
                Storage::disk('oss')->delete($imgUrl7);
            if(isset($imgUrl8))
                Storage::disk('oss')->delete($imgUrl8);
            throw $e;
        }
    }
}
