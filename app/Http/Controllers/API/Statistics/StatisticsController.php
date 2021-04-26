<?php

namespace App\Http\Controllers\API\Statistics;

use App\Http\Controllers\Controller;
use App\Http\Resources\RebateDataResources;
use App\Models\BusinessApply;
use App\Models\BusinessData;
use App\Models\Order;
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
            $data['total_consumption'] = Order::where("status",Order::STATUS_SUCCEED)
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
}
