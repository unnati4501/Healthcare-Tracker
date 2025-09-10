<?php declare (strict_types = 1);

namespace App\Http\Requests\Api\V21;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

/**
 * Class UpdatePersonalChallengeRequest
 *
 * @package App\Http\Requests\Api\V3
 */
class UpdatePersonalChallengeRequest extends FormRequest
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
        $logoMax = config('zevolifesettings.fileSizeValidations.personalChallenge.logo', 2048);
        $rules   = array();

        $rules['logo'] = [
            'sometimes',
            'nullable',
            'image',
            'mimes:jpg,jpeg,png',
            "max:{$logoMax}"
        ];
        $rules['name']          = 'required|max:100';
        $rules['description']   = 'required|max:500';
        $rules['duration']      = 'required|integer|max:365';
        $rules['target_value']  = 'required_if:challengetype,challenge';
        if ($this->input('type') == 'streak') {
            $rules['task'] = 'required|max:50';
        }
        $rules['isRecursive']    = 'required|in:true,false';
        $rules['recursiveCount'] = 'integer';
        $rules['imageId']        = 'sometimes|nullable|integer|exists:challenge_image_library,id';

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
            'logo.max' => 'The logo may not be greater than 5MB.'
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
