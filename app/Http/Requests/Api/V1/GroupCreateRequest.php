<?php
declare (strict_types = 1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class GroupCreateRequest
 *
 * @package App\Http\Requests\Auth
 */
class GroupCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return \true;
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
        $rules = [
            'name'        => 'required|min:2|max:50',
            'image'       => 'required|image|mimes:jpg,jpeg,png|max:10240',
            'description' => 'required|max:200',
            'users'       => 'required|empty_json_data',
        ];

        if ($version >= 35 && $xDeviceOs != config('zevolifesettings.PORTAL')){
            $rules = [
                'name'        => ['required', 'min:2', 'max:100', 'regex:/(^([^<>%#!*()_]*))+$/'],
                'image'       => 'required|image|mimes:jpg,jpeg,png|max:10240',
                'users'       => 'required|empty_json_data',
            ];
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
        return [];
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
