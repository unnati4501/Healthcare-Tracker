<?php
declare (strict_types = 1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use \Illuminate\Contracts\Validation\Validator;
use \Illuminate\Validation\ValidationException;

/**
 * Class EditProfileRequest
 *
 * @package App\Http\Requests\Auth
 */
class EditProfileRequest extends FormRequest
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
        $logoMax   = config('zevolifesettings.fileSizeValidations.user.logo', 2048);
        $coverMax  = config('zevolifesettings.fileSizeValidations.user.cover', 2048);
        $gender    = array_keys(config('zevolifesettings.gender'));
        $gender    = implode(',', $gender);

        $rules = [
            'firstName'    => 'required|min:2|max:50',
            'lastName'     => 'required|min:2|max:50',
            'dob'          => 'required|date_format:Y-m-d',
            'gender'       => "required|in:{$gender}",
            'password'     => 'sometimes|nullable|min:8',
            'profileImage' => "sometimes|nullable|image|mimes:jpg,jpeg,png|max:{$logoMax}",
            'coverImage'   => "sometimes|nullable|image|mimes:jpg,jpeg,png|max:{$coverMax}",
            'location'     => 'sometimes|nullable|string|max:50',
        ];

        if ($xDeviceOs === config('zevolifesettings.PORTAL')) {
            $rules['profileImage'] = 'sometimes|nullable';
        }

        if ($version >= 15 && $xDeviceOs != config('zevolifesettings.PORTAL')) {
            $rules['location_id']   = 'required';
            $rules['department_id'] = 'required';
            $rules['team_id']       = 'required';
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
            'profileImage.max' => 'The profile image may not be greater then 2MB.',
            'coverImage.max'   => 'The cover image may not be greater than 2MB.',
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
