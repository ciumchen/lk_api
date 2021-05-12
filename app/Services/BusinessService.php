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
            $business_applyDB->id = $business_apply_data->business_apply_id;

            //上传修改图片
            $updateImg = 0;
            if ($request->img!='') {
                $imgUrl1 = OssService::base64Upload($request->img);
                $business_applyDB->img = $imgUrl1;
                $updateImg = 1;
                $imgData['img']=$imgUrl1;
            }
            if ($request->img2!='') {
                $imgUrl2 = OssService::base64Upload($request->img2);
                $business_applyDB->img2 = $imgUrl2;
                $updateImg = 1;
                $imgData['img2']=$imgUrl2;
            }
            if ($request->img_just!='') {
                $imgUrl3 = OssService::base64Upload($request->img_just);
                $business_applyDB->img_just = $imgUrl3;
                $updateImg = 1;
                $imgData['img_just']=$imgUrl3;
            }
            if ($request->img_back!='') {
                $imgUrl4 = OssService::base64Upload($request->img_back);
                $business_applyDB->img_back = $imgUrl4;
                $updateImg = 1;
                $imgData['img_back']=$imgUrl4;
            }
            if ($request->img_hold!='') {
                $imgUrl5 = OssService::base64Upload($request->img_hold);
                $business_applyDB->img_hold = $imgUrl5;
                $updateImg = 1;
                $imgData['img_hold']=$imgUrl5;
            }
            if ($request->img_details1!='') {
                $imgUrl6 = OssService::base64Upload($request->img_details1);
                $business_applyDB->img_details1 = $imgUrl6;
                $updateImg = 1;
                $imgData['img_details1']=$imgUrl6;
            }
            if ($request->img_details2!='') {
                $imgUrl7 = OssService::base64Upload($request->img_details2);
                $business_applyDB->img_details2 = $imgUrl7;
                $updateImg = 1;
                $imgData['img_details2']=$imgUrl7;
            }
            if ($request->img_details3!='') {
                $imgUrl8 = OssService::base64Upload($request->img_details3);
                $business_applyDB->img_details3 = $imgUrl8;
                $updateImg = 1;
                $imgData['img_details3']=$imgUrl8;
            }

            Log::info("oss图片log:",$imgData);
            //修改商家申请表
            if ($updateImg==1){
                $business_applyDB->update($imgData);
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
