<?php

namespace App\Http\Requests;

use App\Rules\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
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
            'verify_code' => ['bail', 'required', 'string'],
            'password' => ['bail', 'required', 'string', 'min:6'],
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
            'phone' => '手机号',
            'password' => '密码',
            'verify_code' => '验证码',
        ];
    }
}
