<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\models\SubCategory;
use App\Models\CategoryTags;

class CreateWebinarRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('add-webinar');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $coverMax       = config('zevolifesettings.fileSizeValidations.webinar.cover', 2048);
        $trackMax       = config('zevolifesettings.fileSizeValidations.webinar.track', (100 * 2560));
        $headerImageMax = config('zevolifesettings.fileSizeValidations.webinar.header_image', 2048);

        $rules                     = array();
        $rules['title']            = 'required|alpha_num_spaces|min:2|max:50';
        $rules['webinar_category'] = 'required|integer|exists:sub_categories,id';
        $rules['webinar_type']     = 'required';
        $rules['duration']         = 'required|integer|min:1|max:1440';
        $rules['author']           = 'required|integer|exists:' . User::class . ',id';
        $rules['webinar_cover']    = [
            "required",
            "image",
            "mimes:jpg,jpeg,png",
            "max:{$coverMax}",
            Rule::dimensions()->minWidth(1280)->minHeight(640)->ratio(2 / 1),
        ];
        $rules['webinar_file']    = "required_if:webinar_type,1|nullable|mimetypes:video/mp4|max:{$trackMax}";
        $rules['webinar_youtube'] = "required_if:webinar_type,2|nullable|url|youtube_url";
        $rules['webinar_vimeo']   = "required_if:webinar_type,3|nullable|url|vimeo_url";
        $rules['goal_tag']        = 'array|max:3';
        $rules['webinar_company'] = 'required';
        $rules['webinar_company.*'] = 'integer';
        $rules['header_image'] = [
            "required",
            "image",
            "mimes:jpg,jpeg,png",
            "max:{$headerImageMax}",
            Rule::dimensions()->minWidth(800)->minHeight(800)->ratio(1 / 1.0),
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
            'webinar_file.required_if'    => 'The :attribute is required',
            'webinar_file.mimetypes'      => 'The :attribute must be a file of type: MP4.',
            'webinar_file.max'            => 'The :attribute may not be greater than 250MB.',
            'webinar_cover.max'           => 'The :attribute may not be greater than 2MB.',
            'webinar_youtube.required_if' => 'The :attribute field is required.',
            'webinar_company.required'    => 'The :attribute is required',
            'webinar_vimeo.required_if'   => 'The :attribute field is required',
            'webinar_vimeo.vimeo_url'     => 'The vimeo link is not valid url',
            'webinar_cover.dimensions'    => 'The uploaded image does not match the given dimension and ratio.',
            'header_image.dimensions'     => 'The uploaded image does not match the given dimension and ratio.',
        ];
    }

    public function attributes()
    {
        return [
            'webinar_youtube'     => 'youtube',
            'webinar_subcategory' => 'webinar category',
            'webinar_company'     => 'company selection',
            'webinar_vimeo'       => 'Vimeo link',
        ];
    }
}
