<div class="card-inner">
    <div class="row">
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('name', trans('meditationtrack.form.labels.title')) }}
                {{ Form::text('name', old('name', ($data->title ?? null)), ['class' => 'form-control', 'placeholder' => trans('meditationtrack.form.placeholder.name'), 'id' => 'name', 'autocomplete' => 'off']) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('track_subcategory', trans('meditationtrack.form.labels.subcategory_name')) }}
                {{ Form::select('track_subcategory', $subcategory, ($data->sub_category_id ?? null), ['class' => 'form-control select2','id'=>'track_subcategory', 'placeholder' => trans('meditationtrack.form.placeholder.track_subcategory'), 'data-placeholder' => trans('meditationtrack.form.placeholder.track_subcategory')] ) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('track_type', trans('meditationtrack.form.labels.track_type')) }}
                {{ Form::select('track_type', config('zevolifesettings.meditationTrackType'), old('track_type', ($data->type ?? null)), ['class' => 'form-control select2', 'id'=>'track_type', 'placeholder' => trans('meditationtrack.form.placeholder.track_type'), 'data-placeholder' => trans('meditationtrack.form.placeholder.track_type'), 'disabled' => $edit]) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('track_cover', trans('meditationtrack.form.labels.cover')) }}
                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('meditation_tracks.cover') }}">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
                <div class="custom-file custom-file-preview">
                    {{ Form::file('track_cover', ['class' => 'custom-file-input form-control', 'id' => 'track_cover', 'data-width' => config('zevolifesettings.imageConversions.meditation_tracks.cover.width'), 'data-height' => config('zevolifesettings.imageConversions.meditation_tracks.cover.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.meditation_tracks.cover'), 'data-previewelement' => '#track_cover_preview', 'accept' => 'image/*'])}}
                    <label class="file-preview-img" for="track_cover_preview">
                        <img id="track_cover_preview" src="{{ ($data->cover_url ?? asset('assets/dist/img/boxed-bg.png')) }}" width="200"/>
                    </label>
                    {{ Form::label('track_cover', ((!empty($data) && !empty($data->getFirstMediaUrl('cover'))) ? $data->getFirstMedia('cover')->name : trans('meditationtrack.form.placeholder.choose_file')), ["class" => "custom-file-label"]) }}
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-4 type_wrappers" id="audio_type_wrapper" style="display: none;">
            <div class="form-group">
                {{ Form::label('audio_type', trans('meditationtrack.form.labels.audio_type')) }}
                <div>
                    <label class="custom-radio" for="music">
                        {{ trans('meditationtrack.form.labels.music') }}
                        {{ Form::radio('audio_type', '1', old('audio_type', (!empty($data) ? (($data->audio_type == 1) ? true : false) : true)), ['class' => 'form-control', 'id' => 'music']) }}
                        <span class="checkmark">
                        </span>
                        <span class="box-line">
                        </span>
                    </label>
                    <label class="custom-radio" for="vocal">
                        {{ trans('meditationtrack.form.labels.vocal') }}
                        {{ Form::radio('audio_type', '2', old('audio_type', (!empty($data) ? (($data->audio_type == 2) ? true : false) : false)), ['class' => 'form-control', 'id' => 'vocal']) }}
                        <span class="checkmark">
                        </span>
                        <span class="box-line">
                        </span>
                    </label>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-4 type_wrappers" id="audio_background_wrapper" style="display: none;">
            <div class="form-group">
                {{ Form::label('track_background', trans('meditationtrack.form.labels.background')) }}
                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('meditation_tracks.background') }}">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
                <div class="custom-file custom-file-preview">
                    {{ Form::file('track_background', ['class' => 'custom-file-input form-control', 'id' => 'track_background', 'data-width' => config('zevolifesettings.imageConversions.meditation_tracks.background.width'), 'data-height' => config('zevolifesettings.imageConversions.meditation_tracks.background.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.meditation_tracks.background'), 'data-previewelement' => '#track_background_preview', 'accept' => 'image/*']) }}
                    <label class="file-preview-img" for="track_background_preview">
                        <img id="track_background_preview" src="{{ ($data->background_url ?? asset('assets/dist/img/boxed-bg.png')) }}" width="200"/>
                    </label>
                    {{ Form::label('track_background', ((!empty($data) && ($data->type == 1) && !empty($data->getFirstMediaUrl('background'))) ? $data->getFirstMedia('background')->name : trans('meditationtrack.form.placeholder.choose_file')), ["class" => "custom-file-label"]) }}
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-4 type_wrappers" id="audio_background_wrapper_portal" style="display: none;">
            <div class="form-group">
                {{ Form::label('track_background_portal', trans('meditationtrack.form.labels.background_portal')) }}
                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('meditation_tracks.background_portal') }}">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
                <div class="custom-file custom-file-preview">
                    {{ Form::file('track_background_portal', ['class' => 'custom-file-input form-control', 'id' => 'track_background_portal', 'data-width' => config('zevolifesettings.imageConversions.meditation_tracks.background_portal.width'), 'data-height' => config('zevolifesettings.imageConversions.meditation_tracks.background_portal.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.meditation_tracks.background_portal'), 'data-previewelement' => '#track_background_portal_preview', 'accept' => 'image/*']) }}
                    <label class="file-preview-img" for="track_background_portal_preview">
                        <img id="track_background_portal_preview" src="{{ ($data->background_portal_url ?? asset('assets/dist/img/boxed-bg.png')) }}" width="200"/>
                    </label>
                    {{ Form::label('track_background_portal', ((!empty($data) && ($data->type == 1) && !empty($data->getFirstMediaUrl('background_portal'))) ? $data->getFirstMedia('background_portal')->name : trans('meditationtrack.form.placeholder.choose_file')), ["class" => "custom-file-label"]) }}
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-4 type_wrappers" id="audio_wrapper" style="display: none;">
            <div class="form-group">
                {{ Form::label('track_audio', trans('meditationtrack.form.labels.track_file')) }}
                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getSizeHelpTooltipText('meditation_tracks.track') }}">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
                <div class="custom-file">
                    {{ Form::file('track_audio', ['class' => 'custom-file-input form-control', 'id' => 'track_audio', 'accept' => 'audio/*']) }}
                    {{ Form::label('track_audio', ((!empty($data) && ($data->type == 1) && !empty($data->getFirstMediaUrl('track'))) ? $data->getFirstMedia('track')->name : trans('meditationtrack.form.placeholder.choose_file')), ["class" => "custom-file-label"]) }}
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-4 type_wrappers" id="video_wrapper" style="display: none;">
            <div class="form-group">
                {{ Form::label('track_video', trans('meditationtrack.form.labels.track_file')) }}
                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getSizeHelpTooltipText('meditation_tracks.track') }}">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
                <div class="custom-file">
                    {{ Form::file('track_video', ['class' => 'custom-file-input form-control', 'id' => 'track_video', 'accept' => 'video/*']) }}
                    {{ Form::label('track_video', ((!empty($data) && ($data->type == 2) && !empty($data->getFirstMediaUrl('track'))) ? $data->getFirstMedia('track')->name : trans('meditationtrack.form.placeholder.choose_file')), ["class" => "custom-file-label"]) }}
                </div>
            </div>
        </div>
        <div class="col-md-12 col-xl-8 type_wrappers" id="youtube_wrapper" style="display: none;">
            <div class="form-group">
                {{ Form::label('track_youtube', trans('meditationtrack.form.labels.youtube')) }}
                <span class="font-16 qus-sign-tooltip" data-placement="top" data-toggle="help-tooltip" title="{{ config('zevolifesettings.youtube_hint_message.message') }}">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
                {{ Form::text('track_youtube', old('track_youtube', ((!empty($data) && ($data->type == 3) && !empty($data->getFirstMedia('track'))) ? config('zevolifesettings.youtubeappurl').$data->getFirstMedia('track')->getCustomProperty('ytid') : null)), ['class' => 'form-control', 'placeholder' => config('zevolifesettings.youtube_hint_message.placeholder'), 'id' => 'track_youtube', 'autocomplete' => 'off']) }}
            </div>
        </div>
        <div class="col-md-12 col-xl-8 type_wrappers" id="vimeo_wrapper" style="display: none;">
            <div class="form-group">
                {{ Form::label('track_vimeo', trans('meditationtrack.form.labels.vimeo')) }}
                <span class="font-16 qus-sign-tooltip" data-placement="top" data-toggle="help-tooltip" title="{{ config('zevolifesettings.vimeo_hint_message.message') }}">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
                {{ Form::text('track_vimeo', old('track_vimeo', (!empty($data) && $data->type == 4 && !empty($data->getFirstMedia('track')) ? config('zevolifesettings.vimeoappurl').$data->getFirstMedia('track')->getCustomProperty('vmid') : '')), ['class' => 'form-control', 'placeholder' => config('zevolifesettings.vimeo_hint_message.placeholder'), 'id' => 'webinar_vimeo', 'autocomplete' => 'off']) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('duration', trans('meditationtrack.form.labels.duration')) }}
                {{ Form::text('duration', old('duration', ($data->duration ?? null)), ['class' => 'form-control numeric', 'placeholder' => trans('meditationtrack.form.placeholder.duration'), 'id' => 'duration', 'readonly' => true]) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('health_coach', trans('meditationtrack.form.labels.health_coach')) }}
                {{ Form::select('health_coach', $healthcoach, ($data->coach_id ?? null), ['class' => 'form-control select2','id' => 'health_coach', 'placeholder' => trans('meditationtrack.form.placeholder.health_coach'), 'data-placeholder' => trans('meditationtrack.form.placeholder.health_coach')] ) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('goal_tag', trans('meditationtrack.form.labels.goal_tag')) }}
                {{ Form::select('goal_tag[]', $goalTags, ($goal_tags ?? null), ['class' => 'form-control select2','id' => 'goal_tag', 'multiple' => true, 'data-placeholder' => trans('meditationtrack.form.placeholder.goal_tag'), 'data-allow-clear' => 'false']) }}
            </div>
        </div>
        @if($roleGroup == 'zevo')
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('tag', trans('meditationtrack.form.labels.tag')) }}
                {{ Form::select('tag', $tags, ($data->tag_id ?? null), ['class' => 'form-control select2', 'id' => 'tag', 'placeholder' => trans('meditationtrack.form.placeholder.tag'), 'data-placeholder' => trans('meditationtrack.form.placeholder.tag'), 'data-allow-clear' => 'true']) }}
            </div>
        </div>
        @endif
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('Header Image', trans('meditationtrack.form.labels.header_image')) }}
                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('meditation_tracks.header_image') }}">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
                <div class="custom-file custom-file-preview">
                    <label class="file-preview-img" for="header_image_preview" style="display: flex;">
                        <img id="header_image_preview" src="{{ ((!empty($data) && !empty($data->getFirstMediaUrl('header_image'))) ? $data->getFirstMediaUrl('header_image') : asset('assets/dist/img/boxed-bg.png')) }}" width="200"/>
                    </label>
                    {{ Form::file('header_image', ['class' => 'custom-file-input form-control', 'id' => 'header_image', 'data-width' => config('zevolifesettings.imageConversions.meditation_tracks.header_image.width'), 'data-height' => config('zevolifesettings.imageConversions.meditation_tracks.header_image.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.meditation_tracks.header_image'), 'autocomplete' => 'off','title'=>'', 'data-previewelement' => '#header_image_preview', 'accept' => 'image/*']) }}
                    {{ Form::label('header_image', ((!empty($data) && !empty($data->getFirstMediaUrl('header_image'))) ? $data->getFirstMedia('header_image')->name : trans('meditationtrack.form.placeholder.choose_file')), ["class" => "custom-file-label"]) }}
                </div>
            </div>
        </div>
    </div>
    
