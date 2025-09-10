<?php
declare (strict_types = 1);

namespace App\Http\Requests\Api\V12;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * Class UpdateTrackedExerciseRequest
 *
 * @package App\Http\Requests\Api\V12
 */
class UpdateTrackedExerciseRequest extends FormRequest
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
        $routeImageMax = config('zevolifesettings.fileSizeValidations.exercise.logo', 2048);
        return [
            'routeImage' => "sometimes|nullable|image|mimes:jpg,jpeg,png|max:{$routeImageMax}",
            'duration'   => 'required|numeric',
            'distance'   => 'required|numeric',
            'calories'   => 'required|numeric',
            'startAt'    => 'required',
            'endAt'      => 'required',
        ];
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
