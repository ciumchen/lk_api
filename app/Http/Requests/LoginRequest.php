<?php

namespace App\Http\Requests;

use App\Rules\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LoginRequest extends FormRequest
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
    public function rules()
    {

        return [
            'phone' => ['bail', 'required', new PhoneNumber()],
            'password' => ['required_if:driver,password'],
            'verify_code' => ['required_if:driver,verify_code'],
            'driver' => ['bail', 'required', Rule::in(['Password', 'VerifyCode'])],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'password.required_if' => '密码不能为空',
            'verify_code.required_if' => '验证码不能为空',
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
            'driver' => '登录方式',
            'phone' => '手机号',
            'password' => '密码',
            'verify_code' => '验证码',
        ];
    }
}
