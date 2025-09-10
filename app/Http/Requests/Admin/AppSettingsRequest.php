<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AppSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('update-app-settings');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $app_settings = config('zevolifesettings.app_settings');

        $rules = array();

        foreach ($app_settings as $key => $value) {
            if (!empty($value['validation'])) {
                $rules[$key] = $value['validation'];
            }
        }

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
            'splash_image_url.max' => 'The :attribute may not be greater than 2MB.',
        ];
    }
}
