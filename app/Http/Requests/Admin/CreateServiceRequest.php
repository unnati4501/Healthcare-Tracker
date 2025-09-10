<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
         return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $logoMax                = config('zevolifesettings.fileSizeValidations.services.logo', 2048);
        $iconMax                = config('zevolifesettings.fileSizeValidations.services.icon', 2048);
        $rules                  = array();
        $rules['name']          = ['required', "regex:/^[a-zA-z0-9 \/.,<>&()+\'\-]+$/", 'unique:services,name', 'max:100'];
        $rules['description']   = ['required', 'regex:/(^([^0-9<>%$#@!*()_]*))+/', 'max:5000'];
        $rules['logo']          = [
            "required",
            "image",
            "mimes:jpg,jpeg,png",
            "max:{$logoMax}",
        ];

        $rules['icon']          = [
            //"required",
            "sometimes",
            "nullable",
            "image",
            "mimes:jpg,jpeg,png",
            "max:{$iconMax}",
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
            'name.regex'        => 'Please enter valid service name',
            'description.regex' => 'Please enter valid description',
            'description.max'   => 'The :attribute may not be greater than 5000 characters',
        ];
    }
}
