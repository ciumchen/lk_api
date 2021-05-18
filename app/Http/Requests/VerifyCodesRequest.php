<?php

namespace App\Http\Requests;

use App\Models\VerifyCode;
use App\Rules\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VerifyCodesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(){


        return [
            'type' => ['bail', 'required', Rule::in(array_values(VerifyCode::$typeLabels))],
            'phone' => ['bail', 'required', new PhoneNumber()],

        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'type' => '验证码类型',
            'phone' => '手机号',
        ];
    }
}
