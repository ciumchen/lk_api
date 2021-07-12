<?php

namespace App\Http\Controllers\API\Business;

use App\Exceptions\LogicException;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Http\Resources\AdResources;
use App\Http\Resources\BusinessCategoryResources;
use App\Http\Resources\BusinessDataResources;
use App\Http\Requests\UpdateBusinessDataRequest;
use App\Http\Resources\OrdersResources;
use App\Http\Resources\UserResources;
use App\Models\Ad;
use App\Models\BusinessApply;
use App\Models\BusinessCategory;
use App\Models\BusinessData;
use App\Models\CityData;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Services\BusinessService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use PDOException;
use Illuminate\Pagination\Paginator;

class BusinessController extends Controller
{
    /**录单
     * @param OrderRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws LogicException
     */
    public function __invoke(OrderRequest $request){

        $user = $request->user();

        if($user->id == $request->uid)
            throw new LogicException('商家自己不能给自己录单');
        //检测用户状态
        $user->checkStatus();

        if($user->role != User::ROLE_BUSINESS)
            throw new LogicException('非商家无法录单，请联系在店消费商家');

        $buyUser = User::where('id', $request->uid)->first();

        //检测买家账户是否异常
        $buyUser->checkStatus();

        try{
            Order::create([
                'uid' => $request->uid,
                'business_uid' => $user->id,
                'name' => $request->name,
                'profit_ratio' => $request->ratio,
                'price' => $request->price,
                'profit_price' => bcmul($request->price, bcdiv($request->ratio, 100, 4), 2),
            ]);
        }catch (PDOException $e) {
            report($e);
            throw new LogicException('录单失败，请联系客服');
        } catch (Exception $e) {
            throw $e;
        }

        return response()->json(['code'=>0, 'msg'=>'录单成功，请尽快缴纳让利金额，等待审核']);

    }