</div>
<div class="card-inner">
    <h3 class="card-inner-title">
        {{ trans('meditationtrack.title.visibility') }}
    </h3>
    <div id="setPermissionList" class="tree-multiselect-box">
        <select id="track_company" name="meditation_company[]" multiple="multiple" class="form-control">
            @foreach($companies as $rolekey => $rolevalue)
                @foreach($rolevalue['companies'] as $key => $value)
                    @foreach($value['location'] as $locationKey => $locationValue)
                        @foreach($locationValue['department'] as $departmentKey => $departmentValue)
                            @foreach($departmentValue['team'] as $teamKey => $teamValue)
                                <option value="{{ $teamValue['id'] }}" data-section="{{ $rolevalue['roleType'] }}/{{$value['companyName']}}/{{$locationValue['locationName']}}/{{$departmentValue['departmentName']}}" {{ (!empty($meditation_companys) && in_array($teamValue['id'], $meditation_companys))? 'selected' : ''   }} >{{ $teamValue['name'] }}</option>
                            @endforeach
                        @endforeach
                    @endforeach
                @endforeach
            @endforeach
        </select>
        <span id="track_company-error" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">{{ trans('labels.meditationtrack.company_selection') }}</span>
    </div>
</div>
<video class="d-none" id="video_duration">
</video>
<audio class="d-none" id="audio_duration">
</audio>