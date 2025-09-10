<?php
declare (strict_types = 1);

namespace App\Http\Requests\Api\V26;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use \Illuminate\Contracts\Validation\Validator;
use \Illuminate\Validation\ValidationException;

/**
 * Class ContactUsRequest
 *
 * @package App\Http\Requests\Auth
 */
class ContactUsRequest extends FormRequest
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
    public function rules(Request $request)
    {
        $xDeviceOs = $request->header('X-Device-Os', '');
        $rules     = [
            'name'          => 'required|min:2|max:40',
            'email'         => 'required|email_simple|max:255',
            'description'   => 'required',
            'attachment'    => 'sometimes|nullable|mimes:jpg,jpeg,png,doc,pdf'
        ];
        if ($xDeviceOs === config('zevolifesettings.PORTAL')) {
            $rules['attachment'] = 'sometimes|nullable';
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
