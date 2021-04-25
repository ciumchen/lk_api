<?php

namespace App\Http\Requests;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
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
            //'uid' => ['bail', 'required'],
            'payChannel' => ['bail', 'required'],
            'money' => ['bail', 'required'],
            'number' => ['bail', 'required'],
            'goodsTitle' => ['bail', 'required'],
            'goodsDesc' => ['bail', 'required'],
            'deviceInfo' => ['bail', 'required'],
            'description' => ['bail', 'required'],
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
            //'uid' => '用户id',
            'payChannel' => '支付类型',
            'money' => '商品金额',
            'number' => '商品数量',
            'goodsTitle' => '商品标题',
            'goodsDesc' => '商品内容',
            'deviceInfo' => '设备信息',
            'description' => '来源类型',
        ];
    }
}
