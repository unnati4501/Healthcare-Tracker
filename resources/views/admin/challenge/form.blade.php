<div class="card-inner">
    <div class="row">
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('logo', trans('challenges.form.labels.logo')) }}
                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('challenge.logo') }}">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
                <div class="custom-file custom-file-preview">
                    <label class="file-preview-img" for="profileImage" style="display: flex;">
                        @if(!empty($challengeData->logo))
                        <img id="previewImg" src="{{$challengeData->logo}}" width="200" height="200" />
                        @else
                        <img id="previewImg" src="{{asset('assets/dist/img/boxed-bg.png')}}" width="200" height="200"/>
                        @endif
                    </label>
                    {{ Form::file('logo', ['class' => 'custom-file-input form-control', 'id' => 'logo', 'data-width' => config('zevolifesettings.imageConversions.challenge.logo.width'), 'data-height' => config('zevolifesettings.imageConversions.challenge.logo.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.challenge.logo'), 'autocomplete' => 'off','title'=>''])}}
                    <label class="custom-file-label" for="logo">
                        @if(!empty($challengeData->logo_name))
                            {{$challengeData->logo_name}}
                        @else
                            {{ trans('challenges.form.placeholders.logo') }}
                        @endif
                    </label>
                </div>
            </div>            
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('name', trans('challenges.form.labels.name')) }}
                @if(!empty($challengeData->title))
                    {{ Form::text('name', old('name',$challengeData->title), ['class' => 'form-control', 'placeholder' => trans('challenges.form.placeholders.name'), 'id' => 'name', 'autocomplete' => 'off']) }}
                @else
                    {{ Form::text('name', old('name'), ['class' => 'form-control', 'placeholder' => trans('challenges.form.placeholders.name'), 'id' => 'name', 'autocomplete' => 'off']) }}
                @endif
            </div>            
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('description', trans('challenges.form.labels.info')) }}
                @if(!empty($challengeData->description))
                    {!! Form::textarea('info', old('info',$challengeData->description), ['id' => 'info', 'rows' => 5, 'class' => 'form-control','placeholder'=>trans('challenges.form.placeholders.info'),'spellcheck'=>'false']) !!}
                @else
                    {!! Form::textarea('info', old('info'), ['id' => 'info', 'rows' => 5, 'class' => 'form-control','placeholder'=>trans('challenges.form.placeholders.info'),'spellcheck'=>'false']) !!}
                @endif
            </div>
        </div>
    </div>
    <div class="row">
        @if($route == 'challenges' || $route == 'teamChallenges')
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('map_challenge', trans('challenges.form.labels.map_challenge')) }}
                <div>
                    <label class="custom-checkbox">
                        {{ trans('challenges.form.placeholders.map_challenge') }}
                        @if(!$edit)
                        @php
                            $checked = "";
                            if(!empty(old('map_challenge')) && old('map_challenge') ==  'yes'){
                                $checked = 'checked="checked"';
                            }
                        @endphp
                        <input type="checkbox" value="yes" name="map_challenge" id="map_challenge" {{$checked}} />
                        <span class="checkmark">
                        </span>
                        <span class="box-line">
                        </span>
                        @else
                        @php
                            $checked = "";
                            if(!empty($challengeData) && $challengeData->map_id != ''){
                                $checked = 'checked="checked"';
                            }
                        @endphp
                        <input type="checkbox" value="yes" name="map_challenge" id="map_challenge" {{$checked}} disabled="true" />
                        <span class="checkmark">
                        </span>
                        <span class="box-line">
                        </span>
                        @endif
                    </label>
                </div>
            </div>
        </div>
        @if($edit && $challengeData->map_id != '')
        <div class="col-lg-6 col-xl-4 map_listing">
            <div class="form-group">
                {{ Form::label('select_map', trans('challenges.form.labels.select_map')) }}
                {{ Form::select('select_map', $map_library, old('select_map', $challengeData->map_id), ['class' => 'form-control select2','id'=>'select_map',"style"=>"width: 100%;", 'placeholder' => '', 'data-placeholder' => trans('challenges.form.placeholders.select_map'), 'autocomplete' => 'off', "disabled" => true] ) }}
            </div>
        </div>
        @else
        <div class="col-lg-6 col-xl-4 map_listing d-none">
            <div class="form-group">
                {{ Form::label('select_map', trans('challenges.form.labels.select_map')) }}
                {{ Form::select('select_map', $map_library, old('select_map'), ['class' => 'form-control select2','id'=>'select_map',"style"=>"width: 100%;", 'placeholder' => '', 'data-placeholder' => trans('challenges.form.placeholders.select_map'), 'autocomplete' => 'off'] ) }}
            </div>
        </div>
        @endif
        @endif
        <div class="col-lg-6 col-xl-4">
            <div class="form-group challenge_category">
                {{ Form::label('challenge_category', trans('challenges.form.labels.category')) }}
                @if(!empty($challengeData->challenge_category_id))
                    {{ Form::select('challenge_category', $challenge_categories, $challengeData->challenge_category_id, ['class' => 'form-control select2','id'=>'challenge_category',"style"=>"width: 100%;", 'placeholder' => '', 'data-placeholder' => trans('challenges.form.placeholders.category'), 'autocomplete' => 'off','disabled'=>'true'] ) }}
                @else
                    {{ Form::select('challenge_category', $challenge_categories, old('challenge_category'), ['class' => 'form-control select2','id'=>'challenge_category',"style"=>"width: 100%;", 'placeholder' => '', 'data-placeholder' => trans('challenges.form.placeholders.category'), 'autocomplete' => 'off'] ) }}
                @endif
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6 col-xl-12">
            <div class="mb-2">
                <h6 class="text-primary challenge-rule-title d-none">
                    {{ trans('challenges.form.labels.rule1') }}
                </h6>
            </div>
            <hr class="mt-1"/>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6 col-xl-4">
            <div class="form-group target_type">
                {{ Form::label('target_type', trans('challenges.form.labels.target_type')) }}
                @if(!empty($challengeData->challengeRules[0]->challenge_target_id))
                    {{ Form::select('target_type', $challenge_targets, $challengeData->challengeRules[0]->challenge_target_id, ['class' => 'form-control select2','id'=>'target_type',"style"=>"width: 100%;", 'placeholder' => '', 'data-placeholder' => trans('challenges.form.placeholders.target_type'), 'autocomplete' => 'off','disabled'=>'true'] ) }}
                @else
                    {{ Form::select('target_type', $challenge_targets, old('target_type'), ['class' => 'form-control select2','id'=>'target_type',"style"=>"width: 100%;", 'placeholder' => '', 'data-placeholder' => trans('challenges.form.placeholders.target_type'), 'autocomplete' => 'off'] ) }}
                @endif
            </div>
        </div>
        <div class="col-lg-6 col-xl-4 d-none excercise_type">
            <div class="form-group d-none excercise_type">
                {{ Form::label('excercise_type', trans('challenges.form.labels.exercise_type')) }}
                @if(!empty($challengeData->challengeRules[0]->model_id))
                    {{ Form::select('excercise_type', $exercises, $challengeData->challengeRules[0]->model_id, ['class' => 'form-control select2','id'=>'excercise_type',"style"=>"width: 100%;", 'placeholder' => '', 'data-placeholder' => trans('challenges.form.placeholders.exercise_type'), 'autocomplete' => 'off','disabled'=>'true'] ) }}
                @else
                    {{ Form::select('excercise_type', $exercises, old('excercise_type'), ['class' => 'form-control select2','id'=>'excercise_type',"style"=>"width: 100%;", 'placeholder' => '', 'data-placeholder' => trans('challenges.form.placeholders.exercise_type'), 'autocomplete' => 'off','disabled'=>'true'] ) }}
                @endif
            </div>
        </div>
        <div class="col-lg-6 col-xl-4 d-none content_type">
            <div class="form-group d-none content_type">
                {{ Form::label('content_type', trans('challenges.form.labels.content_type')) }}
                @if($edit)
                    {{ Form::select('content_challenge_ids[]', $contentCategories, explode(',', $challengeData->challengeRules[0]->content_challenge_ids), ['class' => 'form-control select2', 'multiple' => true, 'id'=>'content_type',"style"=>"width: 100%;", 'placeholder' => '', 'data-placeholder' => trans('challenges.form.placeholders.content_type'), 'autocomplete' => 'off','disabled'=>'true'] ) }}
                @else
                    {{ Form::select('content_challenge_ids[]', $contentCategories, (old("content_challenge_ids") ?? null), ['class' => 'form-control select2', 'id'=>'content_type', 'multiple' => true, "style"=>"width: 100%;", 'data-placeholder' => trans('challenges.form.placeholders.content_type'), 'autocomplete' => 'off','disabled'=>'true'] ) }}
                @endif
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('uom', trans('challenges.form.labels.uom')) }}
                @if(!empty($challengeData->challengeRules[0]->uom))
                    {{ Form::select('uom', $uoms, $challengeData->challengeRules[0]->uom, ['class' => 'form-control select2','id'=>'uom',"style"=>"width: 100%;", 'placeholder' => '', 'data-placeholder' => trans('challenges.form.placeholders.uom'), 'autocomplete' => 'off','disabled'=>'true'] ) }}
                @else
                    {{ Form::select('uom', $uoms, null, ['class' => 'form-control select2','id'=>'uom',"style"=>"width: 100%;", 'placeholder' => '', 'data-placeholder' => trans('challenges.form.placeholders.uom'), 'autocomplete' => 'off','disabled'=>'true'] ) }}
                @endif
                <input type="hidden" name="unite" id="unite" value="" />
            </div>
        </div>
        <div class="col-lg-6 col-xl-4 targetUnits">
            <div class="form-group target_units">
                {{ Form::label('target_units', trans('challenges.form.labels.target_units')) }}
                @if(isset($challengeData->challengeRules[0]->target))
                    {{ Form::text('target_units', old('target_units',$challengeData->challengeRules[0]->target), ['class' => 'form-control', 'placeholder' => trans('challenges.form.placeholders.target_units'), 'id' => 'target_units', 'autocomplete' => 'off','disabled'=>'true']) }}
                @else
                    {{ Form::text('target_units', old('target_units'), ['class' => 'form-control', 'placeholder' => trans('challenges.form.placeholders.target_units'), 'id' => 'target_units', 'autocomplete' => 'off']) }}
                @endif
            </div>
        </div>
    </div>
    <div class="row d-none rule2">
        <div class="col-lg-6 col-xl-12">
            <div class="mb-2">
                <h6 class="text-primary challenge-rule-title d-none">
                    {{ trans('challenges.form.labels.rule2') }}
                </h6>
            </div>
            <hr class="mt-1"/>
        </div>       
    </div>
    <div class="row d-none rule2">
        <div class="col-lg-6 col-xl-4">
            <div class="form-group target_type1">
                {{ Form::label('target_type1', trans('challenges.form.labels.target_type')) }}
                @if(!empty($challengeData->challengeRules[1]->challenge_target_id))
                    {{ Form::select('target_type1', $challenge_targets, $challengeData->challengeRules[1]->challenge_target_id, ['class' => 'form-control select2','id'=>'target_type1',"style"=>"width: 100%;", 'placeholder' => '', 'data-placeholder' => trans('challenges.form.placeholders.target_type'), 'autocomplete' => 'off','disabled'=>'true'] ) }}
                @else
                    {{ Form::select('target_type1', $challenge_targets, null, ['class' => 'form-control select2','id'=>'target_type1',"style"=>"width: 100%;", 'placeholder' => '', 'data-placeholder' => trans('challenges.form.placeholders.target_type'), 'autocomplete' => 'off'] ) }}
                @endif
            </div>
        </div>
        <div class="col-lg-6 col-xl-4 d-none excercise_type1">
            <div class="form-group d-none excercise_type1" >
                {{ Form::label('excercise_type1', trans('challenges.form.labels.exercise_type')) }}
                @if(!empty($challengeData->challengeRules[1]->model_id))
                    {{ Form::select('excercise_type1', $exercises, $challengeData->challengeRules[1]->model_id, ['class' => 'form-control select2','id'=>'excercise_type1',"style"=>"width: 100%;", 'placeholder' => '', 'data-placeholder' => trans('challenges.form.placeholders.exercise_type'), 'autocomplete' => 'off','disabled'=>'true'] ) }}
                @else
                    {{ Form::select('excercise_type1', $exercises, null, ['class' => 'form-control select2','id'=>'excercise_type1',"style"=>"width: 100%;", 'placeholder' => '', 'data-placeholder' => trans('challenges.form.placeholders.exercise_type'), 'autocomplete' => 'off','disabled'=>'true'] ) }}
                @endif
            </div>
        </div>
        <div class="col-lg-6 col-xl-4 d-none content_type1">
            <div class="form-group d-none content_type1">
                {{ Form::label('content_type', trans('challenges.form.labels.content_type')) }}
                @if($edit && !empty($challengeData->challengeRules[1]->content_challenge_ids))
                    {{ Form::select('content_challenge_ids1[]', $contentCategories, explode(',', $challengeData->challengeRules[1]->content_challenge_ids), ['class' => 'form-control select2', 'multiple' => true, 'id'=>'content_type1',"style"=>"width: 100%;", 'placeholder' => '', 'data-placeholder' => trans('challenges.form.placeholders.content_type'), 'autocomplete' => 'off','disabled'=>'true'] ) }}
                @else
                    {{ Form::select('content_challenge_ids1[]', $contentCategories, (old("content_challenge_ids") ?? null), ['class' => 'form-control select2', 'id'=>'content_type1', 'multiple' => true, "style"=>"width: 100%;", 'data-placeholder' => trans('challenges.form.placeholders.content_type'), 'autocomplete' => 'off','disabled'=>'true'] ) }}
                @endif
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('uom1', trans('challenges.form.labels.uom')) }}
                @if(!empty($challengeData->challengeRules[1]->uom))
                    {{ Form::select('uom1', $uoms, $challengeData->challengeRules[1]->uom, ['class' => 'form-control select2','id'=>'uom1',"style"=>"width: 100%;", 'placeholder' => '', 'data-placeholder' => trans('challenges.form.placeholders.uom'), 'autocomplete' => 'off','disabled'=>'true','disabled'=>'true'] ) }}
                @else
                    {{ Form::select('uom1', $uoms, null, ['class' => 'form-control select2','id'=>'uom1',"style"=>"width: 100%;", 'placeholder' => '', 'data-placeholder' => trans('challenges.form.placeholders.uom'), 'autocomplete' => 'off','disabled'=>'true'] ) }}
                @endif
                <input type="hidden" name="unite1" id="unite1" value="" />
            </div>            
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group target_units1">
                {{ Form::label('target_units1', trans('challenges.form.labels.target_units')) }}
                @if(!empty($challengeData->challengeRules[1]->target))
                    {{ Form::text('target_units1', old('target_units1',$challengeData->challengeRules[1]->target), ['class' => 'form-control', 'placeholder' => trans('challenges.form.labels.target_units'), 'id' => 'target_units1', 'autocomplete' => 'off','disabled'=>'true']) }}
                @else
                    {{ Form::text('target_units1', old('target_units1'), ['class' => 'form-control', 'placeholder' => trans('challenges.form.labels.target_units'), 'id' => 'target_units1', 'autocomplete' => 'off']) }}
                @endif
            </div>
        </div>
        <div class="col-lg-6 col-xl-12">
            <hr class="mt-1"/>
        </div>               
    </div>
    <div class="row">
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('start_date', trans('challenges.form.labels.start_date')) }}
                @if(!empty($challengeData->start_date1))
                    @if(date('Y-m-d',strtotime($challengeData->start_date1)) <= date('Y-m-d') || !empty($challengeData->parent_id))
                        {{ Form::text('start_date', date('Y-m-d',strtotime($challengeData->start_date1)), ['class' => 'form-control datepicker ', 'id' => 'start_date', 'placeholder' => trans('challenges.form.placeholders.start_date'),  'autocomplete'=>'off','disabled'=>'true']) }}
                        <input type="hidden" name="start_date" id="start_date" value="{{date('Y-m-d',strtotime($challengeData->start_date1))}}" />
                    @else
                        {{ Form::text('start_date', date('Y-m-d',strtotime($challengeData->start_date1)), ['class' => 'form-control datepicker ', 'id' => 'start_date', 'placeholder' => trans('challenges.form.placeholders.start_date'),  'autocomplete'=>'off']) }}
                    @endif
                @else
                    {{ Form::text('start_date', old('start_date'), ['class' => 'form-control datepicker ', 'id' => 'start_date', 'placeholder' => trans('challenges.form.placeholders.start_date'), 'autocomplete'=>'off']) }}
                @endif
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('end_date', trans('challenges.form.labels.end_date')) }}
                @if(!empty($challengeData->end_date1))
                    @if(!empty($challengeData->parent_id))
                        {{ Form::text('end_date', date('Y-m-d',strtotime($challengeData->end_date1)), ['class' => 'form-control datepicker ', 'id' => 'end_date', 'placeholder' => trans('challenges.form.placeholders.end_date'), 'autocomplete'=>'off','disabled'=>'true']) }}
                        <input type="hidden" name="end_date" id="end_date" value="{{date('Y-m-d',strtotime($challengeData->end_date1))}}" />
                    @else
                        {{ Form::text('end_date', date('Y-m-d',strtotime($challengeData->end_date1)), ['class' => 'form-control datepicker ', 'id' => 'end_date', 'placeholder' => trans('challenges.form.placeholders.end_date'), 'autocomplete'=>'off']) }}
                    @endif
                @else
                    {{ Form::text('end_date', old('end_date'), ['class' => 'form-control datepicker ', 'id' => 'end_date', 'placeholder' => trans('challenges.form.placeholders.end_date'), 'autocomplete'=>'off']) }}
                @endif
            </div>            
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('numberOfday', trans('challenges.form.labels.days')) }}
                <input type="text" name="numberOfday" id="numberOfday" disabled="true" class="form-control">
            </div>            
        </div>      
    </div>
    @if(isset($route) && $route == 'challenges')
    <div class="row">
        <div class="col-lg-6 col-xl-4 open-challenge {{ $hideOpenChallenge }}">
            <div class="form-group">
                {{ Form::label('close', trans('challenges.form.labels.open')) }}
                <div>
                    <label class="custom-checkbox">
                        {{ trans('challenges.form.placeholders.open') }}
                        @if(!$edit)
                        @php
                            $checked = "";
                            if(!empty(old('close')) && old('close') ==  'yes'){
                                $checked = 'checked="checked"';
                            }
                        @endphp
                        <input type="checkbox" value="yes" name="close" id="close" {{$checked}} />
                        <span class="checkmark">
                        </span>
                        <span class="box-line">
                        </span>
                        @else
                        @php
                            $checked = "";
                            if(!empty($challengeData) && $challengeData->close ==  0){
                                $checked = 'checked="checked"';
                            }
                        @endphp
                        <input type="checkbox" value="yes" name="close" id="close" {{$checked}} disabled="true" />
                        <span class="checkmark">
                        </span>
                        <span class="box-line">
                        </span>
                        @endif
                    </label>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-12">
            <hr class="mt-1"/>
        </div>            
    </div> 
    <div class="row d-none" id="recursiveSection">
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('recursive', trans('challenges.form.labels.recursive')) }}
                <div>
                    <label class="custom-checkbox">
                        {{ trans('challenges.form.placeholders.recursive') }}
                        @if(!$edit)
                        @php
                            $checked = "";
                            if(!empty(old('recursive')) && old('recursive') ==  'yes'){
                                $checked = 'checked="checked"';
                            }
                        @endphp
                        <input type="checkbox" value="yes" name="recursive" id="recursive" {{$checked}} />
                        <span class="checkmark">
                        </span>
                        <span class="box-line">
                        </span>
                        @else
                        @php
                            $checked = "";
                            if(!empty($challengeData) && $challengeData->recurring ==  1){
                                $checked = 'checked="checked"';
                            }
                        @endphp
                        <input type="checkbox" value="yes" name="recursive" id="recursive" {{$checked}} disabled="true"/>
                        @if(!empty($challengeData) && $challengeData->recurring ==  1)
                        <input type="hidden" value="yes" name="recursive" id="recursive" {{$checked}} />
                        @endif
                        <span class="checkmark">
                        </span>
                        <span class="box-line">
                        </span>
                        @endif
                    </label>
                </div>
            </div>   
        </div>     
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                <div>
                    {{ Form::label('recursive_type', trans('challenges.form.labels.recursive_type')) }}
                </div>
                <div class="">
                @if(!empty($challengeData->recurring_type))
                    @foreach($recurring_type as $key=>$value)
                        <label class="custom-radio">{{$value}}
                            <input class="recursive_type" type="radio" name="recursive_type" value="{{$key}}" {{ ($challengeData->recurring_type == $key)? 'checked' : '' }} disabled="true">
                            <span class="checkmark"></span><span class="box-line"></span>
                        </label>
                    @endforeach
                    <input class="recursive_type" type="hidden" name="recursive_type" value="{{$challengeData->recurring_type}}" />
                @else
                    @foreach($recurring_type as $key=>$value)
                        <label class="custom-radio">{{$value}}
                            <input class="recursive_type" type="radio" name="recursive_type" value="{{$key}}" {{ (old('recursive_type') == $key)? 'checked' : '' }} disabled="true">
                            <span class="checkmark"></span><span class="box-line"></span>
                        </label>
                    @endforeach
                @endif
                </div>
            </div>            
        </div>   
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('recursive_count', trans('challenges.form.labels.recursive_count')) }}
                @if(!empty($challengeData->recurring_count))
                    {{ Form::text('recursive_count', old('recursive_count',$challengeData->recurring_count), ['class' => 'form-control', 'placeholder' => trans('challenges.form.placeholders.recursive_count'), 'id' => 'recursive_count', 'autocomplete' => 'off','disabled'=>'true', 'min' => 1, 'max' => 365, "oninput"=>"validity.valid||(value='')", "onkeypress"=>"return event.charCode >= 48 && event.charCode <= 57"]) }}
                    <input class="recursive_count" type="hidden" name="recursive_count" value="{{ $challengeData->recurring_count }}" />
                @else
                    {{ Form::text('recursive_count', old('recursive_count'), ['class' => 'form-control', 'placeholder' => trans('challenges.form.placeholders.recursive_count'), 'id' => 'recursive_count', 'autocomplete' => 'off','disabled'=>'true', 'min' => 1, 'max' => 365, "oninput"=>"validity.valid||(value='')", "onkeypress"=>"return event.charCode >= 48 && event.charCode <= 57"]) }}
                @endif
            </div>
        </div>
        @if(!empty($challengeData))
        <div class="col-lg-6 col-xl-6">
            <div class="form-group">
                {{ Form::label('locations', trans('challenges.form.labels.location')) }}
                {{ Form::select('locations[]', $companyLocations, old('locations[]', explode(',', $challengeData->locations)), ['class' => 'form-control select2','id'=>'locations',"style"=>"width: 100%;", 'autocomplete' => 'off', 'multiple' => true, "disabled" => $locationDepartmentEdit] ) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-6">
            <div class="form-group">
                {{ Form::label('department', trans('challenges.form.labels.department')) }}
                {{ Form::select('department[]', $companyDepartment, old('department[]', explode(',', $challengeData->departments)), ['class' => 'form-control select2','id'=>'department',"style"=>"width: 100%;", 'autocomplete' => 'off', 'multiple' => true, "disabled" => $locationDepartmentEdit] ) }}
            </div>
        </div>
        @else
        <div class="col-lg-6 col-xl-6">
            <div class="form-group">
                {{ Form::label('locations', trans('challenges.form.labels.location')) }}
                {{ Form::select('locations[]', $companyLocations, old('locations[]'), ['class' => 'form-control select2','id'=>'locations',"style"=>"width: 100%;", 'multiple' => true, 'autocomplete' => 'off'] ) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-6">
            <div class="form-group">
                {{ Form::label('department', trans('challenges.form.labels.department')) }}
                {{ Form::select('department[]', $companyDepartment, old('department[]'), ['class' => 'form-control select2','id'=>'department',"style"=>"width: 100%;", "disabled" => true, 'multiple' => true, 'autocomplete' => 'off'] ) }}
            </div>
        </div>
        @endif
        <div class="col-lg-6 col-xl-12">
            <hr class="mt-1"/>
        </div>
    </div>   
    @endif
    <div class="row d-none" id="ongoing_badges_flag">
        <div class="col-lg-6 col-xl-4">
            {{ Form::label('ongoing_challenge_badge', trans('challenges.form.labels.ongoing_challenge_badge')) }}
            <div>
                <label class="custom-checkbox" for="ongoing_challenge_badge">
                    {{ trans('challenges.form.labels.ongoing_challenge_badge') }}
                    {{ Form::checkbox('ongoing_challenge_badge', 1, old('ongoing_challenge_badge', (!empty($challengeData) && $challengeData->is_badge) ? true : false ), ['class' => 'form-control', 'id' => 'ongoing_challenge_badge', 'disabled' => $allowTargetUnitEdit]) }}
                    <span class="checkmark">
                    </span>
                    <span class="box-line">
                    </span>
                </label>
            </div>
        </div>
    </div>
</div>
<div class="card-inner d-none" id="ongoing_badges_div">
    <h3 class="card-inner-title">
        {{ trans('challenges.form.labels.ongoing_badges') }}
    </h3>
    <div class="row">
        <div class="col-md-10">
            <div class="table-responsive">
                <table class="table custom-table no-hover badges-table gap-adjust no-border" id="ongoingBadgeTbl">
                    <tbody>
                        @forelse ($challengeOngoingBadges ?? [] as $key => $badge)
                        @include('admin.challenge.ongoing_badges', [
                            'count'    => $key,
                            'target'   => $badge['target'],
                            'inDays'   => $badge['in_days'],
                            'badge'    => $badge['badge_id'],
                            'show_del' => 'hide',
                        ])
                        @empty
                        @include('admin.challenge.ongoing_badges', [
                            'count'     => '0',
                            'target'    => null,
                            'inDays'    => null,
                            'badge'     => null,
                            'show_del'  => '',
                        ])
                        @endforelse
                    </tbody>
                </table>
                <ul>
                    <li id="ongoing_badges-error" style="display: none;width: 100%;font-size: 85%; color: #f44436;">{{ trans('challenges.messages.ongoing_badge_required') }}
                    </li>
                    <li id="ongoing_badges_min-error" style="display: none;width: 100%;font-size: 85%; color: #f44436;">{{ trans('challenges.messages.ongoing_badge_min_days') }}
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
{{-- if($route != 'companyGoalChallenges') --}}
<div class="card-inner" id="participate_users">
    <h3 class="card-inner-title">
        @if($route == 'challenges')
        {{ trans('challenges.form.labels.participant_users') }}
        @else
        {{ trans('challenges.form.labels.participant_teams') }}
        @endif
    </h3>
    <div class="mb-4">
        <div id="setPermissionList" class="tree-multiselect-box">
            <select id="group_member" name="group_member" multiple="multiple" class="form-control" >
                @php
                    $particepentReadonly = "";
                    if(!empty($challengeData->start_date1) && date('Y-m-d',strtotime($challengeData->start_date1)) <= date('Y-m-d') && $route == 'challenges') {
                        $particepentReadonly = 'readonly';
                    }
                @endphp
                @if($route == 'challenges')
                @foreach($departmentData as $deptGroup => $deptData)
                    @foreach($deptData['teams'] as $teamGroup => $teamData)
                        @foreach($teamData['members'] as $memberGroup => $memberData)
                            @if($edit && !empty($groupUserData))
                                <option value="{{ $memberData['id'] }}" data-section="{{ $deptData['name'] }}/{{ $teamData['name'] }}"  {{ ((!empty($groupUserData) && in_array($memberData['id'], $groupUserData)))? 'selected' : ''   }} {{ $particepentReadonly }} {{ ($challengeData->creator_id == $memberData['id'])? 'readonly':'' }}>{{ $memberData['name'] }}</option>
                            @else
                                <option value="{{ $memberData['id'] }}" data-section="{{ $deptData['name'] }}/{{ $teamData['name'] }}"  {{ (!empty(old('members_selected')) && in_array($memberData['id'], old('members_selected')))? 'selected' : ''   }} >{{ $memberData['name'] }}</option>
                            @endif
                        @endforeach
                    @endforeach
                @endforeach
                @elseif($route == 'teamChallenges' || $route == 'companyGoalChallenges')
                @foreach($departmentData as $deptGroup => $deptData)
                    @foreach($deptData['teams'] as $teamGroup => $teamData)
                        @if($edit && !empty($groupUserData))
                            <option value="{{ $teamData['id'] }}" data-section="{{ $deptData['name'] }}"  {{ ((!empty($groupUserData) && in_array($teamData['id'], $groupUserData)))? 'selected' : ''   }} {{ $particepentReadonly }}>{{ $teamData['name'] }}</option>
                        @else
                            <option value="{{ $teamData['id'] }}" data-section="{{ $deptData['name'] }}"  {{ (!empty(old('members_selected')) && in_array($teamData['id'], old('members_selected')))? 'selected' : ''   }} >{{ $teamData['name'] }}</option>
                        @endif
                    @endforeach
                @endforeach
                @elseif($route == 'interCompanyChallenges')
                @foreach($companyData as $companyGroup => $companyData)
                    @foreach($companyData['departments'] as $deptGroup => $deptData)
                        @foreach($deptData['teams'] as $teamGroup => $teamData)
                            @if($edit && !empty($groupUserData))
                                @if((!empty($participatedComp) && $companyData['companyStatus'] == false && in_array($companyData['id'],$participatedComp)) || $companyData['companyStatus'] == true)
                                <option value="{{ $teamData['id'] }}" data-cid="{{ $companyData['id'] }}" data-section="{{ $companyData['name'] }}/{{ $deptData['name'] }}"  {{ ((!empty($groupUserData) && in_array($teamData['id'], $groupUserData)))? 'selected' : ''   }} {{ $particepentReadonly }} {{ ($challengeData->creator_id == $teamData['id'])? 'readonly':'' }}>{{ $teamData['name'] }}</option>
                                @endif
                            @else
                                <option value="{{ $teamData['id'] }}" data-cid="{{ $companyData['id'] }}" data-section="{{ $companyData['name'] }}/{{ $deptData['name'] }}"  {{ (!empty(old('members_selected')) && in_array($teamData['id'], old('members_selected')))? 'selected' : ''   }} qwe="{{ $companyData['id'] }}">{{ $teamData['name'] }}</option>
                            @endif
                        @endforeach
                    @endforeach
                @endforeach
                @endif
            </select>
        </div>                
    </div>    
    @if($route == 'challenges')
    <span id="group_member-error" style="display: none; width: 100%; margin-top: 1rem; font-size: 80%; color: #f44436; margin-left: 1rem;">
        {{ trans('challenges.messages.users_required') }}
    </span>
    <span id="group_member-min-error" style="display: none; width: 100%; margin-top: 1rem; font-size: 80%; color: #f44436; margin-left: 1rem;">
        {{ trans('challenges.messages.users_min') }}
    </span>
    <span id="group_member-max-error" style="display: none; width: 100%; margin-top: 1rem; font-size: 80%; color: #f44436; margin-left: 1rem;">
        {{ trans('challenges.messages.users_max') }}
    </span>
    @elseif($route == 'teamChallenges' || $route == 'companyGoalChallenges')
    <span id="group_member-error" style="display: none; width: 100%; margin-top: 1rem; font-size: 80%; color: #f44436; margin-left: 1rem;">
        {{ trans('challenges.messages.teams_required') }}
    </span>
    @if($route == 'teamChallenges')
    <span id="group_member-min-error" style="display: none; width: 100%; margin-top: 1rem; font-size: 80%; color: #f44436; margin-left: 1rem;">
        {{ trans('challenges.messages.teams_min') }}
    </span>
    @else
    <span id="group_member-min-error" style="display: none; width: 100%; margin-top: 1rem; font-size: 80%; color: #f44436; margin-left: 1rem;">
        {{ trans('challenges.messages.teams_min_company_goal') }}
    </span>
    @endif
    <span id="group_member-max-error" style="display: none; width: 100%; margin-top: 1rem; font-size: 80%; color: #f44436; margin-left: 1rem;">
        {{ trans('challenges.messages.teams_max') }}
    </span>
    @elseif($route == 'interCompanyChallenges')
    <span id="group_member-error" style="display: none; width: 100%; margin-top: 1rem; font-size: 80%; color: #f44436; margin-left: 1rem;">
        {{ trans('challenges.messages.teams_required') }}
    </span>
    <span id="group_member-min-error" style="display: none; width: 100%; margin-top: 1rem; font-size: 80%; color: #f44436; margin-left: 1rem;">
        {{ trans('challenges.messages.company_min') }}
    </span>
    @endif
</div>
{{-- endif --}}