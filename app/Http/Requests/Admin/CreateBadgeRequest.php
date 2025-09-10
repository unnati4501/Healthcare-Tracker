<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateBadgeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('create-badge');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $payload       = $this->input();
        $rules         = array();
        $logoMax       = config('zevolifesettings.fileSizeValidations.badge.logo', 2048);
        $rules['name'] = 'required|alpha_num_spaces|min:2|max:50';
        $rules['logo'] = [
            'required',
            'image',
            'mimes:jpg,jpeg,png',
            "max:{$logoMax}",
            Rule::dimensions()->minWidth(320)->minHeight(320)->ratio(1 / 1.0),
        ];
        $rules['info']           = 'max:200';
        $rules['badge_target']   = 'required_if:badge_type,challenge|required_if:badge_type,general';
        $rules['excercise_type'] = 'required_if:badge_target,4';
        if (isset($payload['badge_type']) && $payload['badge_type'] != 'ongoing') {
            $rules['target_values'] = 'required|integer|max:100000000';
        }
        $rules['no_of_days'] = 'required_if:will_badge_expire,yes|sometimes|nullable|integer|max:365';

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
            'excercise_type.required_if' => "The Excercise Type field is required when Badge Target is Exercises.",
            'uom.required_if'            => "The uom field is required when Badge Target is Exercises.",
            'logo.max'                   => 'The :attribute may not be greater than 2MB.',
            'logo.dimensions'            => 'The uploaded image does not match the given dimension and ratio.',
        ];
    }
}
