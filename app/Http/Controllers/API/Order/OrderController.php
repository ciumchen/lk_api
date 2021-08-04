<?php

namespace App\Http\Controllers\API\Order;

use App\Exceptions\LogicException;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Http\Resources\OrdersResources;
use App\Models\Order;
use App\Models\OrderList;
use App\Models\Setting;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use PDOException;
class OrderController extends Controller
{
    /**录单
     * @param OrderRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws LogicException
     */
    public function __invoke(OrderRequest $request){

        $user = $request->user();
//        var_dump($user);exit;
        $buyUser = User::where('phone', $request->phone)->first();
        if($user->id == $buyUser->id)
            throw new LogicException('商家自己不能给自己录单');
        //检测用户状态
        $user->checkStatus();

        if($user->role != User::ROLE_BUSINESS)
            throw new LogicException('非商家无法录单，请联系在店消费商家');
        $maxPrice = Setting::getSetting('max_price')??99999;
        if(bccomp($request->price, 0.1, 8) < 0 || bccomp($request->price, $maxPrice, 8) > 0)
            throw new LogicException("消费金额必须在0.1-{$maxPrice}之间");


        $limitPrice = Setting::getSetting('limit_price')??0;

        //商家录单限额验证
        if($user->businessData->limit_price > 0 || $limitPrice > 0){

            $myLimitPrice = Order::whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
                ->where('business_uid', $user->id)
                ->whereIn('status', [Order::STATUS_DEFAULT, Order::STATUS_SUCCEED])
                ->sum('price');
            $limitPrice = $user->businessData->limit_price > 0 ?$user->businessData->limit_price: $limitPrice;
            if(bcadd($myLimitPrice, $request->price, 2) > $limitPrice)
                throw new LogicException("已超过今日录单限额，最高录单金额{$limitPrice}");
        }

        //检测买家账户是否异常
        $buyUser->checkStatus();
        if($user->businessData->status != 1)
            throw new LogicException("请先完善商家信息，才能录单");
        try{
            $re = Order::create([
                'uid' => $buyUser->id,
                'business_uid' => $user->id,
                'name' => $request->name,
                'profit_ratio' => $request->ratio,
                'price' => $request->price,
                'profit_price' => bcmul($request->price, bcdiv($request->ratio, 100, 4), 2),
                'pay_status' => 'await',
            ])->toArray();
        }catch (PDOException $e) {
            report($e);
            throw new LogicException('录单失败，请联系客服');
        } catch (Exception $e) {
            throw $e;
        }

        return response()->json(['code'=>0, 'msg'=>'录单成功，请尽快缴纳让利金额，等待审核','orderId'=>$re['id']]);

    }

    /**获取我的订单
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function getMyOrders(Request $request)
    {
        $this->validate($request, [
            'page' => ['bail', 'nullable', 'int', 'min:1'],
            'per_page' => ['bail', 'nullable', 'int', 'min:1', 'max:50'],
        ]);

        $user = $request->user();
        $bOrder = $request->input('bOrder', false);

        $data = (new Order())
            ->leftJoin('trade_order', function($join){
                $join->on('order.id', 'trade_order.oid');
            })
            ->when(!$bOrder,function($query) use ($user) {
                return $query->where('uid', $user->id);
            })
            ->when($bOrder,function($query) use ($user) {
                $query->where('business_uid', $user->id);
            })
            ->orderBy('order.created_at', 'desc')
            ->latest('id')
            ->forPage(Paginator::resolveCurrentPage('page'), $request->per_page ?: 10)
            ->distinct('order.id')
            ->get(['order.*', 'trade_order.numeric']);

        return response()->json(['code'=>0, 'msg'=>'获取成功', 'data' => OrdersResources::collection($data)]);
    }

    /**删除
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws LogicException
     */
    public function delOrder(Request $request){
        $id = $request->post('id');
        if(!$id)
            throw new LogicException('请传递要删除的订单号');

        $user = $request->user();
        try{
            $res = $user->businessOrder()->whereId($id)->where('status', Order::STATUS_DEFAULT)->delete();

            if($res <= 0)
                throw new LogicException('您无权删除该订单');

        }catch (PDOException $e) {
            report($e);
            throw new LogicException('删除订单失败，请联系客服');
        } catch (Exception $e) {
            throw $e;
        }
        return response()->json(['code'=>0, 'msg'=>'删除成功']);
    }

    /**获取可选让利比例
     * @return int[]
     */
    public function getRatio(){
        return response()->json(['code'=>0, 'data'=>Setting::getManySetting('business_rebate_scale')]);
    }

    /**获取用户订单列表
     * @param Request $request
     * @return mixed
     * @throws LogicException
     */
    public function userOrderList(Request $request)
    {
        //返回
        return (new OrderList())->userOrderList($request->uid);
    }

    /**获取商家订单列表
     * @param Request $request
     * @return mixed
     * @throws LogicException
     */
    public function shopOrderList(Request $request)
    {
        //返回
        return (new OrderList())->shopOrderList($request->uid);
    }

    /**获取多人代充充值详情
     * @param Request $request
     * @return mixed
     * @throws LogicException
     */
    public function getMobileDetails(Request $request)
    {
        //返回
        return (new OrderList())->getMobileDetailsList($request->id);
    }
}
