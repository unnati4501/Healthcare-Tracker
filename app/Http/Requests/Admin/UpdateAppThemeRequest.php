<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAppThemeRequest extends FormRequest
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
        $theme    = $this->route('theme');
        $themeMax = config('zevolifesettings.fileSizeValidations.app-theme.theme', 2048);
        return [
            'name'  => ['required', 'min:1', 'max:20', "unique:app_themes,name,{$theme->id}"],
            'theme' => ['sometimes', 'nullable', 'file', 'mimetypes:application/json,text/plain', "max:{$themeMax}"],
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
            'theme.mimes' => 'The file must be a JSON.',
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
