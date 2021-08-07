<?php

namespace App\Http\Controllers\API\Statistics;

use App\Http\Controllers\Controller;
use App\Http\Resources\RebateDataResources;
use App\Models\BusinessApply;
use App\Models\BusinessData;
use App\Models\Order;
use App\Models\OrderIntegralLkDistribution;
use App\Models\RebateData;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    /**
     * 获取统计页面数据
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatistics()
    {
        //总的累计信息
        $allData = Cache::remember("today_statistics",180, function (){
            //消费总额
            $data['total_consumption'] = Order::where("status",Order::STATUS_SUCCEED)->where('name','!=','开通会员')
                    ->sum("price") ?? 0;

            //消费人数
            $data['total_customers'] = Order::where("status",Order::STATUS_SUCCEED)
                    ->distinct("uid")
                    ->count() ?? 0;

            //商家总数
            $data['total_business'] = BusinessData::where("status", "<", BusinessData::STATUS_DELETED)->count();

            return $data;

        });

        //昨日相关让利信息
        $yesterday = RebateData::select(DB::raw("
            consumer,
            business,
            welfare,
            share,
            market,
            platform,
            people,
            new_business,
            total_consumption,
            consumer_lk_iets,
            business_lk_iets
        "))->where('day', now()->yesterday()->toDate())->first();

        $yesterday = new RebateDataResources($yesterday);
        //总的累计让利信息
        $total = RebateData::select(DB::raw("
            sum(consumer) as consumer,
            sum(business) as business,
            sum(welfare) as welfare,
            sum(share) as share,
            sum(market) as market,
            sum(platform) as platform
        "))->first();

        $total = new RebateDataResources($total);

        $ratio = Setting::getManySetting('business_rebate_scale');
        $userRatio = Setting::getManySetting('user_rebate_scale');

        //各比例让利累计
        $allRatioData = Cache::remember("ratio_data",180, function () use ($ratio){

            $data = [];
            foreach($ratio as $v){
                $data[] = Order::where('profit_ratio', $v)->where('pay_status','succeeded')->sum('price')??0;

            }
            return $data;

        });
        //昨日各比例让利累计
        $yesterdayRatioData = Cache::remember("yesterday_ratio_data",180, function () use ($ratio){

            $data = [];
            foreach($ratio as $v){
                $data[] = Order::where('profit_ratio', $v)->where('pay_status','succeeded')->whereBetween('created_at', [now()->yesterday()->startOfDay(), now()->yesterday()->endOfDay()])->sum('profit_price')??0;

            }
            return $data;

        });

        return response()->json(['code' => 0, 'msg' => 'OK', 'data' => [
            'yesterday' => $yesterday,
            'all_data' => $allData,
            'total' => $total,
            'ratio' => $ratio,
            'all_ratio_data'=> $allRatioData,
            'user_ratio_data'=> $userRatio,
            'yesterday_ratio_data'=> $yesterdayRatioData
        ]]);

    }

    //获取今日消费金额和昨日消费金额统计
    public function getNewStatistics(){
        //获取平台所有用户总数
        $Data['userCount'] = DB::table('users')->count();

//        //今日消费5%-10%-20%的消费金额的统计
//        $Data['todayPriceTotal']['rl5'] = DB::table('order')->where('profit_ratio',5)->where('status',2)->where('updated_at','>',date('Y-m-d',time()))->sum('price');
//        $Data['todayPriceTotal']['rl10'] = DB::table('order')->where('profit_ratio',10)->where('status',2)->where('updated_at','>',date('Y-m-d',time()))->sum('price');
//        $Data['todayPriceTotal']['rl20'] = DB::table('order')->where('profit_ratio',20)->where('status',2)->where('updated_at','>',date('Y-m-d',time()))->sum('price');
//
//        //昨日消费5%-10%-20%的消费金额的统计
//        $Data['yesterdayPriceTotal']['rl5'] = DB::table('order')->where('profit_ratio',5)->where('status',2)
//            ->where('updated_at','>',date('Y-m-d',strtotime("-1 day")))->where('updated_at','<',date('Y-m-d',time()))->sum('price');
//        $Data['yesterdayPriceTotal']['rl10'] = DB::table('order')->where('profit_ratio',10)->where('status',2)
//            ->where('updated_at','>',date('Y-m-d',strtotime("-1 day")))->where('updated_at','<',date('Y-m-d',time()))->sum('price');
//        $Data['yesterdayPriceTotal']['rl20'] = DB::table('order')->where('profit_ratio',20)->where('status',2)
//            ->where('updated_at','>',date('Y-m-d',strtotime("-1 day")))->where('updated_at','<',date('Y-m-d',time()))->sum('price');

        //今日消费5%-10%-20%的消费金额的统计---新修改2021年8月7日 14:46:45
        $Data['todayPriceTotal']['rl5'] = DB::table('order')->where('profit_ratio',5)->where('status',2)->where('name','!=','开通会员')->where('created_at','>',date('Y-m-d',time()))->sum('price');
        $Data['todayPriceTotal']['rl10'] = DB::table('order')->where('profit_ratio',10)->where('status',2)->where('name','!=','开通会员')->where('created_at','>',date('Y-m-d',time()))->sum('price');
        $Data['todayPriceTotal']['rl20'] = DB::table('order')->where('profit_ratio',20)->where('status',2)->where('name','!=','开通会员')->where('created_at','>',date('Y-m-d',time()))->sum('price');

        //昨日消费5%-10%-20%的消费金额的统计
//        $Data['yesterdayPriceTotal']['rl5'] = DB::table('order')->where('profit_ratio',5)->where('status',2)
//            ->where('created_at','>',date('Y-m-d',strtotime("-1 day")))->where('created_at','<',date('Y-m-d',time()))->sum('price');
//        $Data['yesterdayPriceTotal']['rl10'] = DB::table('order')->where('profit_ratio',10)->where('status',2)
//            ->where('created_at','>',date('Y-m-d',strtotime("-1 day")))->where('created_at','<',date('Y-m-d',time()))->sum('price');
//        $Data['yesterdayPriceTotal']['rl20'] = DB::table('order')->where('profit_ratio',20)->where('status',2)
//            ->where('created_at','>',date('Y-m-d',strtotime("-1 day")))->where('created_at','<',date('Y-m-d',time()))->sum('price');

        return $Data;
    }

    //商户让利累计
    public function shRlCount(Request $request){
        //5%-10%-20%的消费金额的统计
        $uid = $request->input('uid');//获取当前用户的id
        $Data['rl5'] = DB::table('order')->where('business_uid',$uid)->where('profit_ratio',5)->where('pay_status','succeeded')->sum('profit_price');
        $Data['rl10'] = DB::table('order')->where('business_uid',$uid)->where('profit_ratio',10)->where('pay_status','succeeded')->sum('profit_price');
        $Data['rl20'] = DB::table('order')->where('business_uid',$uid)->where('profit_ratio',20)->where('pay_status','succeeded')->sum('profit_price');

        return $Data;
    }

    //获取今日排队和剩余排队订单的消费金额的让利比例的统计（5%-10%-20%）
    public function getGiveOderPrice(){
        //获取今日所有添加积分的订单的消费金额
        $todaytime=strtotime(date("Y-m-d"),time());
        $todayData = OrderIntegralLkDistribution::where('day',$todaytime)->first();
        if($todayData){
            $todayData->toArray();
            $data['todayData']['price_5'] = $todayData['price_5'];
            $data['todayData']['price_10'] = $todayData['price_10'];
            $data['todayData']['price_20'] = $todayData['price_20'];
        }

        //获取昨日所有添加积分的订单的消费金额
        $yesterdaytime=strtotime(date('Y-m-d',strtotime("-1 day")));
        $yesterdayData = OrderIntegralLkDistribution::where('day',$yesterdaytime)->first();
        if($yesterdayData){
            $yesterdayData->toArray();
            $data['yesterdayData']['price_5'] = $yesterdayData['price_5'];
            $data['yesterdayData']['price_10'] = $yesterdayData['price_10'];
            $data['yesterdayData']['price_20'] = $yesterdayData['price_20'];
        }

//        //剩余订单统计 surplus
//        $data['sypddd']['price_5'] = Order::where('status',2)->where('line_up',1)->where('profit_ratio',5)->sum('price');
//        $data['sypddd']['price_10'] = Order::where('status',2)->where('line_up',1)->where('profit_ratio',10)->sum('price');
//        $data['sypddd']['price_20'] = Order::where('status',2)->where('line_up',1)->where('profit_ratio',20)->sum('price');

        //剩余订单统计 surplus
        $data['sypddd']['price_5'] = Order::where('status',2)->where('line_up',1)->where('profit_ratio',5)->count('id');
        $data['sypddd']['price_10'] = Order::where('status',2)->where('line_up',1)->where('profit_ratio',10)->count('id');
        $data['sypddd']['price_20'] = Order::where('status',2)->where('line_up',1)->where('profit_ratio',20)->count('id');

        return response()->json(['code'=>0, 'msg'=>'获取成功', 'data' => $data]);

    }

    //获取所有订单类型的让利比例
    public function getOrderRlbl(){
        $typeArr = [
            'HF' => 'set_business_rebate_scale_hf',
            'YK' => 'set_business_rebate_scale_yk',
            'MT' => 'set_business_rebate_scale_mt',
            'DD' => 'set_business_rebate_scale_dd',
            'ZL' => 'set_business_rebate_scale_zl',
            'AT' => 'set_business_rebate_scale_at',
            'VC' => 'set_business_rebate_scale_vc',
        ];
        $data = array();
        foreach ($typeArr as $k=>$v){
            $data[$k] = Setting::where('key',$v)->value('value');
        }

        return $data;
    }


}


