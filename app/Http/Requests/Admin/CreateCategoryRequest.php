<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('create-category');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $payload       = $this->input();
        $logoMax       = config('zevolifesettings.fileSizeValidations.category.logo', 2048);
        $rules         = array();
        $rules['name'] = ['required', 'regex:/(^[A-Za-z ]+$)+/', 'min:2', 'max:50',
            Rule::unique('categories')
                ->where(function ($query) use ($payload) {
                    return $query->where('name', @$payload['name']);
                })];
        $rules['description'] = 'sometimes|nullable|max:200';
        $rules['logo']        = ['required', 'image', 'mimes:jpg,jpeg,png', "max:{$logoMax}"];

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
            'name.required' => trans('labels.category.validation.name_required'),
            'name.regex'    => trans('labels.category.validation.name_regex'),
            'name.unique'   => trans('labels.category.validation.name_unique'),
            'logo.max'      => 'The :attribute may not be greater than 2MB.',
        ];
    }
}
