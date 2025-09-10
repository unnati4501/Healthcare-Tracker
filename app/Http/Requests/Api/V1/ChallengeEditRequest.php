<?php
declare (strict_types = 1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;

/**
 * Class ChallengeEditRequest
 *
 * @package App\Http\Requests\Auth
 */
class ChallengeEditRequest extends FormRequest
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
        $rules   = [
            'title'       => 'required|min:2|max:50',
            'description' => 'required|max:200',
            'startDate'   => 'required|date',
            'endDate'     => 'required|date|after_or_equal:startDate',
        ];

        if ($version >= 9) {
            $rules['image']   = 'sometimes|nullable|image|mimes:jpg,jpeg,png|max:10240';
            $rules['imageId'] = 'sometimes|nullable|integer|exists:challenge_image_library,id';
        } else {
            $rules['image'] = 'sometimes|nullable|image|mimes:jpg,jpeg,png|max:10240';
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
            'imageId.required_without' => 'The image field is required.',
            'image.required_without'   => 'The image field is required.',
            'image.required'           => 'The image field is required.',
            'imageId.exists'           => 'Invalid image supplied',
        ];
    }

    public function attributes(): array
    {
        return [
            'imageId' => 'image',
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
