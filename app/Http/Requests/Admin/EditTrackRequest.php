<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use App\Models\MeditationTrack;
use App\Models\CategoryTags;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EditTrackRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('update-meditation-library');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $coverMax           = config('zevolifesettings.fileSizeValidations.meditation_tracks.cover', 2048);
        $backgroundMax      = config('zevolifesettings.fileSizeValidations.meditation_tracks.background', 2048);
        $trackMax           = config('zevolifesettings.fileSizeValidations.meditation_tracks.track', (100 * 1024));
        $headerImageMax     = config('zevolifesettings.fileSizeValidations.meditation_tracks.background', 2048);
        $headerImageRules   = array();
        $headerImage = function () {
            $thisId         = $this->route('track')->id;
            $getTrackData   = MeditationTrack::find($thisId);
            if (!empty($getTrackData)){
                return $getTrackData->header_image_name;
            } 
        };
        $headerImageRules = [
            empty($headerImage()) ? "required" : "nullable",
            "image",
            "mimes:jpg,jpeg,png",
            "max:{$headerImageMax}",
            Rule::dimensions()->minWidth(800)->minHeight(800)->ratio(1 / 1.0),
        ];

        $rules                      = array();
        $rules['name']              = 'required|alpha_num_spaces|min:2|max:50';
        $rules['track_subcategory'] = 'required|integer|exists:sub_categories,id';
        $rules['duration']     = 'required|integer';
        $rules['health_coach'] = 'required';
        $rules['track_cover']  = [
            "sometimes",
            "nullable",
            "image",
            "mimes:jpg,jpeg,png",
            "max:{$coverMax}",
            Rule::dimensions()->minWidth(640)->minHeight(1280)->ratio(1 / 2),
        ];
        $rules['track_background'] = [
            "sometimes",
            "nullable",
            "image",
            "mimes:jpg,jpeg,png",
            "max:{$backgroundMax}",
            Rule::dimensions()->minWidth(640)->minHeight(1280)->ratio(1 / 2),
        ];
        $rules['track_background_portal'] = [
            "sometimes",
            "nullable",
            "image",
            "mimes:jpg,jpeg,png",
            "max:{$backgroundMax}",
            Rule::dimensions()->minWidth(1280)->minHeight(640)->ratio(2 / 1),
        ];
        $rules['track_audio']        = "sometimes|nullable|mimetypes:audio/mpeg,audio/mp3,audio/m4a,audio/x-m4a|max:{$trackMax}";
        $rules['track_video']        = "sometimes|nullable|mimetypes:video/mp4|max:{$trackMax}";
        $rules['track_youtube']      = "required_if:track_type,3|nullable|url|youtube_url";
        $rules['goal_tag']           = 'array|max:3';
        $rules['audio_type']         = "required_if:track_type,1|nullable";
        $rules['meditation_company'] = 'required';
        $rules['meditation_company.*'] = 'integer';
        $rules['header_image']       = $headerImageRules;
        $rules['tag']               = 'nullable|integer|exists:' . CategoryTags::class . ',id';
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
            'track_cover.max'                    => 'The :attribute may not be greater than 2MB.',
            'track_background.max'               => 'The :attribute may not be greater than 2MB.',
            'track_background_portal.max'        => 'The :attribute may not be greater than 2MB.',
            'track_audio.max'                    => 'The :attribute may not be greater than 100MB.',
            'track_video.max'                    => 'The :attribute may not be greater than 100MB.',
            'track_youtube.required_if'          => 'The :attribute field is required.',
            'track_audio.mimetypes'              => 'The :attribute must be a file of type: MP3.',
            'track_video.mimetypes'              => 'The :attribute must be a file of type: MP4.',
            'duration.between'                   => 'The :attribute must be between 1 second to 1 hour.',
            'meditation_company.required'        => 'The :attribute is required',
            'track_cover.dimensions'             => 'The uploaded image does not match the given dimension and ratio.',
            'track_background.dimensions'        => 'The uploaded image does not match the given dimension and ratio.',
            'track_background_portal.dimensions' => 'The uploaded image does not match the given dimension and ratio.',
            'header_image.dimensions'            => 'The uploaded image does not match the given dimension and ratio.',
        ];
    }

    public function attributes()
    {
        return [
            'name'                    => 'track name',
            'duration'                => 'track duration',
            'track_subcategory'       => 'category',
            'track_audio'             => 'audio track file',
            'track_video'             => 'video track file',
            'track_youtube'           => 'youtube',
            'meditation_company'      => 'company selection',
            'track_background'        => 'mobile track background ',
            'track_background_portal' => 'portal track background ',
            'health_coach'            => 'wellbeing specialist',
        ];
    }
}
