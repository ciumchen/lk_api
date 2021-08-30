<?php

namespace App\Http\Controllers\API\Test;

use App\Http\Controllers\Controller;
use App\Models\BusinessApply;
use App\Models\BusinessData;
use App\Models\IntegralLogs;
use App\Models\LkshopOrder;
use App\Models\LkshopOrderLog;
use App\Models\OrderAirTrade;
use App\Models\OrderIntegralLkDistribution;
use App\Models\OrderMobileRecharge;
use App\Models\OrderUtilityBill;
use App\Models\OrderVideo;
use App\Models\RebateData;
use App\Models\TradeOrder;
use App\Models\User;
use App\Models\UserIdImg;
use App\Models\Users;
use App\Models\UserUpdatePhoneLog;
use App\Models\UserUpdatePhoneLogSd;
use Illuminate\Http\Request;
use App\Services\OrderService;
use App\Services\OssService;
use App\Models\Order;
use App\Services\OrderService_test;
use Illuminate\Support\Facades\DB;
use App\Exceptions\LogicException;
use App\Models\Address;
use App\Models\Assets;
use App\Models\AssetsLogs;
use App\Models\AssetsType;
use App\Models\BanList;
use App\Models\Setting;
use App\Models\WithdrawLogs;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Models\VerifyCode;
use App\Services\AddressService;
use App\Services\AssetsService;
use App\Services\TransferService;
use App\Services\AssetConversionService;
use App\Models\TtshopUser;

class MyNingModifyController extends Controller
{

    /**
     * 测试启用
     */
    public function __construct()
    {
        //die('测试接口');
    }
//
//    //添加用户待扣除的积分
    public function addUserNotDeductedAssets(){
        set_time_limit(0);
        ini_set('max_execution_time', '0');

    $arr = array(
["uid"=>68,"not_deducted_assets"=>	1.2],
["uid"=>8,"not_deducted_assets"=>	1.75],
["uid"=>99,"not_deducted_assets"=>	5.865],
["uid"=>9708,"not_deducted_assets"=>	16],
["uid"=>101,"not_deducted_assets"=>	17],
["uid"=>271,"not_deducted_assets"=>	16],
["uid"=>367,"not_deducted_assets"=>	24],
["uid"=>15438,"not_deducted_assets"=>	8],
["uid"=>6395,"not_deducted_assets"=>	2],
["uid"=>18904,"not_deducted_assets"=>	6],
["uid"=>4984,"not_deducted_assets"=>	18],
["uid"=>618,"not_deducted_assets"=>	21],
["uid"=>850,"not_deducted_assets"=>	51],
["uid"=>8609,"not_deducted_assets"=>	27],
["uid"=>202,"not_deducted_assets"=>	11.104],
["uid"=>837,"not_deducted_assets"=>	5.328],
["uid"=>14786,"not_deducted_assets"=>	20],
["uid"=>5859,"not_deducted_assets"=>	8],
["uid"=>203,"not_deducted_assets"=>	6],
["uid"=>10013,"not_deducted_assets"=>	5.25],
["uid"=>4731,"not_deducted_assets"=>	3.75],
["uid"=>9,"not_deducted_assets"=>	8],
["uid"=>33,"not_deducted_assets"=>	6],
["uid"=>60,"not_deducted_assets"=>	9],
["uid"=>9885,"not_deducted_assets"=>	16],
["uid"=>507,"not_deducted_assets"=>	31],
["uid"=>609,"not_deducted_assets"=>	42],
["uid"=>1454,"not_deducted_assets"=>	16],
["uid"=>1644,"not_deducted_assets"=>	24],
["uid"=>80,"not_deducted_assets"=>	15.75],
["uid"=>125,"not_deducted_assets"=>	17.5],
["uid"=>7837,"not_deducted_assets"=>	12],
["uid"=>56,"not_deducted_assets"=>	12],
["uid"=>16,"not_deducted_assets"=>	4],
["uid"=>10062,"not_deducted_assets"=>	8],
["uid"=>11047,"not_deducted_assets"=>	6],
["uid"=>3401,"not_deducted_assets"=>	5],
["uid"=>10,"not_deducted_assets"=>	0.5],
["uid"=>639,"not_deducted_assets"=>	2],
["uid"=>10674,"not_deducted_assets"=>	62],
["uid"=>100,"not_deducted_assets"=>	31],
["uid"=>134,"not_deducted_assets"=>	62],
["uid"=>191,"not_deducted_assets"=>	93],
["uid"=>10323,"not_deducted_assets"=>	18],
["uid"=>109,"not_deducted_assets"=>	5.5],
["uid"=>81,"not_deducted_assets"=>	1],
["uid"=>1829,"not_deducted_assets"=>	3],
["uid"=>237,"not_deducted_assets"=>	12],
["uid"=>17437,"not_deducted_assets"=>	12],

    );

    $i = 0;
    foreach ($arr as $k=>$v){
        $AssetsModel = Assets::where(['uid'=>$v['uid'],'assets_type_id'=>1])->first();
        if ($AssetsModel!=null){
            $AssetsModel->not_deducted_assets = $v['not_deducted_assets'];
            $AssetsModel->save();

        }
        $i++;
    }
    dd($i);



    }


}








