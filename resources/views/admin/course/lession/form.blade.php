<div class="row">
    <div class="col-lg-6 col-xl-4">
        <div class="form-group">
            {{ Form::label('Logo', trans('masterclass.lesson.form.labels.logo')) }}
            <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('meditation_tracks.header_image') }}">
                <i aria-hidden="true" class="far fa-info-circle text-primary">
                </i>
            </span>
            <div class="custom-file custom-file-preview">
                <label class="file-preview-img" for="logo_preview" style="display: flex;">
                    <img id="logo_preview" src="{{ ((!empty($record) && !empty($record->getFirstMediaUrl('logo'))) ? $record->getFirstMediaUrl('logo') : asset('assets/dist/img/boxed-bg.png')) }}" width="200"/>
                </label>
                {{ Form::file('logo', ['class' => 'custom-file-input form-control', 'id' => 'logo', 'data-width' => config('zevolifesettings.imageConversions.course_lession.logo.width'), 'data-height' => config('zevolifesettings.imageConversions.course_lession.logo.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.course_lession.logo'), 'autocomplete' => 'off','title'=>'', 'data-previewelement' => '#logo_preview', 'accept' => 'image/*']) }}
                {{ Form::label('logo', ((!empty($record) && !empty($record->getFirstMediaUrl('logo'))) ? $record->getFirstMedia('logo')->name : trans('masterclass.lesson.form.placeholder.choose_file')), ["class" => "custom-file-label"]) }}
            </div>
        </div>
    </div>
    <div class="col-lg-6 col-xl-4">
        <div class="form-group">
            {{ Form::label('title', trans('masterclass.lesson.form.labels.title')) }}
            {{ Form::text('title', old('title', ($record->title ?? null)), ['class' => 'form-control', 'placeholder' => trans('masterclass.lesson.form.placeholder.title'), 'id' => 'title', 'autocomplete' => 'off']) }}
        </div>
    </div>
    <div class="col-lg-6 col-xl-4">
        <div class="form-group">
            <div>
                <label class="custom-checkbox no-label">
                    {{ trans('masterclass.lesson.form.labels.auto_progress') }}
                    {{ Form::checkbox('auto_progress', true, old('auto_progress', ($record->auto_progress ?? true)), ['class' => 'form-control', 'id' => 'auto_progress']) }}
                    <span class="checkmark">
                    </span>
                    <span class="box-line">
                    </span>
                </label>
            </div>
        </div>
    </div>
    <div class="col-lg-6 col-xl-4">
        <div class="form-group">
            {{ Form::label('lesson_type', trans('masterclass.lesson.form.labels.lesson_type')) }}
            {{ Form::select('lesson_type', $lessionType, old('lesson_type', ($record->type ?? null)), ['class' => 'form-control select2', 'id' => 'lesson_type', 'placeholder' => trans('masterclass.lesson.form.placeholder.lesson_type'), 'data-placeholder' => trans('masterclass.lesson.form.placeholder.lesson_type'), 'disabled' => $edit] ) }}
        </div>
    </div>

    <div class="col-lg-12" id="main_wrapper" style="display: none;">
        <div class="row">
            <div class="col-lg-12 type_wrappers" id="audio_wrapper" style="display: none;">
                <div class="row">
                    <div class="col-lg-6 col-xl-4">
                        <div class="form-group">
                            {{ Form::label('audio', trans('masterclass.lesson.form.labels.audio')) }}
                            <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getSizeHelpTooltipText('course_lession.audio') }}">
                                <i aria-hidden="true" class="far fa-info-circle text-primary">
                                </i>
                            </span>
                            <div class="custom-file">
                                {{ Form::file('audio', ['class' => 'custom-file-input form-control', 'id' => 'audio']) }}
                            {{ Form::label('audio', ((!empty($record) && !empty($record->getFirstMediaUrl('audio'))) ? $record->getFirstMedia('audio')->name : trans('masterclass.lesson.form.placeholder.choose_file')), ["class" => "custom-file-label"]) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-xl-4">
                        <div class="form-group">
                            {{ Form::label('audio_background', trans('masterclass.lesson.form.labels.audio_background')) }}
                            <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('course_lession.audio_background') }}">
                                <i aria-hidden="true" class="far fa-info-circle text-primary">
                                </i>
                            </span>
                            <div class="custom-file custom-file-preview">
                                <label class="file-preview-img" for="audio_background_preview" style="display: flex;">
                                    <img id="audio_background_preview" src="{{ ((!empty($record) && !empty($record->getFirstMediaUrl('audio_background'))) ? $record->getFirstMediaUrl('audio_background') : asset('assets/dist/img/boxed-bg.png')) }}" width="200"/>
                                </label>
                                {{ Form::file('audio_background', ['class' => 'custom-file-input form-control', 'id' => 'audio_background', 'data-width' => config('zevolifesettings.imageConversions.course_lession.audio_background.width'), 'data-height' => config('zevolifesettings.imageConversions.course_lession.audio_background.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.course_lession.audio_background'), 'autocomplete' => 'off','title'=>'', 'data-previewelement' => '#audio_background_preview']) }}
                                {{ Form::label('audio_background', ((!empty($record) && !empty($record->getFirstMediaUrl('audio_background'))) ? $record->getFirstMedia('audio_background')->name : trans('masterclass.lesson.form.placeholder.choose_file')), ["class" => "custom-file-label"]) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-xl-4">
                        <div class="form-group">
                            {{ Form::label('audio_background_portal', trans('masterclass.lesson.form.labels.audio_background_portal')) }}
                            <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('course_lession.audio_background_portal') }}">
                                <i aria-hidden="true" class="far fa-info-circle text-primary">
                                </i>
                            </span>
                            <div class="custom-file custom-file-preview">
                                <label class="file-preview-img" for="audio_background_portal_preview" style="display: flex;">
                                    <img id="audio_background_portal_preview" src="{{ ((!empty($record) && !empty($record->getFirstMediaUrl('audio_background_portal'))) ? $record->getFirstMediaUrl('audio_background_portal') : asset('assets/dist/img/boxed-bg.png')) }}" width="200"/>
                                </label>
                                {{ Form::file('audio_background_portal', ['class' => 'custom-file-input form-control', 'id' => 'audio_background_portal', 'data-width' => config('zevolifesettings.imageConversions.course_lession.audio_background_portal.width'), 'data-height' => config('zevolifesettings.imageConversions.course_lession.audio_background_portal.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.course_lession.audio_background_portal'), 'autocomplete' => 'off','title'=>'', 'data-previewelement' => '#audio_background_portal_preview']) }}
                                {{ Form::label('audio_background_portal', ((!empty($record) && !empty($record->getFirstMediaUrl('audio_background_portal'))) ? $record->getFirstMedia('audio_background_portal')->name : trans('masterclass.lesson.form.placeholder.choose_file')), ["class" => "custom-file-label"]) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-xl-4 type_wrappers" id="video_wrapper" style="display: none;">
                <div class="form-group">
                    {{ Form::label('video', trans('masterclass.lesson.form.labels.video')) }}
                    <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getSizeHelpTooltipText('course_lession.video') }}">
                        <i aria-hidden="true" class="far fa-info-circle text-primary">
                        </i>
                    </span>
                    <div class="custom-file">
                        {{ Form::file('video', ['class' => 'custom-file-input form-control', 'id' => 'video']) }}
                        {{ Form::label('video', ((!empty($record) && !empty($record->getFirstMediaUrl('video'))) ? $record->getFirstMedia('video')->name : trans('masterclass.lesson.form.placeholder.choose_file')), ["class" => "custom-file-label"]) }}
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-xl-8 type_wrappers" id="youtube_wrapper" style="display: none;">
                <div class="form-group">
                    {{ Form::label('youtube', trans('masterclass.lesson.form.labels.youtube')) }}
                    <span class="font-16 qus-sign-tooltip" data-placement="top" data-toggle="help-tooltip" title="{{ config('zevolifesettings.youtube_hint_message.message') }}">
                        <i aria-hidden="true" class="far fa-info-circle text-primary">
                        </i>
                    </span>
                    {{ Form::text('youtube', old('youtube', ((!empty($record) && !empty($record->getFirstMedia('youtube'))) ? config('zevolifesettings.youtubeappurl').$record->getFirstMedia('youtube')->getCustomProperty('ytid') : null)), ['class' => 'form-control', 'placeholder' => config('zevolifesettings.youtube_hint_message.placeholder'), 'id' => 'youtube']) }}
                </div>
            </div>
            <div class="col-lg-6 col-xl-8 type_wrappers" id="vimeo_wrapper" style="display: none;">
                <div class="form-group">
                    {{ Form::label('vimeo', trans('masterclass.lesson.form.labels.vimeo')) }}
                    <span class="font-16 qus-sign-tooltip" data-placement="top" data-toggle="help-tooltip" title="{{ config('zevolifesettings.vimeo_hint_message.message') }}">
                        <i aria-hidden="true" class="far fa-info-circle text-primary">
                        </i>
                    </span>
                    {{ Form::text('vimeo', old('vimeo', ((!empty($record) && !empty($record->getFirstMedia('vimeo'))) ? config('zevolifesettings.vimeoappurl') . $record->getFirstMedia('vimeo')->getCustomProperty('vmid') : null)), ['class' => 'form-control', 'placeholder' => config('zevolifesettings.vimeo_hint_message.placeholder'), 'id' => 'vimeo']) }}
                </div>
            </div>
            <div class="col-lg-6 col-xl-4">
                <div class="form-group">
                    {{ Form::label('duration', trans('masterclass.lesson.form.labels.duration')) }}
                    <div class="input-group symbol-end">
                    <span class="input-group-text">
                            {{ trans('masterclass.lesson.form.placeholder.minutes') }}
                        </span>    
                    {{ Form::text('duration', old('duration', (isset($record->duration) ? timeToDecimal($record->duration) : null)), ['class' => 'form-control numeric', 'id' => 'duration',]) }}
                        
                    </div>
                </div>
            </div>
            <div class="col-lg-12 col-xl-12 type_wrappers" id="content_wrapper" style="display: none;">
                <div class="form-group">
                    {{ Form::label('description', trans('masterclass.lesson.form.labels.content')) }}
                    {{ Form::textarea('description', old('description', (isset($record->description) ? htmlspecialchars_decode($record->description) : null)), ['class' => 'form-control article-ckeditor', 'id' => 'description', 'data-errplaceholder' => '#description-error-cstm', 'data-formid' => (($edit) ? "#courseLessionEdit" : "#courseLessionAdd"), 'data-upload-path' => route('admin.ckeditor-upload.masterclass-lesson', ['_token' => csrf_token()])]) }}
                    <div>
                        <small>
                            For the best appearance try full screen mode by clicking on button
                            <i class="fas fa-arrows-alt" style="transform: rotate(45deg);">
                            </i>
                            from toolbar.
                        </small>
                    </div>
                    <div id="description-error-cstm">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<audio class="d-none" id="audio_duration">
</audio>
<video class="d-none" id="video_duration">
</video>