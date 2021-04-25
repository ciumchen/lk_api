<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Rules\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterPost extends FormRequest
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
            'phone' => ['bail', 'required', new PhoneNumber(), Rule::unique(User::class, 'phone')],
            'password' => ['bail', 'required', 'string'],
            'verify_code' => ['bail', 'required', 'string'],
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
