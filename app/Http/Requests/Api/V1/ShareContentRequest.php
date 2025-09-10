<?php
declare (strict_types = 1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;

/**
 * Class EditProfileRequest
 *
 * @package App\Http\Requests\Auth
 */
class ShareContentRequest extends FormRequest
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
    public function rules(): array
    {
        $version = getApiVersion();

        $rules = [
            'groupId'   => 'required|exists:groups,id',
            'modelId'   => 'required|integer',
            'modelType' => 'required|string|in:feed,course,meditation,recipe,masterclass',
        ];

        if ($version >= 8) {
            $rules = [
                'groupIds'  => 'required|array',
                'modelId'   => 'required|integer',
                'modelType' => 'required|string|in:feed,course,meditation,recipe,masterclass',
            ];
        }

        if ($version >= 12) {
            $rules = [
                'groupIds'  => 'required|array',
                'modelId'   => 'required|integer',
                'modelType' => 'required|string|in:feed,course,meditation,recipe,masterclass,webinar',
            ];
        }

        if ($version >= 17) {
            $rules = [
                'groupIds'  => 'required|array',
                'modelId'   => 'required|integer',
                'modelType' => 'required|string|in:feed,course,meditation,recipe,masterclass,webinar,badge',
            ];
        }

        if ($version >= 36) {
            $rules = [
                'groupIds'  => 'required|array',
                'modelId'   => 'required|integer',
                'modelType' => 'required|string|in:feed,course,meditation,recipe,masterclass,webinar,badge,podcast',
            ];
        }

        if ($version >= 41) {
            $rules = [
                'groupIds'  => 'required|array',
                'modelId'   => 'required|integer',
                'modelType' => 'required|string|in:feed,course,meditation,recipe,masterclass,webinar,badge,podcast,short',
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
        return [

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
