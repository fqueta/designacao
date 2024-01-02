<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
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
            //'post_name' => ['required'],
            // 'post_title' => ['required'],
            //'post_content' => ['required'],
        ];
    }
    public function messages()
    {
        return [
            'post_name.required' => __('O campo Slug é obrigatório'),
            'post_title.required' => __('O campo Nome é obrigatório'),
        ];
    }
}
