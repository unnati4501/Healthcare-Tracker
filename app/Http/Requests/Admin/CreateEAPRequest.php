<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateEAPRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('create-support');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $role        = getUserRole();
        $logoMax     = config('zevolifesettings.fileSizeValidations.eap.logo', (2 * 1024));
        $companyData = auth()->user()->company()->first();

        $rules = [
            'logo'        => [
                "required",
                "image",
                "mimes:jpg,jpeg,png",
                "max:{$logoMax}",
                Rule::dimensions()->minWidth(512)->minHeight(512)->ratio(1 / 1.0),
            ],
            'title'       => 'required|max:60',
            'telephone'   => 'required|numeric|digits_between:1,24',
            'email'       => 'sometimes|nullable|email_simple|max:50',
            'website'     => ['nullable', 'max:50', 'regex:/^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/)?[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/'],
            'description' => 'required|introduction:750',
            'locations'   => 'nullable',
            'department'  => 'nullable',
        ];

        if ($role->group == 'zevo' || ($role->group == 'reseller' && $companyData->parent_id == null)) {
            $rules['eap_company'] = 'required';
        }

        return $rules;
    }

    /**
     * Custom error messages
     *
     * @return array
     */
    public function messages(): array
    {
        $messages                             = [];
        $messages['telephone.digits_between'] = 'The :attribute field may not be greater than 25 characters.';
        $messages['description.introduction'] = 'The :attribute field may not be greater than 750 characters.';
        $messages['logo.max']                 = 'The :attribute field may not be greater than 2MB.';
        $messages['eap_company.required']     = 'The company selection is required';
        $messages['logo.dimensions']          = 'The uploaded image does not match the given dimension and ratio.';
        return $messages;
    }
}
