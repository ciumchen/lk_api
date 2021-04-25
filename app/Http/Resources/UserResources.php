<?php

namespace App\Http\Resources;

use App\Models\AssetsType;
use App\Models\Order;
use App\Models\User;
use App\Services\AssetsService;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResources extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        //我的消费
        $mySpent = Order::where("status", Order::STATUS_SUCCEED)
            ->where("uid",$this->id)
            ->sum("price");

        //iets余额
        $user = User::find($this->id);
        $assetsType = AssetsType::where("assets_name", AssetsType::DEFAULT_ASSETS_NAME)->first();
        $balance = AssetsService::getBalanceData($user, $assetsType);
        $encourageType = AssetsType::where("assets_name", AssetsType::DEFAULT_ASSETS_ENCOURAGE)->first();
        $encourage = AssetsService::getBalanceData($user, $encourageType);
        return [
            'id' => $this->id,
            'phone' => $this->phone,
            'role' => $this->role,
            'username' => (string) $this->username,
            'avatar' => (string) $this->avatar,
            'lk' => rtrim_zero(format_decimal($this->lk)),
            'business_lk' => format_decimal($this->business_lk),
            'return_lk' => rtrim_zero(format_decimal($this->return_lk)),
            'business_integral' => format_decimal($this->business_integral),
            'integral' => rtrim_zero(format_decimal($this->integral)),
            'return_integral' => rtrim_zero(format_decimal(bcadd($this->return_integral, $this->return_business_integral, 8))),
            'my_spent' => rtrim_zero(format_decimal($mySpent)),
            'amount' => rtrim_zero($balance->amount ?? 0),
            'encourage' => rtrim_zero($encourage->amount ?? 0),
            'freeze_amount' => rtrim_zero($balance->freeze_amount ?? 0),
            'share_url' => env('HTTP_URL')."/register?invite_code={$this->code_invite} 邀请注册，获得更多奖励"
        ];
    }
}
