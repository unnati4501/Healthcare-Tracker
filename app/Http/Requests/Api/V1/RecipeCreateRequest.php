<?php declare (strict_types = 1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;

/**
 * Class RecipeCreateRequest
 *
 * @package App\Http\Requests\Auth
 */
class RecipeCreateRequest extends FormRequest
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
            'title'        => 'required|max:50',
            'calories'     => 'required|numeric|between:1,99999',
            'cooking_time' => 'required|numeric',
            'serves'       => 'required|numeric|between:1,999',
            'directions'   => 'required|description',
            'ingredients'  => 'required',
            'nutritions'   => 'required',
        ];

        if ($version >= 4) {
            $rules['subcategories'] = 'required';
        } else {
            $rules['categories'] = 'required';
        }

        if ($version >= 3) {
            $rules['image']   = 'required';
            $rules['image.*'] = 'image|mimes:jpg,jpeg,png|max:5120|filecount:image,3';
        } else {
            $rules['image'] = 'required|image|mimes:jpg,jpeg,png|max:5120';
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
        $messages = [];
        $version  = getApiVersion();
        if ($version >= 3) {
            $messages['image.*.image']     = 'The image field must be an image.';
            $messages['image.*.mimes']     = 'The image feild must be a file of type: jpg, jpeg, png.';
            $messages['image.*.max']       = 'The image field may not be greater than 5MB.';
            $messages['image.*.filecount'] = 'The image field cannot be more than 3.';
        } else {
            $messages['image.max'] = 'The :attribute field may not be greater than 5MB.';
        }
        return $messages;
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
