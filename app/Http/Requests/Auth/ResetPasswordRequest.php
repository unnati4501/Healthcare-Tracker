<?php declare (strict_types = 1);

namespace App\Http\Requests\Auth;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class ResetPasswordRequest
 *
 * @package App\Http\Requests\Auth
 */
class ResetPasswordRequest extends FormRequest
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

        return [
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|string|min:8|max:20|confirmed|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*\-+,{}\'<=>`~_.:;]).{6,}$/',
        ];
    }

    /**
     * Custom error messages
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'password.regex' => "Your password must be between 8 and 20 characters, should contain atleast 1 uppercase, 1 lowercase, 1 numeric and 1 special character.",
        ];
    }
}
