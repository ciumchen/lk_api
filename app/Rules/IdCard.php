<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class IdCard implements Rule
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
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (18 != strlen($value))
            return false;

        $str = substr($value, 0, 17);

        // 加权因子
        $factor = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];

        // 校验码对应值
        $verifyNumbers = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'];

        $checksum = 0;
        for ($i = 0; $i < strlen($str); ++$i) {
            $checksum += substr($str, $i, 1) * $factor[$i];
        }

        $mod = $checksum % 11;

        return $verifyNumbers[$mod] === strtoupper(substr($value, 17, 1));
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return '身份证根式错误';
    }
}
