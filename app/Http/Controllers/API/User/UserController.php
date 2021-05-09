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
use PDOException;
class UserController extends Controller
{
    /**申请成为商家
     * @param ApplyBusinessRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws LogicException
     */
    public function applyBusiness(ApplyBusinessRequest $request){

        //return response()->json(['code'=>0, 'msg'=>$request->all()]);

        $user = $request->user();
        //检测用户状态
        $user->checkStatus();

        if($user->role == User::ROLE_BUSINESS)
            throw new LogicException('已是商家无需再次申请');


        if(BusinessApply::where('uid', $user->id)->whereIn('status', [BusinessApply::DEFAULT_STATUS, BusinessApply::BY_STATUS])->exists())
            throw new LogicException('已申请成为商家，请等待审核结果');

        try{
          //写入申请商家数据
            BusinessService::submitApply($request, $user);
        }catch (Exception $e) {
            throw $e;
        }

        return response()->json(['code'=>0, 'msg'=>'申请成功']);

    }

    /**提交实名认证
     * @param RealNameRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws LogicException
     */
    public function realName(RealNameRequest $request){

        $user = $request->user();
        $user->checkStatus();
        if($user->is_auth == User::YES_IS_AUTH)
            throw new LogicException('已实名，无需再次提交');

        if(AuthLog::whereIn('status', [AuthLog::BY_STATUS, AuthLog::DEFAULT_STATUS])->exists())
            throw new LogicException('已提交过实名认证，请等待审核');

        try{
            AuthLog::create([
                'uid'=>$user->id,
                'id_card'=>$request->id_card,
                'name'=>$request->name,
                'id_card_img'=>$request->id_card_img,
                'id_card_people_img'=>$request->id_card_people_img,
            ]);
        }catch (PDOException $e) {
            report($e);
            throw new LogicException('提交失败，请重试');
        } catch (Exception $e) {
            throw $e;
        }

        return response()->json(['code'=>0, 'msg'=>'提交成功']);

    }

    /**
     * 获取用户详情
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUser(Request $request)
    {
        $user = $request->user();

        //判断用户是否能成为盟主
        if($user->member_head == User::CUSTOMER)
        {
            $inviteNumber = User::where("status", User::STATUS_NORMAL)
                ->where("role", User::ROLE_BUSINESS)
                ->where("invite_uid", $user->id)
                ->pluck('id')->toArray();

            //邀请商家数量大于50
            if(count($inviteNumber) >= 50)
            {
                $profit = Order::whereIn("business_uid", $inviteNumber)
                    ->where("status", Order::STATUS_SUCCEED)
                    ->sum("price");

                //营业额超50W，升级为盟主
                $limit = Setting::getSetting("leader_limit") ?? 500000;
                if(bccomp($profit, $limit, 2) > 0)
                {
                    $user = User::find($user->id);
                    $user->member_head = User::LEADER;
                    $user->save();
                }
            }
        }

        return response()->json(['code'=>0, 'data'=> new UserResources($user)]);
    }
    /**获取积分记录
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function getMyIntegralLog(Request $request)
    {
        $this->validate($request, [
            'page' => ['bail', 'nullable', 'int', 'min:1'],
            'per_page' => ['bail', 'nullable', 'int', 'min:1', 'max:50'],
        ]);

        $user = $request->user();


        $data = (new IntegralLogs())
            ->where('uid', $user->id)
            ->where('role', $request->input('role'))
            ->latest('id')
            ->forPage(Paginator::resolveCurrentPage('page'), $request->per_page ?: 10)
            ->get();

        return response()->json(['code'=>0, 'msg'=>'获取成功', 'data' => IntegralLogsResources::collection($data)]);
    }
}
