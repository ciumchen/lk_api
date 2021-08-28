<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GwkDhDefaultAuthRequest extends FormRequest
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
            'order_no' => ['bail', 'required'],
            'money' => ['bail', 'required'],
            'mobile' => ['bail', 'required'],
            'oid' => ['bail', 'required'],
            'password' => ['bail', 'required'],
            'type' => ['bail', 'required'],
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
            'order_no' => '订单号',
            'money' => '金额',
            'mobile' => '手机',
            'oid' => '订单id',
            'password' => '支付密码',
            'type' => '兑换类型',
        ];
    }
}
