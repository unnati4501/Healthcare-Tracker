<?php declare (strict_types = 1);

namespace App\Http\Requests\Api\V3;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;

/**
 * Class JoinPersonalChallengeRequest
 *
 * @package App\Http\Requests\Api\V3
 */
class JoinPersonalChallengeRequest extends FormRequest
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
        return [
            'startDate'      => 'required',
            'reminderAt'     => 'required_if:frequencyType,daily',
            'frequencyType'  => 'required',
            'fromTime'       => 'required_if:frequencyType,hourly',
            'toTime'         => 'required_if:frequencyType,hourly',
            'inEvery'        => 'required_if:frequencyType,hourly',
            'isRecursive'    => 'boolean',
            'recursiveCount' => 'integer',
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
