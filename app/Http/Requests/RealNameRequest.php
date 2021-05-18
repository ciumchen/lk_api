<?php

namespace App\Http\Requests;

use App\Rules\IdCard;
use Illuminate\Foundation\Http\FormRequest;

class RealNameRequest extends FormRequest
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
            'id_card' => ['bail', 'required', new IdCard()],
            'name' => ['bail', 'required'],
            'id_card_img' => ['bail', 'required'],
            'id_card_people_img' => ['bail', 'required'],
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
            'id_card' => '身份证',
            'name' => '姓名',
            'id_card_img' => '身份证照片',
            'id_card_people_img' => '手持身份证照片',
        ];
    }
}
