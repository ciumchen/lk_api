<?php

namespace App\Http\Requests;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;

class AdvertRequest extends FormRequest
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
            'uid'         => ['bail', 'required'],
            'packagename' => ['bail', 'required'],
            'type'        => ['bail', 'required'],
            'unique_id'   => ['bail', 'required'],
            'award'       => ['bail', 'required'],
            'sign'        => ['bail', 'required'],
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
            'uid'         => '用户id',
            'packagename' => '渠道包名',
            'type'        => '场景',
            'unique_id'   => '唯一标识',
            'award'       => '奖励数量',
            'sign'        => '签名',
        ];
    }
}
