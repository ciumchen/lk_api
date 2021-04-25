<?php

namespace App\Http\Requests;

use App\Rules\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBusinessDataRequest extends FormRequest
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
            'banners' => ['bail', 'required'],
            'contact_number' => ['bail', 'required', new PhoneNumber()],
            'address' => ['bail', 'required'],
            'category_id' => ['bail', 'required', 'exists:business_category,id'],
            'start_time' => ['bail', 'required'],
            'end_time' => ['bail', 'required'],
            'province' => ['bail', 'required', 'exists:city_data,code'],
            'city' => ['bail', 'required', 'exists:city_data,code'],
            'district' => ['bail', 'required', 'exists:city_data,code'],
            'name' => ['bail', 'required'],
            'main_business' => ['bail', 'required'],
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
            'banners' => '门店图片',
            'contact_number' => '联系电话',
            'address' => '详细地址',
            'category_id' => '商家分类',
            'start_time' => '营业时间',
            'end_time' => '歇业时间',
            'province' => '省份',
            'city' => '市区',
            'district' => '区',
            'name' => '商店名',
            'main_business' => '主营业务',
        ];
    }
}
