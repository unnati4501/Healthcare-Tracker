<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateGroupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('create-group');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $logoMax       = config('zevolifesettings.fileSizeValidations.group.logo', 2048);
        $rules         = array();
        $rules['name'] = ['required', 'min:2', 'max:50'];
        $rules['logo'] = [
            'required',
            'image',
            'mimes:jpg,jpeg,png',
            "max:{$logoMax}",
            Rule::dimensions()->minWidth(512)->minHeight(512)->ratio(1 / 1.0),
        ];
        $rules['category']         = 'required';
        $rules['introduction']     = 'required|max:200';
        $rules['members_selected'] = 'required';

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
            'members_selected.required' => trans('labels.group.group_member_required'),
            'logo.max'                  => 'The :attribute may not be greater than 2MB.',
            'logo.dimensions'           => 'The uploaded image does not match the given dimension and ratio.',
        ];
    }

    public function attributes(): array
    {
        return [
            'category' => 'sub-category',
        ];
    }
}
