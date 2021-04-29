<?php

namespace App\Http\Controllers\API\Auth;

use App\Exceptions\LogicException;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterPost;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserData;
use App\Models\VerifyCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Exception;
use PDOException;
class RegisterController extends Controller
{
    /**注册用户
     * @param RegisterPost $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */
    public function __invoke(RegisterPost $request){
        if($request->invite_code){

            $inviter = User::where('code_invite',$request->invite_code)->first();
//            if(!$inviter)
//                throw new LogicException('无效的邀请码');
        }else{
            $inviter = null;
        }
        if(!VerifyCode::check($request->phone, $request->verify_code, VerifyCode::TYPE_REGISTER))
            throw new LogicException('无效的验证码');


        $this->register($request, $inviter);
        return response()->json(['code'=>0, 'msg'=>'注册成功']);

    }

    /**写入用户数据
     * @param $request
     * @param $inviter
     * @throws Exception
     */
    public function register($request, $inviter = null){
        try {

            DB::transaction(function () use ($request, $inviter) {

                if(!$inviter){
                    $pShareUid = Setting::getSetting('p_share_uid')??0;
//                    if($pShareUid <= 0)
//                        throw new LogicException('默认邀请人未配置');

                    $inviter = $pShareUid;
                }else{
                    $inviter = $inviter->id;
                }
                $user = User::create([
                    'phone' => $request->phone,
                    'invite_uid' => $inviter,
                    'register_ip' => request_ip(),
                ]);

                //创建密码
                $user->changePassword($request->password);

                UserData::create([
                    'uid' => $user->id,
                ]);

            });

        }catch (PDOException $e) {
            report($e);
            throw new LogicException('注册失败，请重试');
        } catch (Exception $e) {
            throw $e;
        }

    }
}
