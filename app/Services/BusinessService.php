<?php

namespace App\Services;

use App\Exceptions\LogicException;
use App\Models\BusinessApply;
use App\Models\BusinessData;
use Exception;
use Illuminate\Support\Facades\Storage;
use PDOException;

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

        $imgUrl = OssService::base64Upload($request->img);

//        return response()->json(['code'=>0, 'msg'=>$imgUrl]);
//        return response()->json(['code'=>0, 'msg'=>$request->img2]);
        $imgUrl = OssService::base64Upload($request->img);
        $imgUrl2 = OssService::base64Upload($request->img2);
        $re = BusinessApply::create([
            'phone' => $request->phone,
            'uid' => $user->id,
            'name' => $request->name,
            'address' => $request->address,
            'work' => $request->work,
            'img' => $imgUrl,
            'img2' => $imgUrl2
        ]);

        return response()->json(['code'=>0, 'msg'=>$re]);


        try{
            $imgUrl = OssService::base64Upload($request->img);
            $imgUrl2 = OssService::base64Upload($request->img2);
            BusinessApply::create([
                'phone' => $request->phone,
                'uid' => $user->id,
                'name' => $request->name,
                'address' => $request->address,
                'work' => $request->work,
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

            if (substr_count($request->banners, env('OSS_URL'))<=0) {

                $imgUrl = OssService::base64Upload($request->banners);
                $businessData->banners = $imgUrl;
            }

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
            if(isset($imgUrl))
                Storage::disk('oss')->delete($imgUrl);
            report($e);
            throw new LogicException('修改失败，请重试');
        } catch (Exception $e) {
            if(isset($imgUrl))
                Storage::disk('oss')->delete($imgUrl);
            throw $e;
        }
    }
}
