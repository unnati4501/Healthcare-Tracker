<div class="col-lg-6 col-xl-4">
    <div class="form-group">
        <label for="logo">{{trans('badge.form.labels.logo')}}</label>
        <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('badge.logo') }}">
            <i aria-hidden="true" class="far fa-info-circle text-primary">
            </i>
        </span>
        <div class="custom-file custom-file-preview">
            <label class="file-preview-img" for="profileImage">
                <img id="previewImg" src="{{ $badgeData->logo ?? asset('assets/dist/img/boxed-bg.png') }}" width="200" height="200" />
            </label>
            {{ Form::file('logo', ['class' => 'custom-file-input form-control', 'id' => 'logo', 'data-width' => config('zevolifesettings.imageConversions.badge.logo.width'), 'data-height' => config('zevolifesettings.imageConversions.badge.logo.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.badge.logo'), 'autocomplete' => 'off','title'=>''])}}
            <label class="custom-file-label" for="logo">
                {{ !empty($badgeData->logo) ? $badgeData->logo_name : trans('badge.form.placeholder.choose_file') }}
            </label>
        </div>
    </div>
</div>
<div class="col-lg-6 col-xl-4">
    <div class="form-group">
        <label for="">{{trans('badge.form.labels.title')}}</label>
        @if(!empty($badgeData->title))
            {{ Form::text('name', old('badge_name',$badgeData->title), ['class' => 'form-control', 'placeholder' => trans('badge.form.placeholder.enter_badge_name'), 'id' => 'badge_name', 'autocomplete' => 'off']) }}
        @else
            {{ Form::text('name', old('badge_name'), ['class' => 'form-control', 'placeholder' => trans('badge.form.placeholder.enter_badge_name'), 'id' => 'badge_name', 'autocomplete' => 'off']) }}
        @endif
    </div>
</div>
<div class="col-lg-6 col-xl-4">
    <div class="form-group">
        {{ Form::label('description', trans('badge.form.labels.info')) }}
        @if(!empty($badgeData->description))
            {!! Form::textarea('info', old('info',$badgeData->description), ['id' => 'info', 'rows' => 5, 'class' => 'form-control','placeholder'=>trans('badge.form.placeholder.enter_details'),'spellcheck'=>'false']) !!}
        @else
            {!! Form::textarea('info', old('info'), ['id' => 'info', 'rows' => 5, 'class' => 'form-control','placeholder'=>trans('badge.form.placeholder.enter_details'),'spellcheck'=>'false']) !!}
        @endif
    </div>
</div>
@if($edit && $badgeData->type == "challenge")
    <div class="col-lg-6 col-xl-4">
        <div class="form-group">
            <label for="">{{trans('badge.form.labels.badge_type')}}</label>
            @if(!empty($badgeData->type))
                {{ Form::select('badge_type', $badgeTypes, $badgeData->type, ['class' => 'form-control select2','id'=>'badge_type',"style"=>"width: 100%;", 'placeholder' => trans('badge.form.placeholder.select_badge_type') ,'data-placeholder' => trans('badge.form.placeholder.select_badge_type'), 'autocomplete' => 'off','disabled'=>'true'] ) }}
            @elseif (!empty($defaultBadge))
                {{ Form::select('badge_type', $badgeTypes, $defaultBadge, ['class' => 'form-control select2','id'=>'badge_type',"style"=>"width: 100%;", 'placeholder' => trans('badge.form.placeholder.select_badge_type') ,'data-placeholder' => trans('badge.form.placeholder.select_badge_type'), 'autocomplete' => 'off','disabled'=>'true'] ) }}
            @else
                {{ Form::select('badge_type', $badgeTypes, null, ['class' => 'form-control select2','id'=>'badge_type',"style"=>"width: 100%;", 'placeholder' => trans('badge.form.placeholder.select_badge_type') ,'data-placeholder' => trans('badge.form.placeholder.select_badge_type'), 'autocomplete' => 'off'] ) }}
            @endif
            <input type="hidden" name="unite1" id="unite1" value="" />
        </div>
    </div>
    <div class="col-lg-6 col-xl-4">
        <div class="form-group">
            <label for="">{{trans('badge.form.labels.challenge_type')}}</label>
            {{ Form::select('challenge_type_slug', $challengeTypeSlug, $badgeData->challenge_type_slug, ['class' => 'form-control select2','id'=>'challenge_type_slug',"style"=>"width: 100%;", 'placeholder' => trans('badge.form.placeholder.select_challenge_type'), 'data-placeholder' => trans('badge.form.placeholder.select_challenge_type'), 'autocomplete' => 'off','disabled'=>'true'] ) }}
        </div>
    </div>
