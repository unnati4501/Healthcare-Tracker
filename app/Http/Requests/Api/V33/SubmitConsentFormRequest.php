<?php
declare (strict_types = 1);

namespace App\Http\Requests\Api\V33;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class SubmitConsentFormRequest extends FormRequest
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
        $version = getApiVersion();
        $payload  = $this->input();
        $rules =  [
            'email'         => 'required|email_simple',
            'ws_id'         => 'required',
            'name'          => ['required', 'regex:/(^([^0-9<>%$#@!*()_]*))+/', 'min:2', 'max:50'],
            'date'          => 'required',
        ];

        if ($version >= 38 && !empty($payload['type']) && $payload['type'] == 1) {
            $rules['fullname']      = ['required', 'regex:/(^([^0-9<>%$#@!*()_]*))+/', 'min:2', 'max:100'];
            $rules['address']       = ['required', 'regex:/(^([^<>%$#!*_]*))+/', 'min:2', 'max:200'];
            $rules['contact_no']    = ['required', 'regex:/^[0-9+]+$/', 'min:6', 'max:14'];
            $rules['relation']      = 'required';
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
            'contact_no.required'   => 'The contact number field is required',
            'contact_no.regex'      => 'Please enter valid contact number',
            'contact_no.min'        => 'The contact number may not be less than 6 characters',
            'contact_no.max'        => 'The contact number may not be greater than 14 characters',
            'fullname.required'     => 'The name field is required',
            'fullname.regex'        => 'Please enter valid name',
            'fullname.max'          => 'The name may not be greater than 100 characters',
            'address.regex'         => 'Please enter valid address.',
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
