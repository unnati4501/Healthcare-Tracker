<?php
declare (strict_types = 1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

/**
 * Class SendMessageToGroupRequest
 *
 * @package App\Http\Requests\Auth
 */
class SendMessageToGroupRequest extends FormRequest
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
            'lastMessageId' => 'sometimes|nullable|integer',
            'parentId'      => 'sometimes|nullable|integer',
            'message'       => 'required',
        ];

        if ($version >= 6) {
            $rules = [
                'lastMessageId' => 'sometimes|nullable|integer',
                'parentId'      => 'sometimes|nullable|integer',
                'type'          => ['required', Rule::in(['message', 'image'])],
                'message'       => 'required_if:type,message',
                'image'         => 'required_if:type,image|image|mimes:jpg,jpeg,png|max:5120',
            ];
        }

        if ($version >= 9) {
            $rules = [
                'lastMessageId' => 'sometimes|nullable|integer',
                'parentId'      => 'sometimes|nullable|integer',
                'type'          => ['required', Rule::in(['message', 'image'])],
                'message'       => 'required_if:type,message',
                'imageUrl'      => 'required_if:type,image',
                'imageName'     => 'required_if:type,image',
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
            'image.max' => 'The image may not be greater than 2MB.',
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
