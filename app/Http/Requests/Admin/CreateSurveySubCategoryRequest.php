<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateSurveySubCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('create-survey-sub-category');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $logoMax               = config('zevolifesettings.fileSizeValidations.surveycategory.logo', 2048);
        $payload               = $this->input();
        $rules                 = array();
        $rules['category']     = 'required';
        $rules['display_name'] = ['required', 'min:2', 'max:50',
            Rule::unique('zc_sub_categories')
                ->where(function ($query) use ($payload) {
                    return $query->where('category_id', @$payload['category'])
                        ->where('display_name', @$payload['display_name']);
                })];
        $rules['logo'] = [
            "sometimes",
            "nullable",
            "image",
            "mimes:jpg,jpeg,png",
            "max:{$logoMax}",
            Rule::dimensions()->minWidth(200)->minHeight(200)->ratio(1 / 1.0),
        ];

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
            'display_name.required' => 'The subcategory name field is required.',
            'display_name.min'      => 'The name must be at least :min characters.',
            'display_name.max'      => 'The name may not be greater than :max characters.',
            'display_name.regex'    => 'Only Letter and Space are allowed.',
            'display_name.unique'   => 'Subcategory already exists.',
            'logo.dimensions'       => 'The uploaded image does not match the given dimension and ratio.',
        ];
    }
}
