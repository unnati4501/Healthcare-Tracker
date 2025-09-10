<?php
declare (strict_types = 1);

namespace App\Http\Requests\Api\V31;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use \Illuminate\Contracts\Validation\Validator;
use \Illuminate\Validation\ValidationException;

/**
 * Class DeleteCronofyScheduleRequest
 *
 * @package App\Http\Requests\Auth
 */
class DeleteCronofyScheduleRequest extends FormRequest
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
        return [
            'reason' => 'sometimes|nullable|min:1|max:1000',
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
            'reason.max' => 'The reason may not be greater than 1000 characters.',
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
