<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\models\Category;

class CreateSubCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('create-sub-category');
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
        $category         = isset($payload['category']) ? is_numeric($payload['category']) : 2;
        $maxLimit         = $modulesWithLimit[$category];
        $logoMax          = config('zevolifesettings.fileSizeValidations.subcategories.logo', 2048);
        $backgroundMax    = config('zevolifesettings.fileSizeValidations.subcategories.background', 2048);
        
        $rules             = array();
        $rules['category'] = 'required|integer|exists:' . Category::class . ',id';
        $rules['name']     = ['required', 'regex:/(^[A-Za-z ]+$)+/', "custom_max_length:{$maxLimit}",
            Rule::unique('sub_categories')
                ->where(function ($query) use ($payload) {
                    return $query
                        ->where('category_id', @$payload['category'])
                        ->where('name', @$payload['name']);
                })];
        $rules['logo']  = [
            "required_if:category,1,2,4,7,9,10",
            "image",
            "mimes:jpg,jpeg,png,svg",
            "max:{$logoMax}",
        ];
        $rules['background'] = [
           "required_if:category,1,2,4,7,9,10",
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
        $payload          = $this->input();
        $category         = isset($payload['category']) ? is_numeric($payload['category']) : 2;
        $maxLimit         = $modulesWithLimit[$category];

        return [
            'name.required'          => 'The sub-category field is required.',
            'name.regex'             => trans('labels.category.validation.name_regex'),
            'name.unique'            => 'sub-category already exists.',
            'name.custom_max_length' => "The sub-category may not be greater than {$maxLimit} characters.",
            'background.required_if' => 'The background image is required',
            'logo.required_if'       => 'The logo is required',
        ];
    }
}
