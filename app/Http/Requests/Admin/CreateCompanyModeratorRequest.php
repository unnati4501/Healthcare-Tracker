<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateCompanyModeratorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('add-moderator');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'first_name' => 'required|regex:/(^([^0-9<>%$#@!*()_]*))+/|min:2|max:50',
            'last_name'  => 'required|regex:/(^([^0-9<>%$#@!*()_]*))+/|min:2|max:50',
            'email'      => 'required|email_simple|max:255|unique:users,email',
        ];
    }
}
