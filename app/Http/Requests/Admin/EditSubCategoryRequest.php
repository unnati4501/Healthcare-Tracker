<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EditSubCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('update-sub-category');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $modulesWithLimit = config('zevolifesettings.subcategoryModulesCharLimit');
        $payload          = $this->input();
        $category         = $this->route('category');
        $maxLimit         = $modulesWithLimit[$category->category_id];
        $logoMax          = config('zevolifesettings.fileSizeValidations.subcategories.logo', 2048);
        $backgroundMax    = config('zevolifesettings.fileSizeValidations.subcategories.background', 2048);
        $rules         = array();
        $rules['name'] = ['required', 'regex:/(^[A-Za-z ]+$)+/', "custom_max_length:{$maxLimit}",
            Rule::unique('sub_categories')
                ->where(function ($query) use ($payload, $category) {
                    return $query
                        ->where('category_id', @$payload['category'])
                        ->where('name', @$payload['name'])
                        ->where('id', '!=', $category->id);
                })];
            $rules['logo']  = [
                "sometimes",
                "nullable",
                "image",
                "mimes:jpg,jpeg,png,svg",
                "max:{$logoMax}",
            ];
            $rules['background'] = [
                "sometimes",
                "nullable",
                "image",
                "mimes:jpg,jpeg,png,svg",
                "max:{$backgroundMax}"
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
        $modulesWithLimit = config('zevolifesettings.subcategoryModulesCharLimit');
        $category         = $this->route('category');
        $maxLimit         = $modulesWithLimit[$category->category_id];

        return [
            'name.required'          => 'The sub-category name field is required.',
            'name.regex'             => trans('labels.category.validation.name_regex'),
            'name.unique'            => 'Sub-category already exists.',
            'name.custom_max_length' => "The sub-category may not be greater than {$maxLimit} characters.",
        ];
    }
}
