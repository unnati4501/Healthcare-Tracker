<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreatenotificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('create-notification');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $logoMax = config('zevolifesettings.fileSizeValidations.user_notification.logo', 2048);
        return [
            'title'              => 'required|min:2|max:100',
            'logo'               => [
                "sometimes",
                "nullable",
                "image",
                "mimes:jpg,jpeg,png",
                "max:{$logoMax}",
                Rule::dimensions()->minWidth(512)->minHeight(512)->ratio(1 / 1.0),
            ],
            'schedule_date_time' => 'required|date_format:Y-m-d H:i:s',
            'members'            => 'required',
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
            'logo.max'        => 'The :attribute may not be greater than 2MB.',
            'logo.dimensions' => 'The uploaded image does not match the given dimension and ratio.',
        ];
    }
}
