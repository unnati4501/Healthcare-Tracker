<?php
declare (strict_types = 1);

namespace App\Http\Requests\Api\V11;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class ChangePasswordRequest extends FormRequest
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
        $xDeviceOs      = strtolower(request()->header('X-Device-Os', ""));

        $validation['password'] = 'required|string|min:8|max:20|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*\-+,{}\'<=>`~_.:;]).{6,}$/';

        if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
            $validation['currentPassword'] = 'required|current_password';
        }

        return $validation;
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
            'currentPassword.current_password' => 'Current Password is incorrect',
        ];
    }

    /**
     * Get the proper failed validation response for the request.
     *
     * @param  array  $errors
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $errors = [];
        foreach ($validator->errors()->getMessages() as $field => $errorArr) {
            $errors[] = [
                'field'   => $field,
                'message' => $errorArr,
            ];
        }
        $response = new JsonResponse([
            'code'    => 422,
            'message' => 'The given data is invalid',
            'errors'  => $errors,
        ], 422);

        throw new \Illuminate\Validation\ValidationException($validator, $response);
    }
}
