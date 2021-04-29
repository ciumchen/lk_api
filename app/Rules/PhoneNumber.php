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
        $tel1 = preg_match('/^1[3-9]\d{9}$/', $value);
        $tel2 = preg_match('/^(0[0-9]{2,3}(\-)?)?([2-9][0-9]{6,7})+((\-)?[0-9]{1,4})?$/', $value);
        if ($tel1){
            return $tel1;
        }elseif ($tel2){
            return $tel2;
        }else{
            return false;
        }
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