@else
    <div class="col-lg-6 col-xl-4">
        <div class="form-group">
            <label for="">{{trans('badge.form.labels.badge_type')}}</label>
            @if($edit && !empty($badgeData->type))
                {{ Form::select('badge_type', $badgeTypes, $badgeData->type, ['class' => 'form-control select2','id'=>'badge_type',"style"=>"width: 100%;", 'placeholder' => trans('badge.form.placeholder.select_badge_type'), 'data-placeholder' => trans('badge.form.placeholder.select_badge_type'), 'autocomplete' => 'off','disabled'=>'true'] ) }}
            @elseif (!empty($defaultBadge))
                {{ Form::select('badge_type', $badgeTypes, $defaultBadge, ['class' => 'form-control select2','id'=>'badge_type',"style"=>"width: 100%;", 'placeholder' => trans('badge.form.placeholder.select_badge_type'), 'data-placeholder' => trans('badge.form.placeholder.select_badge_type'), 'autocomplete' => 'off'] ) }}
            @else
                {{ Form::select('badge_type', $badgeTypes, null, ['class' => 'form-control select2','id'=>'badge_type',"style"=>"width: 100%;", 'placeholder' => trans('badge.form.placeholder.select_badge_type'), 'data-placeholder' => trans('badge.form.placeholder.select_badge_type'), 'autocomplete' => 'off'] ) }}
            @endif
            <input type="hidden" name="unite1" id="unite1" value="" />
        </div>
    </div>
    <div class="col-lg-6 col-xl-4">
        <div class="form-group badge_target">
            <label for="">{{trans('badge.form.labels.badge_target')}}</label>
            @if(!empty($badgeData->challenge_target_id))
                {{ Form::select('badge_target', $challenge_targets, $badgeData->challenge_target_id, ['class' => 'form-control select2','id'=>'badge_target',"style"=>"width: 100%;", 'placeholder' => trans('badge.form.placeholder.select_badge_category'), 'data-placeholder' => trans('badge.form.placeholder.select_badge_category'), 'autocomplete' => 'off','disabled'=>'true'] ) }}
            @elseif($edit && $badgeData->type == 'masterclass')
                {{ Form::select('badge_target', $challenge_targets, 'materclass', ['class' => 'form-control select2','id'=>'badge_target',"style"=>"width: 100%;", 'placeholder' => trans('badge.form.placeholder.select_badge_category'), 'data-placeholder' => trans('badge.form.placeholder.select_badge_category'), 'autocomplete' => 'off'] ) }}
            @elseif($edit && $badgeData->type == 'daily')
                {{ Form::select('badge_target', $challenge_targets, 'steps', ['class' => 'form-control select2','id'=>'badge_target',"style"=>"width: 100%;", 'placeholder' => trans('badge.form.placeholder.select_badge_category'), 'data-placeholder' => trans('badge.form.placeholder.select_badge_category'), 'autocomplete' => 'off'] ) }}
            @else
                {{ Form::select('badge_target', $challenge_targets, null, ['class' => 'form-control select2','id'=>'badge_target',"style"=>"width: 100%;", 'placeholder' => trans('badge.form.placeholder.select_badge_category'), 'data-placeholder' => trans('badge.form.placeholder.select_badge_category'), 'autocomplete' => 'off'] ) }}
            @endif
        </div>
    </div>
    <div class="col-lg-6 col-xl-4 d-none excercise_type">
        <div class="form-group">
            <label for="">{{trans('badge.form.labels.excercise_type')}}</label>
            @if(!empty($badgeData->model_id))
                {{ Form::select('excercise_type', $exercises, $badgeData->model_id, ['class' => 'form-control select2','id'=>'excercise_type',"style"=>"width: 100%;", 'placeholder' => trans('badge.form.placeholder.select_exercise_type'), 'data-placeholder' => trans('badge.form.placeholder.select_exercise_type'), 'autocomplete' => 'off','disabled'=>'true'] ) }}
            @else
                {{ Form::select('excercise_type', $exercises, null, ['class' => 'form-control select2','id'=>'excercise_type',"style"=>"width: 100%;", 'placeholder' => trans('badge.form.placeholder.select_exercise_type'), 'data-placeholder' => trans('badge.form.placeholder.select_exercise_type'), 'autocomplete' => 'off'] ) }}
            @endif
        </div>
    </div>
    @if($edit && ($badgeData->type != "masterclass" && $badgeData->type != "daily" && $badgeData->type != "ongoing"))
    <div class="col-lg-6 col-xl-4 uom">
        <div class="form-group">
            <label for="">{{trans('badge.form.labels.uom')}}</label>
            @if(!empty($badgeData->uom))
                {{ Form::select('uom', $uom_data, $badgeData->uom, ['class' => 'form-control select2','id'=>'uom',"style"=>"width: 100%;", 'placeholder' => trans('badge.form.placeholder.select_unit_of_measurement'), 'data-placeholder' => trans('badge.form.placeholder.select_unit_of_measurement'), 'autocomplete' => 'off','disabled'=>'true','disabled'=>'true'] ) }}
            @else
                {{ Form::select('uom', $uoms, null, ['class' => 'form-control select2','id'=>'uom',"style"=>"width: 100%;", 'placeholder' => trans('badge.form.placeholder.select_unit_of_measurement'), 'data-placeholder' => trans('badge.form.placeholder.select_unit_of_measurement'), 'autocomplete' => 'off','disabled'=>'true'] ) }}
            @endif
            <input type="hidden" name="unite" id="unite" value="" />
        </div>
    </div>
    <div class="col-lg-6 col-xl-4">
        <div class="form-group">
            <label for="">{{trans('badge.form.labels.target_values')}}</label>
            @if(!empty($badgeData->target))
                {{ Form::text('target_values', old('target_values',$badgeData->target), ['class' => 'form-control', 'placeholder' => trans('badge.form.placeholder.enter_target_value'), 'id' => 'target_values', 'autocomplete' => 'off','disabled'=>'true']) }}
            @else
                {{ Form::text('target_values', old('target_values'), ['class' => 'form-control', 'placeholder' => trans('badge.form.placeholder.enter_target_value'), 'id' => 'target_values', 'autocomplete' => 'off']) }}
            @endif
        </div>
    </div>
    @elseif(!$edit)
    <div class="col-lg-6 col-xl-4 uom generalbadgediv">
        <div class="form-group">
            <label for="">{{trans('badge.form.labels.uom')}}</label>
            @if(!empty($badgeData->uom))
                {{ Form::select('uom', $uom_data, $badgeData->uom, ['class' => 'form-control select2','id'=>'uom',"style"=>"width: 100%;", 'placeholder' => trans('badge.form.placeholder.select_unit_of_measurement'), 'data-placeholder' => trans('badge.form.placeholder.select_unit_of_measurement'), 'autocomplete' => 'off','disabled'=>'true','disabled'=>'true'] ) }}
            @else
                {{ Form::select('uom', $uoms, null, ['class' => 'form-control select2','id'=>'uom',"style"=>"width: 100%;", 'placeholder' => trans('badge.form.placeholder.select_unit_of_measurement'), 'data-placeholder' => trans('badge.form.placeholder.select_unit_of_measurement'), 'autocomplete' => 'off','disabled'=>'true'] ) }}
            @endif
            <input type="hidden" name="unite" id="unite" value="" />
        </div>
    </div>
    <div class="col-lg-6 col-xl-4 generalbadgediv">
        <div class="form-group">
            <label for="">{{trans('badge.form.labels.target_values')}}</label>
            @if(!empty($badgeData->target))
                {{ Form::text('target_values', old('target_values',$badgeData->target), ['class' => 'form-control', 'placeholder' => trans('badge.form.placeholder.enter_target_value'), 'id' => 'target_values', 'autocomplete' => 'off','disabled'=>'true']) }}
            @else
                {{ Form::text('target_values', old('target_values'), ['class' => 'form-control', 'placeholder' => trans('badge.form.placeholder.enter_target_value'), 'id' => 'target_values', 'autocomplete' => 'off']) }}
            @endif
        </div>
    </div>
    @endif
    <div class="col-lg-6 col-xl-4">
        <div class="form-group d-none expire_days">
            <label for="">{{trans('badge.form.labels.expire_days')}}</label>
            @if(!empty($badgeData->expires_after))
                {{ Form::text('no_of_days', old('no_of_days',$badgeData->expires_after), ['class' => 'form-control', 'placeholder' => trans('badge.form.placeholder.enter_number_of_days'), 'id' => 'no_of_days', 'autocomplete' => 'off','disabled'=>'true']) }}
            @else
                {{ Form::text('no_of_days', old('no_of_days'), ['class' => 'form-control', 'placeholder' => trans('badge.form.placeholder.enter_number_of_days'), 'id' => 'no_of_days', 'autocomplete' => 'off']) }}
            @endif
        </div>
    </div>
    <div class="col-lg-6 col-xl-4 d-none">
        <div class="form-group">
            <div class="willExpireVisibility">
                <label class="custom-checkbox">
                    {{ trans('badge.form.labels.can_expire') }}
                    @if(!$edit)
                    @php
                        $checked = "";
                        if(!empty(old('will_badge_expire')) && old('will_badge_expire') ==  'yes'){
                            $checked = 'checked="checked"';
                        }

                    @endphp
                    <input type="checkbox" class="form-control" value="yes" name="will_badge_expire" id="will_badge_expire" {{$checked}} />
                        <span class="checkmark">
                        </span>
                        <span class="checkbox-line">
                        </span>
                    @else
                    @php
                        $checked = "";
                        if(!empty($badgeData) && $badgeData->can_expire ==  1){
                            $checked = 'checked="checked"';
                        }

                    @endphp
                    <input type="checkbox" class="form-control" value="yes" name="will_badge_expire" id="will_badge_expire" {{$checked}} disabled="true" />
                        <span class="checkmark">
                        </span>
                        <span class="checkbox-line">
                        </span>
                    @endif
                </label>
            </div>
        </div>
    </div>
@endif