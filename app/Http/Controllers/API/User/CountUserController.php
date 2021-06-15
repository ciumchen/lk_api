<?php


namespace App\Http\Controllers\API\User;

use App\Exceptions\LogicException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApplyBusinessRequest;
use App\Http\Requests\RealNameRequest;
use App\Http\Resources\IntegralLogsResources;
use App\Http\Resources\UserResources;
use App\Models\AuthLog;
use App\Models\BusinessApply;
use App\Models\IntegralLogs;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Services\BusinessService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use PDOException;
class CountUserController extends Controller
{

    //今日新增消费者
    public function addConsumer(){
        $today = date('Y-m-d',time());
        $data['number'] = DB::table('users')->where('created_at','>=',$today)->where('member_head',1)->count();
        return $data;
    }


    //今日新增商户,判断今日审核商户通过的人数
    public function addMerchant(){
        $today = date('Y-m-d',time());
        $data['number'] = DB::table('business_apply')->where('updated_at','>=',$today)->where('status',2)->count();
        return $data;
    }






    public function consumerCount(){
        //消费者-待激励统计
       $data['consumerLk'] = DB::table('users')->sum('lk');//实时lk总数

    //消费者-今日分配
//        $re = DB::table('rebate_data')->where('day',date('Y-m-d'),time())->first();
//        $data['consumerTodayFfNum'] = round($re->consumer/DB::table('users')->sum('lk'),2);

//        $countProfitPrice = DB::table('order')->where('status',2)->where('created_at','>=',date('Y-m-d',time()))->sum('profit_price');
//        $data['consumerTodayFfNum'] = round($countProfitPrice*0.675/DB::table('users')->sum('lk'),2);

        $dataInfo = DB::table('order_integral_lk_distribution')->where('day',strtotime(date('Y-m-d',time())))->first();
        $data['consumerTodayFfNum'] = round(($dataInfo->count_profit_price*0.675)/$data['consumerLk'],2);

    //消费者-昨日lk总数
        $re = DB::table('rebate_data')->where('day',date("Y-m-d",strtotime("-1 day")))->first();
        if($re!=null && $re->consumer_lk_iets){
            if ($re->consumer_lk_iets!=0){
                $data['consumerYesterdayLkNum'] = round($re->consumer/$re->consumer_lk_iets,2);
            }else{
                $data['consumerYesterdayLkNum'] = 0;
            }
        }else{
            $data['consumerYesterdayLkNum'] = 0;
        }
    //消费者-昨日分配
        $re = DB::table('rebate_data')->where('day',date("Y-m-d",strtotime("-1 day")))->first();
        if($re!=null && $re->consumer_lk_iets){
            if ($re->consumer_lk_iets!=0){
                $data['consumerYesterdayLkFf'] = round($re->consumer_lk_iets,2);
            }else{
                $data['consumerYesterdayLkFf'] = 0;
            }
        }else{
            $data['consumerYesterdayLkFf'] = 0;
        }
        return $data;
    }


    public function merchantCount(){
        //商户-待激励统计
        $data['merchantLk'] = DB::table('users')->sum('business_lk');

    //商户-今日分配
//        $re = DB::table('rebate_data')->where('day',date('Y-m-d'),time())->first();
//        $data['merchantTodayFfNum'] = round($re->business/DB::table('users')->sum('business_lk'),2);

//        $countProfitPrice = DB::table('order')->where('status',2)->where('created_at','>=',date('Y-m-d',time()))->sum('profit_price');
//        $data['merchantTodayFfNum'] = round($countProfitPrice*0.15/DB::table('users')->sum('business_lk'),2);

        $dataInfo = DB::table('order_integral_lk_distribution')->where('day',strtotime(date('Y-m-d',time())))->first();
        $data['merchantTodayFfNum'] = round(($dataInfo->count_profit_price*0.15)/DB::table('users')->sum('business_lk'),2);

    //商户-昨日lk总数
        $re = DB::table('rebate_data')->where('day',date("Y-m-d",strtotime("-1 day")))->first();
        if($re!=null && $re->business_lk_iets){
            if ($re->business_lk_iets!=0){
                $data['merchantYesterdayLkNum'] = round($re->business/$re->business_lk_iets,2);
            }else{
                $data['merchantYesterdayLkNum'] = 0;
            }
        }else{
            $data['merchantYesterdayLkNum'] = 0;
        }
    //商户-昨日分配
        $re = DB::table('rebate_data')->where('day',date("Y-m-d",strtotime("-1 day")))->first();
        if($re!=null && $re->business_lk_iets){
            if ($re->business_lk_iets!=0){
                $data['merchantYesterdayLkFf'] = round($re->business_lk_iets,2);
            }else{
                $data['merchantYesterdayLkFf'] = 0;
            }
        }else{
            $data['merchantYesterdayLkFf'] = 0;
        }
        return $data;
    }















}
