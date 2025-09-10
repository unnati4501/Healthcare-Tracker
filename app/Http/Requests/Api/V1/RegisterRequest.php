<?php
declare (strict_types = 1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use \Illuminate\Contracts\Validation\Validator;
use \Illuminate\Validation\ValidationException;

/**
 * Class RegisterRequest
 *
 * @package App\Http\Requests\Auth
 */
class RegisterRequest extends FormRequest
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
    public function rules(Request $request): array
    {
        $version   = getApiVersion();
        $xDeviceOs = $request->header('X-Device-Os', '');
        $gender    = array_keys(config('zevolifesettings.gender'));
        $gender    = implode(',', $gender);
        $rules     = [
            'firstName' => 'required|min:2|max:50',
            'lastName'  => 'required|min:2|max:50',
            'email'     => 'required|email_simple|max:255',
            'password'  => 'required_without:socialId|sometimes|nullable|min:8',
            'gender'    => "required|in:{$gender}",
            'dob'       => 'required|date_format:Y-m-d',
            'height'    => 'required|integer|max:1000000',
            'weight'    => 'required|numeric|between:0,1000',
            'socialId'  => 'required_without:password',
            'goals'     => 'array|max:3',
        ];

        if ($version >= 15 && $xDeviceOs != config('zevolifesettings.PORTAL')) {
            $rules['location_id']   = 'required';
            $rules['department_id'] = 'required';
            $rules['team_id']       = 'required';
        }

        if ($version >= 21 && $xDeviceOs == config('zevolifesettings.PORTAL')) {
            $rules['location_id']   = 'required';
            $rules['department_id'] = 'required';
        } elseif ($version >= 21 && $xDeviceOs != config('zevolifesettings.PORTAL')) {
            $rules['location_id']   = 'required';
            $rules['department_id'] = 'required';
            $rules['team_id']       = 'required';
        }

        if ($version >= 29 && $xDeviceOs != config('zevolifesettings.PORTAL')) {
            $rules['password'] = 'nullable';
            $rules['socialId'] = 'nullable';
        }

        if ($version >= 31) {
            $rules['password'] = 'nullable';
            $rules['socialId'] = 'nullable';
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
        ];
    }

    /**
     * Get the proper failed validation response for the request.
     *
     * @param  array  $errors
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function failedValidation(Validator $validator)
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

        throw new ValidationException($validator, $response);
    }
}
