<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EditDTBannerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('edit-dt-banners');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $bannerImageMax           = config('zevolifesettings.fileSizeValidations.company.banner_image', 2048);
        
        return [
            'banner_image'        => [
                "sometimes",
                "nullable",
                "image",
                "mimes:jpg,jpeg,png",
                "max:{$bannerImageMax}",
                Rule::dimensions()->minWidth(640)->minHeight(640)->ratio(1 / 1.0),
            ],
            'description'    => 'required',
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
            'banner_image.required'        => 'This field is required',
            'banner_image.dimensions'      => 'The uploaded image does not match the given dimension and ratio.',
            'description.required'         => 'This field is required',
        ];
    }

    public function attributes(): array
    {
        return [];
    }
}
