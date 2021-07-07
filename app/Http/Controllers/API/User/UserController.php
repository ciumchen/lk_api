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
use App\Models\VerifyCode;
use App\Services\BusinessService;
use App\Services\OssService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use PDOException;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{

    /**申请成为商家
     *
     * @param ApplyBusinessRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws LogicException
     */
    public function applyBusiness(ApplyBusinessRequest $request)
    {
        $user = $request->user();
        //检测用户状态
        $user->checkStatus();
        if ($user->role == User::ROLE_BUSINESS) {
            throw new LogicException('已是商家无需再次申请');
        }
        if (BusinessApply::where('uid', $user->id)->whereIn('status', [BusinessApply::DEFAULT_STATUS,
                                                                       BusinessApply::BY_STATUS,
        ])->exists()) {
            throw new LogicException('已申请成为商家，请等待审核结果');
        }
        try {
            //写入申请商家数据
            BusinessService::submitApply($request, $user);
        } catch (Exception $e) {
            throw $e;
        }
        return response()->json(['code' => 0, 'msg' => '申请成功']);
    }

    /**提交实名认证
     *
     * @param RealNameRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws LogicException
     */
    public function realName(RealNameRequest $request)
    {
        $user = $request->user();
        $user->checkStatus();
        if ($user->is_auth == User::YES_IS_AUTH) {
            throw new LogicException('已实名，无需再次提交');
        }
        if (AuthLog::whereIn('status', [AuthLog::BY_STATUS, AuthLog::DEFAULT_STATUS])->exists()) {
            throw new LogicException('已提交过实名认证，请等待审核');
        }
        try {
            AuthLog::create([
                'uid'                => $user->id,
                'id_card'            => $request->id_card,
                'name'               => $request->name,
                'id_card_img'        => $request->id_card_img,
                'id_card_people_img' => $request->id_card_people_img,
            ]);
        } catch (PDOException $e) {
            report($e);
            throw new LogicException('提交失败，请重试');
        } catch (Exception $e) {
            throw $e;
        }
        return response()->json(['code' => 0, 'msg' => '提交成功']);
    }

    /**
     * 获取用户详情
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUser(Request $request)
    {
        $user = $request->user();
        //判断用户是否能成为盟主
        if ($user->member_head == User::CUSTOMER) {
            $inviteNumber = User::where("status", User::STATUS_NORMAL)
                                ->where("role", User::ROLE_BUSINESS)
                                ->where("invite_uid", $user->id)
                                ->pluck('id')->toArray();
            //邀请商家数量大于50
            if (count($inviteNumber) >= 50) {
                $profit = Order::whereIn("business_uid", $inviteNumber)
                               ->where("status", Order::STATUS_SUCCEED)
                               ->sum("price");
                //营业额超50W，升级为盟主
                $limit = Setting::getSetting("leader_limit") ?? 500000;
                if (bccomp($profit, $limit, 2) > 0) {
                    $user = User::find($user->id);
                    $user->member_head = User::LEADER;
                    $user->save();
                }
            }
        }
        return response()->json(['code' => 0, 'data' => new UserResources($user)]);
    }

    /**获取积分记录
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function getMyIntegralLog(Request $request)
    {
        $this->validate($request, [
            'page'     => ['bail', 'nullable', 'int', 'min:1'],
            'per_page' => ['bail', 'nullable', 'int', 'min:1', 'max:50'],
        ]);
        $user = $request->user();
        $data = (new IntegralLogs())
            ->where('uid', $user->id)
            ->where('role', $request->input('role'))
            ->latest('id')
            ->forPage(Paginator::resolveCurrentPage('page'), $request->per_page ?: 10)
            ->get();
        return response()->json(['code' => 0, 'msg' => '获取成功', 'data' => IntegralLogsResources::collection($data)]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function changeInviteUid(Request $request)
    {
        $phone = $request->input('phone');
        $user = $request->user();
        if (!in_array($user->invite_uid, [1, 2])) {
            throw new LogicException('非系统默认邀请人不可修改');
        }
        $new_invite_user = (new User)->getUserByPhone($phone);
        if (empty($new_invite_user)) {
            throw new LogicException('邀请人不存在');
        }
        if ($new_invite_user->id == $user->id) {
            throw new LogicException('邀请人不能是自己');
        }
        if ($new_invite_user->status != 1) {
            throw new LogicException('邀请人状态为非正常状态不可修改');
        }
        try {
            $user->invite_uid = $new_invite_user->id;
            $user->save();
        } catch (Exception $e) {
            throw $e;
        }
        $return = [
            'code' => 0,
            'msg'  => '邀请人修改成功',
        ];
        return response()->json($return);
    }


    /**
     * 修改头像
     * TODO:修改头像
     *
     * @param \Illuminate\Http\Request $request
     *
     * @throws \App\Exceptions\LogicException|\OSS\Core\OssException
     */
    public function changeUserAvatar(Request $request)
    {
        $avatar = $request->input('avatar');
        $user = $request->user();
        $old_avatar = $user->avatar;
        try {
            if (!$avatar) {
                throw new LogicException('请上传图片');
            }
            if (!in_array($old_avatar, (new User())->avatar_default)) {
//                dd($old_avatar);
                Storage::disk('oss')->delete($old_avatar);
            }
            $avatar = OssService::base64Upload($avatar, 'avatar/');
            $user->avatar = $avatar;
            $user->save();
            $avatar_url = env('OSS_URL') . $avatar;
        } catch (LogicException $e) {
            throw new LogicException('');
        }
        $data = [
            'code' => 0,
            'msg'  => '修改成功',
            'data' => ['avatar_url' => $avatar_url],
        ];
        return response()->json($data);
    }

    /**
     * 修改个性签名
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\LogicException
     */
    public function changeUserSign(Request $request)
    {
        $sign = $request->input('sign');
        $user = $request->user();
        try {
            if (empty($sign)) {
                throw  new LogicException('请填写签名文字');
            }
            if (mb_strlen($sign) > 50) {
                throw new LogicException('签名不能超过50个字符');
            }
            $user->sign = $sign;
            $user->save();
        } catch (LogicException $le) {
            throw $le;
        }
        return response()->json(['code' => 0, 'msg' => '修改成功']);
    }

    /**
     * 修改姓名
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\LogicException
     */
    public function changeRealName(Request $request)
    {
        $real_name = $request->input('real_name');
        $user = $request->user();
        try {
            if (empty($real_name)) {
                throw  new LogicException('请填写您的姓名');
            }
            $reg = "/^[\x{4e00}-\x{9fa5}]{2,15}(\·[\x{4e00}-\x{9fa5}]{3,16}){0,1}$/u";
            if (!preg_match($reg, $real_name)) {
                throw new LogicException('姓名只能输入中文和 · ');
            }
            if (mb_strlen($real_name) > 50) {
                throw new LogicException('姓名不能超过30个字符');
            }
            $user->real_name = $real_name;
            $user->save();
        } catch (LogicException $le) {
            throw $le;
        }
        return response()->json(['code' => 0, 'msg' => '修改成功']);
    }

    /**
     * 修改性别
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\LogicException
     */
    public function changeUserSex(Request $request)
    {
        $sex = $request->input('sex');
        $user = $request->user();
        try {
            if (!in_array($sex, [0, 1, 2])) {
                throw new LogicException('超出设置范围');
            }
            $user->sex = $sex;
            $user->save();
        } catch (LogicException $le) {
            throw $le;
        }
        return response()->json(['code' => 0, 'msg' => '修改成功']);
    }

    /**
     * 修改生日
     *
     * @param \Illuminate\Http\Request $request
     */
    public function changeUserBirth(Request $request)
    {
        $birth = $request->input('birth');
        $user = $request->user();
        try {
            if (strtotime($birth) > strtotime(date('Y-m-d')) || strtotime($birth) < strtotime(date('Y-m-d', 0))) {
                throw new LogicException('生日格式错误或超出设置范围');
            }
            $birth = date('Y-m-d', strtotime($birth));
            $user->birth = $birth;
            $user->save();
        } catch (LogicException $le) {
            throw $le;
        }
        return response()->json(['code' => 0, 'msg' => '修改成功']);
    }

    /**
     * 修改密码
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeUserPassword(Request $request)
    {
        $password = $request->input('password');
        $new_password = $request->input('new_password');
        $confirm_password = $request->input('confirm_password');
        if ($new_password != $confirm_password) {
            throw new LogicException('两次输入密码不一致');
        }
        $user = $request->user();
        if ($user->verifyPassword($password) == false) {
            throw new LogicException('原密码错误');
        }
        $user->changePassword($new_password);
        return response()->json(['code' => 0, 'msg' => '修改成功']);
    }

    //修改用户手机号
    public function updateUserPhone(Request $request){
        $this->validate($request, [
            'phone' => ['bail', 'required'],
            'verify_code' => ['bail', 'required'],
        ], [
            'phone' => '手机号',
            'verify_code' => '验证码',
        ]);
        $phone = $request->input('phone');//新手机号
        if(preg_match('/^1[3-9]\d{9}$/', $phone)!=1){
            throw new LogicException('手机号格式不合法',0);
        }
        $user = $request->user();

        if (User::STATUS_NORMAL != $user->status) {
            throw new LogicException('账户异常',0);
        }

        if (!VerifyCode::check($user->phone, $request->verify_code, VerifyCode::TYPE_UPDATE_USER_PHONE)) {
            throw new LogicException('无效的验证码',0);
        }

        if($user->phone == $phone){
            throw new LogicException('更换的手机号不能跟旧手机号相同',0);
        }

        $phoneUser = User::where('phone', $phone)->first();
        if ($phoneUser != ''){
            throw new LogicException('该手机号已被其他的用户使用，请更换其他手机号',0);
        }

        $user->phone = $phone;
        if($user->save()){
            return response()->json(['code' => 1, 'msg' => '修改成功']);
        }else{
            return response()->json(['code' => 0, 'msg' => '修改失败']);
        }

    }




}
