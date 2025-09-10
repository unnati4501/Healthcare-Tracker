<div class="card-inner">
    <div class="row">
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('name', trans('podcast.form.labels.title')) }}
                {{ Form::text('name', old('name', ($data->title ?? null)), ['class' => 'form-control', 'placeholder' => trans('podcast.form.placeholder.name'), 'id' => 'name', 'autocomplete' => 'off']) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('podcast_subcategory', trans('podcast.form.labels.subcategory_name')) }}
                {{ Form::select('podcast_subcategory', $subcategory, ($data->sub_category_id ?? null), ['class' => 'form-control select2','id'=>'podcast_subcategory', 'placeholder' => trans('podcast.form.placeholder.podcast_subcategory'), 'data-placeholder' => trans('podcast.form.placeholder.podcast_subcategory')] ) }}
            </div>
        </div>
        <div class="col-md-6 col-xl-4 type_wrappers" id="audio_wrapper">
            <div class="form-group">
                {{ Form::label('track_audio', trans('podcast.form.labels.track_file')) }}
                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ trans('podcast.form.tooltip.track') }}">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
                <div class="custom-file">
                    {{ Form::file('track_audio', ['class' => 'custom-file-input form-control', 'id' => 'track_audio', 'accept' => 'audio/*']) }}
                    {{ Form::label('track_audio', ((!empty($data) && !empty($data->getFirstMediaUrl('track'))) ? $data->getFirstMedia('track')->name : trans('podcast.form.placeholder.choose_file')), ["class" => "custom-file-label"]) }}
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('podcast_logo', trans('podcast.form.labels.logo')) }}
                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('podcasts.logo') }}">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
                <div class="custom-file custom-file-preview">
                    {{ Form::file('podcast_logo', ['class' => 'custom-file-input form-control', 'id' => 'podcast_logo', 'data-width' => config('zevolifesettings.imageConversions.podcasts.logo.width'), 'data-height' => config('zevolifesettings.imageConversions.podcasts.logo.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.podcasts.logo'), 'data-previewelement' => '#podcast_logo_preview', 'accept' => 'image/*'])}}
                    <label class="file-preview-img" for="podcast_logo_preview">
                        <img id="podcast_logo_preview" src="{{ ($data->logo_url ?? asset('assets/dist/img/boxed-bg.png')) }}" width="200"/>
                    </label>
                    {{ Form::label('podcast_logo', ((!empty($data) && !empty($data->getFirstMediaUrl('logo'))) ? $data->getFirstMedia('logo')->name : trans('podcast.form.placeholder.choose_file')), ["class" => "custom-file-label"]) }}
                </div>
            </div>
        </div>
        
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('duration', trans('podcast.form.labels.duration')) }}
                {{ Form::text('duration', old('duration', ($data->duration ?? null)), ['class' => 'form-control numeric', 'placeholder' => trans('podcast.form.placeholder.duration'), 'id' => 'duration', 'readonly' => true]) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('health_coach', trans('podcast.form.labels.health_coach')) }}
                {{ Form::select('health_coach', $healthcoach, ($data->coach_id ?? null), ['class' => 'form-control select2','id' => 'health_coach', 'placeholder' => trans('podcast.form.placeholder.health_coach'), 'data-placeholder' => trans('podcast.form.placeholder.health_coach')] ) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('goal_tag', trans('podcast.form.labels.goal_tag')) }}
                {{ Form::select('goal_tag[]', $goalTags, ($goal_tags ?? null), ['class' => 'form-control select2','id' => 'goal_tag', 'multiple' => true, 'data-placeholder' => trans('podcast.form.placeholder.goal_tag'), 'data-allow-clear' => 'false']) }}
            </div>
        </div>
        @if($roleGroup == 'zevo')
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('tag', trans('podcast.form.labels.tag')) }}
                {{ Form::select('tag', $tags, ($data->tag_id ?? null), ['class' => 'form-control select2', 'id' => 'tag', 'placeholder' => trans('podcast.form.placeholder.tag'), 'data-placeholder' => trans('podcast.form.placeholder.tag'), 'data-allow-clear' => 'true']) }}
            </div>
        </div>
        @endif
    </div>
</div>
<div class="card-inner">
    <h3 class="card-inner-title">
        {{ trans('podcast.title.visibility') }}
    </h3>
    <div id="setPermissionList" class="tree-multiselect-box">
        <select id="podcast_company" name="podcast_company[]" multiple="multiple" class="form-control">
            @foreach($companies as $rolekey => $rolevalue)
                @foreach($rolevalue['companies'] as $key => $value)
                    @foreach($value['location'] as $locationKey => $locationValue)
                        @foreach($locationValue['department'] as $departmentKey => $departmentValue)
                            @foreach($departmentValue['team'] as $teamKey => $teamValue)
                                <option value="{{ $teamValue['id'] }}" data-section="{{ $rolevalue['roleType'] }}/{{$value['companyName']}}/{{$locationValue['locationName']}}/{{$departmentValue['departmentName']}}" {{ (!empty($podcast_companies) && in_array($teamValue['id'], $podcast_companies))? 'selected' : ''   }} >{{ $teamValue['name'] }}</option>
                            @endforeach
                        @endforeach
                    @endforeach
                @endforeach
            @endforeach
        </select>
        <span id="podcast_company-error" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">{{ trans('labels.podcast.company_selection') }}</span>
    </div>
</div>
<audio class="d-none" id="audio_duration">
</audio>