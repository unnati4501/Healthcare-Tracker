<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\CategoryTags;

class CreateShortsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('create-shorts');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $trackMax       = config('zevolifesettings.fileSizeValidations.shorts.track', (100 * 1024));
        $headerImageMax = config('zevolifesettings.fileSizeValidations.shorts.header_image', 2048);

        $rules                     = array();
        $rules['title']            = 'required|min:2|max:100';
        $rules['shorts_category']  = 'required|integer';
        $rules['shorts_type']      = 'required';
        $rules['duration']         = 'required|integer|min:1|max:1440';
        $rules['author']           = 'required';
        $rules['video']            = "required_if:shorts_type,1|nullable|mimetypes:video/mp4|max:{$trackMax}";
        $rules['youtube']          = "required_if:shorts_type,2|nullable|url|youtube_url";
        $rules['vimeo']            = "required_if:shorts_type,3|nullable|url";
        $rules['goal_tag']         = 'array|max:3';
        $rules['shorts_companys']  = 'required';
        $rules['shorts_companys.*']  = 'integer';
        $rules['description']      = 'required|description';
        $rules['header_image'] = [
            "required",
            "image",
            "mimes:jpg,jpeg,png",
            "max:{$headerImageMax}",
            Rule::dimensions()->minWidth(1080)->minHeight(1920)->ratio(1080/1920),
        ];
        $rules['tag'] = 'nullable|integer|exists:' . CategoryTags::class . ',id';
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
            'title'                       => 'The :attribute is required!',
            'duration.required'           => 'The :attribute field is required.',
            'tracvideok_file.required_if' => 'The video is required',
            'video.mimetypes'             => 'The video must be a file of type: MP4.',
            'video.max'                   => 'The video may not be greater than 100MB.',
            'youtube.required_if'         => 'The :attribute field is required.',
            'vimeo.required_if'           => 'The :attribute field is required',
            'vimeo.vimeo_url'             => 'The vimeo link is not valid url',
            'header_image.dimensions'     => 'The uploaded image does not match the given dimension and ratio.',
        ];
    }

    public function attributes()
    {
        return [
            'title'              => 'name',
            'youtube'            => 'youtube',
            'shorts_subcategory' => 'short category',
            'shorts_company'     => 'company selection',
            'vimeo'              => 'Vimeo link',
        ];
    }
}
