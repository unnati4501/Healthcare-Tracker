<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EditBadgeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('update-badge');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $logoMax       = config('zevolifesettings.fileSizeValidations.badge.logo', 2048);
        $rules         = array();
        $rules['name'] = 'required|alpha_num_spaces|min:2|max:50';
        $rules['logo'] = [
            'sometimes',
            'nullable',
            'image',
            'mimes:jpg,jpeg,png',
            "max:{$logoMax}",
            Rule::dimensions()->minWidth(320)->minHeight(320)->ratio(1 / 1.0),
        ];
        $rules['info'] = 'max:200';

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
            'logo.max'        => 'The :attribute may not be greater than 2MB.',
            'logo.dimensions' => 'The uploaded image does not match the given dimension and ratio.',
            // 'excercise_type.required_if' => "The Excercise Type field is required when Badge Target is Exercises.",
            // 'uom.required_if' => "The uom field is required when Badge Target is Exercises."
        ];
    }
}
