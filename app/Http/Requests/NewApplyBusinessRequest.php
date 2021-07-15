<?php

namespace App\Http\Requests;

use App\Rules\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NewApplyBusinessRequest extends FormRequest
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
            'img' => ['bail', 'required'],//营业执照图片
            'img2' => ['bail', 'required'],//门店照图片
            'img_just' => ['bail', 'required'],//身份证正面照
            'img_back' => ['bail', 'required'],//身份证反面照
//            'img_hold' => ['bail', 'required'],
//            'img_details' => ['bail', 'required'],
            'contact_number' => ['bail', 'required', new PhoneNumber()],//联系方式
            'address' => ['bail', 'required'],//商家详细地址
            'category_id' => ['bail', 'required', 'exists:business_category,id'],//店铺类别
            'start_time' => ['bail', 'required'],//
            'end_time' => ['bail', 'required'],
            'province' => ['bail', 'required', 'exists:city_data,code'],//省
            'city' => ['bail', 'required', 'exists:city_data,code'],//市
            'district' => ['bail', 'required', 'exists:city_data,code'],//区
            'name' => ['bail', 'required'],//商店名称
            'main_business' => ['bail', 'required'],//主营业务
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
            'img' => '营业执照',
            'img2' => '商家头图',
            'img_just' => '身份证正面照',
            'img_back' => '身份证反面照',
//            'img_hold' => '身份证手持照',
//            'img_details' => '商家详情照',
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
