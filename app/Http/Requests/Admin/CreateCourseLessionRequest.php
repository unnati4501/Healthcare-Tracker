<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateCourseLessionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('manage-course-modules');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $audioMax           = config('zevolifesettings.fileSizeValidations.course.trailer_audio', (100 * 1024));
        $audioBackgroundMax = config('zevolifesettings.fileSizeValidations.course.logo', 2048);
        $videoMax           = config('zevolifesettings.fileSizeValidations.course.trailer_video', (100 * 1024));
        $logoMax            = config('zevolifesettings.fileSizeValidations.course_lession.logo', 2048);

        return [
            'title'                   => "required|min:2|max:50",
            'lesson_type'             => "required",
            'audio'                   => "required_if:lesson_type,1|nullable|mimetypes:audio/mpeg,audio/mp3,audio/m4a,audio/x-m4a|max:{$audioMax}",
            'audio_background'        => [
                "required_if:lesson_type,1",
                "nullable",
                "image",
                "mimes:jpg,jpeg,png",
                "max:{$audioBackgroundMax}",
                Rule::dimensions()->minWidth(640)->minHeight(1280)->ratio(1 / 2),
            ],
            'audio_background_portal' => [
                "required_if:lesson_type,1",
                "nullable",
                "image",
                "mimes:jpg,jpeg,png",
                "max:{$audioBackgroundMax}",
                Rule::dimensions()->minWidth(1280)->minHeight(640)->ratio(2 / 1),
            ],
            'logo' => [
                "required",
                "image",
                "mimes:jpg,jpeg,png",
                "max:{$logoMax}",
                Rule::dimensions()->minWidth(800)->minHeight(800)->ratio(1 / 1.0),
            ],
            'video'                   => "required_if:lesson_type,2|nullable|mimetypes:video/mp4|max:{$videoMax}",
            'youtube'                 => "required_if:lesson_type,3|nullable|url|youtube_url",
            'description'             => "required_if:lesson_type,4|nullable",
            'vimeo'                   => "required_if:lesson_type,5|nullable|url|vimeo_url",
            'duration'                => "required|integer|min:1|max:1440",
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
            'audio.max'                           => 'The :attribute may not be greater than 100MB.',
            'audio_background.max'                => 'The :attribute may not be greater than 2MB.',
            'audio_background_portal.max'         => 'The :attribute may not be greater than 2MB.',
            'video.max'                           => 'The :attribute may not be greater than 100MB.',
            'audio.required_if'                   => 'The :attribute field is required',
            'audio_background.required_if'        => 'The :attribute field is required',
            'audio_background_portal.required_if' => 'The :attribute field is required',
            'video.required_if'                   => 'The :attribute field is required',
            'youtube.required_if'                 => 'The :attribute field is required',
            'description.required_if'             => 'The :attribute field is required',
            'vimeo.required_if'                   => 'The :attribute field is required',
            'vimeo.vimeo_url'                     => 'The vimeo link is not valid url',
            'audio_background.dimensions'         => 'The uploaded image does not match the given dimension and ratio.',
            'audio_background_portal.dimensions'  => 'The uploaded image does not match the given dimension and ratio.',
            'logo.dimensions'                     => 'The uploaded image does not match the given dimension and ratio.',
        ];
    }

    public function attributes(): array
    {
        return [
            'audio'                   => 'audio',
            'audio_background'        => 'mobile audio background',
            'audio_background_portal' => 'portal audio background',
            'video'                   => 'video ',
            'youtube'                 => 'youtube link',
            'description'             => 'description',
            'vimeo'                   => 'Vimeo link',
        ];
    }
}
