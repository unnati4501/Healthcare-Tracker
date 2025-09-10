<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateMeditationCatRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('create-meditation-category');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $logoMax       = config('zevolifesettings.fileSizeValidations.user.logo', 2048);
        $rules         = array();
        $rules['name'] = ['required', 'alpha_spaces', 'min:2', 'max:50'];
        $rules['logo'] = ['required', 'image', 'mimes:jpg,jpeg,png', "max:{$logoMax}"];

        return $rules;
    }

    /**
     * Custom error messages
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'logo.max' => 'The :attribute may not be greater than 2MB.',
        ];
    }
}
