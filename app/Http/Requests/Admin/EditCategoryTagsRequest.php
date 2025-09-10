<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EditCategoryTagsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('edit-category-tags');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $payload       = $this->input();
        $rules         = [];
        $rules['name'] = ['required', 'regex:/(^[A-Za-z ]+$)+/', 'custom_max_length:50',
            Rule::unique('category_tags')
                ->where(function ($query) use ($payload) {
                    return $query
                        ->where('id', '!=', $this->tag->id)
                        ->where('category_id', $this->tag->category_id)
                        ->where('name', @$payload['name']);
                })];

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
            'name.required'          => 'The tag name field is required.',
            'name.regex'             => 'Only Letter and Space are allowed.',
            'name.unique'            => 'Tag name is already exists.',
            'name.custom_max_length' => 'The tag name may not be greater than 50 characters.',
        ];
    }
}
