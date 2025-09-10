<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EditEAPIntroductionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('support-introduction');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules   = array();
        $user    = auth()->user();
        $role    = getUserRole($user);

        if ($role->group == 'zevo') {
            $rules['introduction'] = 'required|introduction:1000';
        } else {
            $rules['introduction'] = 'introduction:1000';
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
        return [
            'introduction.required'     => 'Please enter the introductory text',
            'introduction.introduction' => 'Introductory text may not be greater than 1000 characters',
        ];
    }
}