    /**获取我的申请
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getApplyBusiness(Request $request){


       $data = BusinessApply::whereUid($request->user()->id)->where('status', BusinessApply::DEFAULT_STATUS)->first();
       if($data){
           $data->img = env('OSS_URL').$data->img;
           $data->status = BusinessApply::$typeLabels[$data->status];
       }
       return response()->json(['code'=>0, 'data'=>$data]);

    }

    /**商家中心数据
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBusiness(Request $request){

        $user = $request->user();
        if($user->role != User::ROLE_BUSINESS)
            throw new LogicException('非法访问');

        $data['user'] = new UserResources($user);
        $data['total'] = $user->businessOrder()->where('status', Order::STATUS_SUCCEED)->sum('price');
        $data['profit_price'] = $user->businessOrder()->where('status', Order::STATUS_SUCCEED)->sum('profit_price');
        $data['order_num'] = $user->businessOrder()->where('status', Order::STATUS_SUCCEED)->count();
        $data['today_order_num'] = $user->businessOrder()->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])->where('status', Order::STATUS_SUCCEED)->count();
        $data['today_total'] = $user->businessOrder()->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])->where('status', Order::STATUS_SUCCEED)->sum('price');
        $data['order_list'] = OrdersResources::collection($user->businessOrder()->offset(0)->limit(5)->orderBy('status', 'asc')->orderByDesc('id')->get());

        $welfare = Setting::getSetting('welfare')??4;//公益分配比例
        $data['welfare'] = format_decimal(bcmul($data['profit_price'], bcdiv($welfare, 100, 8), 8));//公益捐赠
        $data['business_data'] = $user->businessData()->first();
        $data['ratio'] = $ratio = Setting::getManySetting('business_rebate_scale');

        //今日让利累计
        $data['todayRatioData'] = Cache::remember("ratio_data_uid_" . $user->id,1800, function () use ($ratio,$user){

            $data = [];
            foreach($ratio as $v){
                $data[] = $user->businessOrder()->where('profit_ratio', $v)->where('status', Order::STATUS_SUCCEED)->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])->sum('profit_price')??0;

            }
            return $data;

        });
        //昨日各比例让利累计
        $data['yesterdayRatioData'] = Cache::remember("yesterday_ratio_data_uid_" . $user->id,1800, function () use ($ratio, $user){

            $data = [];
            foreach($ratio as $v){
                $data[] = $user->businessOrder()->where('profit_ratio', $v)->where('status', Order::STATUS_SUCCEED)->whereBetween('created_at', [now()->yesterday()->startOfDay(), now()->yesterday()->endOfDay()])->sum('profit_price')??0;

            }
            return $data;

        });

        return response()->json(['code'=>0, 'data'=>$data]);
    }

    /**
     * 获取广告
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAd(Request $request)
    {
        $type = $request->input('position', Ad::INDEX);
        $data = Ad::where("position", $type)->first();
        return response()->json(['code' => 0, 'data' => AdResources::make($data)]);
    }

    /**
     * 获取首页商家分类
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBusinessCategory()
    {
        $category = BusinessCategory::orderBy("sort","desc")->get();
        return response()->json(['code' => 0, 'data' => BusinessCategoryResources::collection($category)]);
    }

    /**
     * 获取商家列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBusinessList(Request $request)
    {
        $pageSize = $request->input('pageSize',10);
        $category = $request->input("category");
        $keyword = $request->input('keyword');
        $city = $request->input('city');
        $district = $request->input('district');

        $data = (new BusinessData())
            ->when($category,function($query) use ($category) {
                return $query->where('category_id', $category);
            })
            ->when($keyword,function($query) use ($keyword) {
                return $query->where('name', 'like', "%" . $keyword . "%");
            })
            ->when($city,function($query) use ($city) {
                $cityId = CityData::where("name", $city)->value("code");

                if($cityId)
                {
                    $query->where('city', $cityId);
                }
            })
            ->when($district,function($query) use ($city, $district) {
                $cityId = CityData::where("name", $city)->value("code");
                if($cityId)
                {
                    $districtId = CityData::where("name", $district)
                        ->where("p_code", $cityId)
                        ->value("code");
                    $query->where('district', $districtId);
                }
            })
            ->where("status", BusinessData::STATUS_DEFAULT)
            ->orderBy('is_recommend', 'desc')
            ->orderBy('sort', 'desc')
            ->latest('id')
            ->forPage(Paginator::resolveCurrentPage('page'), $pageSize)
            ->get();


        return response()->json(['code'=>0, 'msg'=>'获取成功', 'data' => BusinessDataResources::collection($data)]);
    }

    /**
     * 获取商家列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBusinessInfo(Request $request)
    {
        $id = $request->input('id');

        $data = BusinessData::find($id);

        $re = DB::table('business_apply')->where('id',$data->business_apply_id)->first();
        $data['img2'] = $re->img2;
        return response()->json(['code'=>0, 'msg'=>'获取成功', 'data' => $data]);
    }

    //获取商家分类
    public function getBusinessFl(){
        $businessData = (new BusinessCategory())->get()->toArray();
        foreach ($businessData as $k=>$v){
            $businessArr[$k]['text'] = $v['name'];
            $businessArr[$k]['value'] = $v['id'];
        }
        return response()->json(['code'=>1, 'msg'=>'获取成功', 'data' => $businessArr]);

    }

    /**获取商家信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws LogicException
     */
    public function getBusinessData(Request $request){
        $user = $request->user();
//        $user->id;//用户ID
//        print_r($user->id);exit;
        if($user->role != User::ROLE_BUSINESS)
            throw new LogicException('非法访问');

        $data['business'] = $user->businessData()->first();

        //查询商户的营业执照和头图
        $business_apply_data = DB::table('business_apply')->where('id',$data['business']->business_apply_id)->first();
        $userIdImgData = DB::table('user_id_img')->where('business_apply_id',$data['business']->business_apply_id)->first();

        $data['business']['img'] = $business_apply_data->img;
        $data['business']['img2'] = $business_apply_data->img2;
        $data['business']['img_details1'] = $business_apply_data->img_details1;
        $data['business']['img_details2'] = $business_apply_data->img_details2;
        $data['business']['img_details3'] = $business_apply_data->img_details3;

        if ($userIdImgData){
            $data['business']['img_just'] = $userIdImgData->img_just;
            $data['business']['img_back'] = $userIdImgData->img_back;
            $data['business']['img_hold'] = $userIdImgData->img_hold;
        }else{
            $data['business']['img_just'] = '';
            $data['business']['img_back'] = '';
            $data['business']['img_hold'] = '';
        }

        $data['business_category'] = BusinessCategory::select(DB::raw('name as text, id as value'))->get();
        $data['category'] = BusinessCategory::whereId($data['business']->category_id)->value('name');
        //省
        if($data['business']->province)
            $data['business']->province_name = CityData::where('code', $data['business']->province)->value('name');
        //市
        if($data['business']->city)
            $data['business']->city_name = CityData::where('code', $data['business']->city)->value('name');
        //区
        if($data['business']->district)
            $data['business']->district_name = CityData::where('code', $data['business']->district)->value('name');
        //营业时间
        if($data['business']->run_time){
            $runTime = explode('-', $data['business']->run_time);
            $data['business']->start_time = $runTime[0];
            $data['business']->end_time = $runTime[1];
        }
        if($data['business']->banners)
            $data['business']->banners = env('OSS_URL').$data['business']->banners;

        return response()->json(['code'=>0, 'data'=>$data]);
    }

    /**修改商家信息
     * @param UpdateBusinessDataRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws LogicException
     */
    public function updateBusinessData(UpdateBusinessDataRequest $request){
        $user = $request->user();

        if($user->role != User::ROLE_BUSINESS)
            throw new LogicException('非法访问');
        try{
            //写入申请商家数据
            BusinessService::updateBusiness($request, $user);
        }catch (Exception $e) {
            throw $e;
        }

        return response()->json(['code'=>0, 'msg'=>'修改成功']);

    }
}
