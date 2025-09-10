<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CreateAppThemeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('create-app-theme');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $themeMax = config('zevolifesettings.fileSizeValidations.app-theme.theme', 2048);
        return [
            'name'  => ['required', 'min:1', 'max:20', 'unique:app_themes,name'],
            'theme' => ['required', 'file', 'mimetypes:application/json,text/plain', "max:{$themeMax}"],
        ];
    }

    /**
     * Custom error messages
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'theme.max'   => 'The :attribute may not be greater than 2MB.',
        ];
    }

    /**
     * Custom attributes name
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'name'  => 'theme',
            'theme' => 'json',
        ];
    }
}
