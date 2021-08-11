<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RealNameAuthRequest extends FormRequest
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
            'uid' =>       ['bail', 'required'],
            'img_just' => ['bail', 'required'],
            'img_back' => ['bail', 'required'],
            'username' => ['bail', 'required'],
            'user_num' => ['bail', 'required'],
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
            'uid' => '用户uid',
            'img_just' => '身份证正面照',
            'img_back' => '身份证反面照',
            'username' => '用户名',
            'user_num' => '身份证号码',
        ];
    }
}
