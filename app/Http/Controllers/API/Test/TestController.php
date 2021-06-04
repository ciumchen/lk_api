<?php


namespace App\Http\Controllers\API\Test;


use App\Services\OrderService;
use App\Services\OssService;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Services\OrderService_test;
use Illuminate\Support\Facades\DB;
class TestController
{
    //test测试
    public function test(){
//        $re = DB::select("select * form users");
        //查询当前用户的邀请人
//        $invite_uid = DB::table("users")->where('id',1)->pluck('invite_uid')->toArray();
//        if($invite_uid[0]!=0){
//            //有邀请人
//            $member_head = DB::table("users")->where('id',$invite_uid[0])->pluck('member_head')->toArray();
//            if ($member_head[0]!=2){
//                //邀请人是非盟主按2%计算
//            }else{
//                //邀请人是盟主按3.5%计算
//            }
//        }else{
//            //没有邀请人按2%计算
//        }
//
//
//
//        echo "<pre>";
//        print_r($invite_uid);
//        print_r($member_head);

        $re = Order::create([
            'state' => 1,
            'uid' => 2,
            'business_uid' => 3,
            'name' => '张三',
            'profit_ratio' => '5',
            'price' => '100',
            'profit_price' => '200',
        ])->toArray();

        var_dump($re['id']);
        echo 'test1112021年4月22日 13:39:29';
    }

    //图片上传oss测试
    public function test2(Request $request){
//        echo 'test22222';
//        var_dump($request->img);
//        var_dump($request->file('img'));
        $imgUrl = OssService::base64Upload($request->img);
        var_dump($imgUrl);

//        $path = $request->file('img')->store('avatars');
//
//        return $path;


    }

    //订单回调测试
    public function orderTest(Request $request){
//        echo "测试积分添加";
        //更新 order 表审核状态
        $orderOn = $request->input('orderOn');
        (new OrderService())->completeOrder($orderOn);
    }

    //自动审核测试
    public function pushOrder(){
        set_time_limit(0);
        ini_set('max_execution_time', '0');
        $count = Order::where('status',"!=",2)->where('id','>',13281)->count();

        if ($count){
            $orderInfo = DB::table('order')->where('order.id','>','13281')
                ->where('order.status',"!=",2)
                ->leftJoin('trade_order','order.id','=','trade_order.oid')
                ->limit(20)->get()->toArray();
            foreach ($orderInfo as $k=>$v){
                if($v->order_no){
                    (new OrderService_test())->completeOrder($v->order_no);
                }

            }

            return "<h4>今次自动完成审核20条记录，总共还有<font color='red'>".($count-20)."</font>条订单还需要审核</h4>";

        }else{
            return '<h4>所有订单审核完成</h4>';
        }


    }


}
