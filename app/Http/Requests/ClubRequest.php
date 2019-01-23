<?php

namespace App\Http\Requests;

use Dingo\Api\Http\FormRequest;

class ClubRequest extends FormRequest
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
            'name' => 'required|unique:name|max:255',
            'email' => 'email|max:255|unique:clubs',
            'phone' => 'required|max:15',
        ];
    }
}
