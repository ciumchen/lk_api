<?php

namespace App\Http\Requests;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderRequest extends FormRequest
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

            'phone' => ['bail', 'required', 'exists:users,phone'],
            'ratio' => ['bail', 'required', Rule::in(Setting::getManySetting('business_rebate_scale'))],
//            'name' => ['bail', 'required'],
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
            'ratio' => '让利比例',
            'phone' => '买家手机号',
//            'name' => '商品名称',
        ];
    }
}
