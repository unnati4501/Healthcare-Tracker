<?php declare (strict_types = 1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class LoginRequest
 *
 * @package App\Http\Requests\Auth
 */
class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $type = Request()->input('type');
        if ($type == '2fa') {
            return [
                'email' => 'required|email|exists:users,email',
                'digit' => 'required_if:type,2fa',
            ];
        } else {
            return [
                'email'    => 'required|email|exists:users,email',
                'password' => 'required_if:type,password',
            ];
        }
    }

    /**
     * Custom error messages
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'password.required_if' => 'The password field is required',
            'digit.required_if'    => 'The OTP field is required',
        ];
    }
}
