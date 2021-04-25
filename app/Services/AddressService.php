<?php

namespace App\Services;

use App\Exceptions\LogicException;
use App\Models\Address;
use Illuminate\Support\Facades\DB;

class AddressService
{
    /**绑定地址
     * @param $address
     * @param $uid
     * @return bool
     */
    public function bindAddress($address, $uid)
    {
        if (42 != strlen($address)) {
            throw new LogicException('地址格式错误，请仔细核对');
        }

        if (Address::where("uid",$uid)->count() > 0)
        {
            throw new LogicException('您已绑定地址，请勿重复绑定');
        }

        if (Address::where('address', $address)->count() > 0) {
            throw new LogicException('该地址已绑定');
        }

        $userAddress = new Address();
        $userAddress->uid = $uid;
        $userAddress->address = $address;
        if(!$userAddress->save())
        {
            throw new LogicException('绑定失败，请稍后再试');
        }

        return true;
    }
}
