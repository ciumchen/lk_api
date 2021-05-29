<?php


namespace App\Http\Controllers\API\Test;


use App\Http\Controllers\API\Test\OpenClient;
use App\Services\OrderService;
use App\Services\OssService;
use Illuminate\Http\Request;
use GuzzleHttp;

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

    /**获取机场站点测试
     * @return mixed
     * @throws
     */
    public function airList()
    {
        $appKey = '10002911';
        $accessTocken = '2dd520ba581a4db5a3fcbd074e19d618';
        $appSecret = 'oBfoIUjgyTREH5c70qeAueUXgAoZT0AW';
        $method = 'qianmi.elife.Air.stations.list';
        $v = 1.1;
        $format = 'json';
        $signMethod = 'sha1';
        $url = 'http://api.bm001.com/api';
        $date = date("Y-m-d H:i:s");

        //组装生成sign 参数
        $signData = [
            'appKey' => $appKey,
            'format' => $format,
            'method' => $method,
            'v'      => $v
        ];
        $methodData = [
            'accessTocken' => $accessTocken,
            'appSecret'    => $appSecret,
            'signMethod'   => $signMethod
        ];

        //生成sign
        $sign = $this->setSign($signData, $methodData);
        //$sign = '6436C15A53DB76E844D9288C1AA9D11459F28202';
        //组装参数
        $http = new GuzzleHttp\Client;
        $query = $signData;
        $query['sign'] = $sign;
        $query['access_token'] = $accessTocken;
        $query['timestamp'] = $date;
        unset($query['appKey'], $query['format']);

        //调用获取机场站点url
        $response = $http->get($url, [
            'query' => $query,
        ]);

        //返回数据
        return json_decode($response->getBody(), 1);
    }

    /**生成sign
     * @param array $params
     * @param array $methodData
     * @return mixed
     * @throws
     */
    /*private function setSign(array $params, array $methodData)
    {
        //封装参数
        if(empty($methodData['accessTocken']))
        {
            throw new Exception("Error:Invalid Arguments:the value of accessToken can not be null." , 41);
            return;
        } else
        {
            $params["access_token"] = $methodData['accessTocken'];
        }

        //排序
        ksort($params);
        $signString = "";
        foreach ($params as $k => $v)
        {
            $signString .= $k . $v;
        }
        unset($k, $v);
        $signString = $methodData['appSecret'] . $signString . $methodData['appSecret'];

        //返回sign
        return strtoupper(call_user_func($methodData['signMethod'], $signString));
    }*/

    /**
     * 加密请求参数
     * @param $params
     * @return string
     */
    /*protected function generateSign($params)
    {
        ksort($params);

        $sign_string = "";
        foreach ($params as $k => $v)
        {
            $sign_string .= $k . $v;
        }
        unset($k, $v);
        $appSecret = 'oBfoIUjgyTREH5c70qeAueUXgAoZT0AW';

        $sign_string = $appSecret . $sign_string . $appSecret;
        $sign_method = 'sha1';

        return strtoupper(call_user_func($sign_method, $sign_string));
    }*/

}
