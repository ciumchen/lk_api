<?php

namespace App\Http\Controllers\API\Test;

use App\Exceptions\LogicException;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderMobileRechargeDetails;
use App\Services\Alipay\AlipayCertService;
use App\Services\Alipay\AlipayService;
use App\Services\bmapi\MobileRechargeService;
use App\Services\OrderService;
use App\Services\SignInService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DongController extends Controller
{
    public static $isIconvEnabled;
    
    public static $iconvOptions;
    
    /**
     * 测试启用
     */
    public function __construct()
    {
//        echo phpinfo();
//        dd(self::getIsIconvEnabled());
//        dump(iconv('UTF-8', 'GBK', '这特么是什么GBK'));
////        dump(iconv('UTF8', 'GBK//IGNORE', '这特么是什么GBK'));
//        dump(iconv('UTF-8', 'GBK//IGNORE', '这特么是什么GBK'));
//        dump(iconv('UTF-8', 'UTF-8//IGNORE', '这特么是什么UTF-8'));
        die('测试接口');
    }
    
    public static function getIsIconvEnabled()
    {
        if (isset(self::$isIconvEnabled)) {
            return self::$isIconvEnabled;
        }
        // Assume no problems with iconv
        self::$isIconvEnabled = true;
        // Fail if iconv doesn't exist
        if (!function_exists('iconv')) {
            self::$isIconvEnabled = false;
        } else {
            if (!@iconv('UTF-8', 'UTF-16LE', 'x')) {
                // Sometimes iconv is not working, and e.g. iconv('UTF-8', 'UTF-16LE', 'x') just returns false,
                self::$isIconvEnabled = false;
            } else {
                if (defined('PHP_OS') && @stristr(PHP_OS, 'AIX') && defined('ICONV_IMPL') && (@strcasecmp(
                            ICONV_IMPL,
                            'unknown'
                        ) == 0) && defined('ICONV_VERSION') && (@strcasecmp(
                            ICONV_VERSION,
                            'unknown'
                        ) == 0)) {
                    // CUSTOM: IBM AIX iconv() does not work
                    self::$isIconvEnabled = false;
                }
            }
        }
        // Deactivate iconv default options if they fail (as seen on IMB i)
        if (self::$isIconvEnabled && !@iconv('UTF-8', 'UTF-16LE'.self::$iconvOptions, 'x')) {
            self::$iconvOptions = '';
        }
        return self::$isIconvEnabled;
    }
    
    //
    public function orderTest(Request $request)
    {
        $order_id = $request->input('order_id');
        $Order = new Order();
        $orderInfo = $Order->find($order_id);
        dump($orderInfo->mobile);
    }
    
    public function carbon()
    {
        echo Carbon::now();                                                      // 获取当前时间
        echo '<br>';
        echo Carbon::now('Arctic/Longyearbyen');                                 //获取指定时区的时间
        echo '<br>';
        echo Carbon::now(new \DateTimeZone('Europe/London'));                    //获取指定时区的时间
        echo '<br>';
        echo Carbon::today();                                                    //获取今天时间 时分秒是 00-00-00
        echo '<br>';
        echo Carbon::tomorrow('Europe/London');                                  // 获取明天的时间
        echo '<br>';
        echo Carbon::yesterday();                                                // 获取昨天的时间
        echo '<br>';
        echo Carbon::now()->timestamp;                                           // 获取当前的时间戳
        echo '<br>';
        //以上结果输出的是一个Carbon 类型的日期时间对象
        $res = Carbon::now();
        echo $res;
    }
    
    public function setMobileDetails(Request $request)
    {
        $Model = new OrderMobileRechargeDetails();
        $data = [];
        $data[] = [
            'order_mobile_id' => '1',
            'order_id'        => '2',
            'mobile'          => '12345678912',
            'money'           => '50',
        ];
        $data[] = [
            'order_mobile_id' => '1',
            'order_id'        => '2',
            'mobile'          => '12345678913',
            'money'           => '50',
        ];
        $data[] = [
            'order_mobile_id' => '1',
            'order_id'        => '2',
            'mobile'          => '12345678914',
            'money'           => '50',
        ];
        $data[] = [
            'order_mobile_id' => '1',
            'order_id'        => '2',
            'mobile'          => '12345678915',
            'money'           => '50',
        ];
        try {
            $res = $Model->addAll($data);
        } catch (\Exception $e) {
            throw $e;
        }
        dd($res);
    }
    
    public function updateAll()
    {
        $Model = new OrderMobileRechargeDetails();
        $data = $Model->updateStatusAll([['order_mobile_id', '=', '1']]);
        dump($data);
//        $res = $Model->save($data);
//        dd($res);
    }
    
    public function updateMobileDetails(Request $request)
    {
        $order_id = $request->input('order_id');
        try {
            $MobileService = new MobileRechargeService();
            $bill = [
                'rechargeAccount' => '18707145152',
                'saleAmount'      => '50.00',
            ];
            $MobileService->updateManyMobileOrder($order_id, $bill);
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Description:
     *
     * @param \Illuminate\Http\Request $request
     *
     * @throws \Exception
     * @author lidong<947714443@qq.com>
     * @date   2021/7/6 0006
     */
    public function manyRecharge(Request $request)
    {
        $order_id = $request->input('order_id');
        try {
            $MobileService = new MobileRechargeService();
            $res = $MobileService->manyRecharge($order_id);
        } catch (\Exception $e) {
            throw new LogicException($e->getMessage());
        }
        dd($res);
    }
    
    public function signIn()
    {
        try {
            $SignInService = new SignInService();
//            $res = $SignInService->getPreDayContinuousLoginTimes(9566, '2020-10-22');
//            $res = $SignInService->updateSignInAfterAddPoints(9566, '2020-10-22');
            $res = $SignInService->yxSignIn(1668, '2020-10-22');
        } catch (\Exception $e) {
            throw new LogicException($e->getMessage());
        }
        return apiSuccess($res);
    }
    
    public function encourage(Request $request)
    {
        $order_id = $request->input('order_id');
        try {
            $Order = Order::findOrFail($order_id);
            $OrderService = new OrderService();
            $desc = $OrderService->getDescription($order_id, $Order);
            $res = $OrderService->completeOrderTable($order_id, $Order->uid, $desc, $Order->order_no);
        } catch (\Exception $e) {
            throw $e;
        }
        return apiSuccess($res);
    }
    
    public function alipayTest()
    {
//        dd('11');
        try {
            $AlipayService = new AlipayService();
            $as = $AlipayService->test();
            dd($as);
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    public function alipayTest2()
    {
        try {
            $AlipayService = new AlipayCertService();
            $as = $AlipayService->authByCertWebPage();
//            echo $as;
            dd($as);
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    public function alipayTest3()
    {
        try {
            $AlipayService = new AlipayCertService();
            $as = $AlipayService->authByCertWebPage();
            echo $as;
//            dd($as);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
