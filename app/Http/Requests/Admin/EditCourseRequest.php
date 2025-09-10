<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use App\Models\Course;
use App\Models\User;
use App\Models\CategoryTags;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EditCourseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('update-course');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $logoMax                   = config('zevolifesettings.fileSizeValidations.course.logo', 2048);
        $trailerAudioMax           = config('zevolifesettings.fileSizeValidations.course.trailer_audio', (100 * 1024));
        $trailerAudioBackgroundMax = config('zevolifesettings.fileSizeValidations.course.trailer_audio_background', 2048);
        $trailerVideoMax           = config('zevolifesettings.fileSizeValidations.course.trailer_video', (100 * 1024));
        $headerImageMax            = config('zevolifesettings.fileSizeValidations.course.header_image', 2048);
        
        $headerImageRules          = array();
        $headerImage = function () {
            $thisId             = $this->route('course')->id;
            $getCouseData       = Course::find($thisId);
            if (!empty($getCouseData)){
                return $getCouseData->header_image_name;
            } 
        };
        $headerImageRules = [
            empty($headerImage()) ? "required" : "nullable",
            "image",
            "mimes:jpg,jpeg,png",
            "max:{$headerImageMax}",
            Rule::dimensions()->minWidth(800)->minHeight(800)->ratio(1 / 1.0),
        ];
        $rules = [
            'logo'                            => [
                "sometimes",
                "nullable",
                "image",
                "mimes:jpg,jpeg,png",
                "max:{$logoMax}",
                Rule::dimensions()->minWidth(1280)->minHeight(640)->ratio(2 / 1),
            ],
            'sub_category'                    => 'required|integer',
            'title'                           => 'required|alpha_num_spaces|min:2|max:75',
            'health_coach'                    => 'required|integer|exists:' . User::class . ',id',
            'trailer_audio'                   => "sometimes|nullable|mimetypes:audio/mpeg,audio/mp3,audio/m4a,audio/x-m4a|max:{$trailerAudioMax}",
            'trailer_audio_background'        => [
                "sometimes",
                "nullable",
                "image",
                "mimes:jpg,jpeg,png",
                "max:{$trailerAudioBackgroundMax}",
                Rule::dimensions()->minWidth(640)->minHeight(1280)->ratio(1 / 2),
            ],
            'trailer_audio_background_portal' => [
                "sometimes",
                "nullable",
                "image",
                "mimes:jpg,jpeg,png",
                "max:{$trailerAudioBackgroundMax}",
                Rule::dimensions()->minWidth(1280)->minHeight(640)->ratio(2 / 1),
            ],
            'trailer_video'                   => "sometimes|nullable|mimetypes:video/mp4|max:{$trailerVideoMax}",
            'trailer_youtube'                 => "required_if:trailer_type,3|nullable|url|youtube_url",
            'trailer_vimeo'                   => "required_if:trailer_type,4|nullable|url|vimeo_url",
            'track_vimeo'                     => [
                "sometimes",
                "nullable",
                "image",
                "mimes:jpg,jpeg,png",
                "max:{$trailerAudioBackgroundMax}",
                Rule::dimensions()->minWidth(1280)->minHeight(640)->ratio(2 / 1),
            ],
            'description'                     => 'required',
            'goal_tag'                        => 'array|max:3',
            'masterclass_company'             => 'required',
            'masterclass_company.*'           => 'integer',
            'tag'                             => 'nullable|integer|exists:' . CategoryTags::class . ',id',
        ];
        $rules['header_image']     = $headerImageRules;

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
            'logo.max'                                    => 'The :attribute may not be greater than 2MB.',
            'trailer_audio.max'                           => 'The :attribute may not be greater than 100MB.',
            'trailer_audio_background.max'                => 'The :attribute may not be greater than 2MB.',
            'trailer_audio_background_portal.max'         => 'The :attribute may not be greater than 2MB.',
            'trailer_video.max'                           => 'The :attribute may not be greater than 100MB.',
            'trailer_type.required_if'                    => 'The :attribute field is required',
            'trailer_audio.required_if'                   => 'The :attribute field is required',
            'trailer_audio_background.required_if'        => 'The :attribute field is required',
            'trailer_audio_background_portal.required_if' => 'The :attribute field is required',
            'trailer_video.required_if'                   => 'The :attribute field is required',
            'masterclass_company.required'                => 'The :attribute is required',
            'trailer_youtube.required_if'                 => 'The :attribute field is required',
            'trailer_vimeo.required_if'                   => 'The :attribute field is required',
            'trailer_vimeo.vimeo_url'                     => 'The vimeo link is not valid url',
            'logo.dimensions'                             => 'The uploaded image does not match the given dimension and ratio.',
            'trailer_audio_background.dimensions'         => 'The uploaded image does not match the given dimension and ratio.',
            'trailer_audio_background_portal.dimensions'  => 'The uploaded image does not match the given dimension and ratio.',
            'track_vimeo.required_if'                     => 'The :attribute field is required',
            'track_vimeo.dimensions'                      => 'The uploaded image does not match the given dimension and ratio.',
            'header_image.dimensions'                     => 'The uploaded image does not match the given dimension and ratio.',
        ];
    }

    public function attributes(): array
    {
        return [
            'sub_category'                    => 'category',
            'trailer_audio'                   => 'audio',
            'trailer_audio_background'        => 'mobile audio background',
            'trailer_audio_background_portal' => 'portal audio background',
            'trailer_video'                   => 'video',
            'masterclass_company'             => 'company selection',
            'trailer_youtube'                 => 'Youtube link',
            'health_coach'                    => 'Author',
            'trailer_vimeo'                   => 'Vimeo link',
            'track_vimeo'                     => 'Vimeo thumbnail',
        ];
    }
}
