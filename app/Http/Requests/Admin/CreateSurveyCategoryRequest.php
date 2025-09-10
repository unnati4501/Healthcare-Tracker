<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateSurveyCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('create-survey-category');
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
        $rules['display_name'] = ['required', 'min:2', 'max:50',
            Rule::unique('zc_categories')
                ->where(function ($query) use ($payload) {
                    return $query->where('display_name', $payload['display_name']);
                })];
        $rules['logo'] = [
            "sometimes",
            "nullable",
            "image",
            "mimes:jpg,jpeg,png",
            "max:{$logoMax}",
            Rule::dimensions()->minWidth(200)->minHeight(200)->ratio(1 / 1.0),
        ];
        $rules['goal_tag'] = 'array|max:3';
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
            'display_name.required' => trans('labels.surveycategory.validation.name_required'),
            'display_name.min'      => trans('labels.surveycategory.validation.min'),
            'display_name.max'      => trans('labels.surveycategory.validation.max'),
            'display_name.regex'    => trans('labels.surveycategory.validation.name_regex'),
            'display_name.unique'   => trans('labels.surveycategory.validation.name_unique'),
            'goal_tag.max'          => trans('labels.surveycategory.validation.goal_tag'),
            'logo.dimensions'       => 'The uploaded image does not match the given dimension and ratio.',
        ];
    }
}
