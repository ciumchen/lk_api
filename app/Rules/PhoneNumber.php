<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class PhoneNumber implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return bool
     */
    public function passes($attribute, $value)
    {
//        return preg_match('/^1[3-9]\d{9}$/', $value);
//        /0\d{2,3}-\d{7,8}/
//        $boole1 = preg_match('/^1[3-9]\d{9}$/', $value);//手机号
//        $boole2 = preg_match('/^([0-9]{3,4}-)?[0-9]{7,8}$/', $value);//座机号
//        if ($boole1||$boole2){
//            if ($boole1){
//                return $boole1;
//            }elseif ($boole2){
//                return $boole2;
//            }
//
//        }else{
//            return false;
//        }

        return preg_match('/^([0-9]{3,4}-)?[0-9]{7,8}$/', $value);//座机号

    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return ':attribute 格式不正确.';
    }
}
