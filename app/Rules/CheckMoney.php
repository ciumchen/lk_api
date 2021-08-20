<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CheckMoney implements Rule
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
        if(preg_match("/^[1-9][0-9]*$/" ,$value/100)){
            return 1;
        }else{
            return 0;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return ':attribute必须是100的倍数';
    }
}
