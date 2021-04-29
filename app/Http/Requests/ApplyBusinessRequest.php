<?php

namespace App\Http\Requests;

use App\Rules\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApplyBusinessRequest extends FormRequest
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
            'img' => ['bail', 'required'],
//            'phone' => ['bail', 'required', new PhoneNumber()],
            'name' => ['bail', 'required'],
            'address' => ['bail', 'required'],
            'work' => ['bail', 'required'],
//            'img2' => ['bail', 'required'],
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
            'phone' => '联系电话',
            'name' => '联系人',
            'address' => '商家地址',
            'work' => '主营业务',
//            'img2' => '门店图片',
        ];
    }
}
