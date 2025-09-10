<div class="card-inner">
    <div class="row">
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                <label for="logo">{{trans('exercise.form.labels.logo')}}</label>
                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('exercise.logo') }}">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
                <div class="custom-file custom-file-preview">
                    <label class="file-preview-img" for="profileImage">
                        <img id="previewImg" src="{{$exerciseData->logo ?? asset('assets/dist/img/boxed-bg.png')}}" width="200" height="200" />
                    </label>
                    {{ Form::file('logo', ['class' => 'custom-file-input form-control', 'id' => 'logo', 'data-width' => config('zevolifesettings.imageConversions.exercise.logo.width'), 'data-height' => config('zevolifesettings.imageConversions.exercise.logo.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.exercise.logo'), 'autocomplete' => 'off','title'=>''])}}
                    <label class="custom-file-label" for="logo">
                        {{ !empty($exerciseData->logo_name) ? $exerciseData->logo_name : trans('exercise.form.placeholder.choose_file') }}
                    </label>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                <label for="previewImg1">{{trans('exercise.form.labels.background')}}</label>
                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('exercise.background') }}">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
                <div class="custom-file custom-file-preview">
                    <label class="file-preview-img" for="profileImage">
                        <img id="previewImg1" src="{{ $exerciseData->background ?? asset('assets/dist/img/boxed-bg.png')}}" width="200" height="200" />
                    </label>
                    {{ Form::file('background', ['class' => 'custom-file-input form-control', 'id' => 'background', 'data-width' => config('zevolifesettings.imageConversions.exercise.background.width'), 'data-height' => config('zevolifesettings.imageConversions.exercise.background.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.exercise.background'), 'autocomplete' => 'off','title'=>''])}}
                    <label class="custom-file-label" for="background">
                        {{ !empty($exerciseData->background_name) ? $exerciseData->background_name : trans('exercise.form.placeholder.choose_file') }}
                    </label>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                <label for="">{{trans('exercise.form.labels.exercise_name')}}</label>
                @if(!empty($exerciseData->title))
                    {{ Form::text('name', old('name',$exerciseData->title), ['class' => 'form-control', 'placeholder' => trans('exercise.form.placeholder.enter_exercise_name'), 'id' => 'name', 'autocomplete' => 'off']) }}
                @else
                    {{ Form::text('name', old('name'), ['class' => 'form-control', 'placeholder' => trans('exercise.form.placeholder.enter_exercise_name'), 'id' => 'name', 'autocomplete' => 'off']) }}
                @endif
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('exercise', trans('exercise.form.labels.type')) }}
                @if(!empty($exerciseData->type))
                    {{ Form::select('type', $type, $exerciseData->type, ['class' => 'form-control select2','id'=>'type',"style"=>"width: 100%;", 'placeholder' => trans('exercise.form.placeholder.select_exercise_type'), 'data-placeholder' => trans('exercise.form.placeholder.select_exercise_type'), 'data-dependent' => 'department_id', 'autocomplete' => 'off'] ) }}
                @else
                    {{ Form::select('type', $type, null, ['class' => 'form-control select2','id'=>'type',"style"=>"width: 100%;", 'placeholder' => trans('exercise.form.placeholder.select_exercise_type'), 'data-placeholder' => trans('exercise.form.placeholder.select_exercise_type'), 'data-dependent' => 'department_id', 'autocomplete' => 'off'] ) }}
                @endif
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                <label>{{trans('exercise.form.labels.calories')}}</label>
                @if(!empty($exerciseData->calories))
                    {{ Form::number('calories', $exerciseData->calories , ['class' => 'form-control', 'placeholder' => '', 'min' => 1, "oninput"=>"validity.valid||(value='')", 'autocomplete' => 'off']) }}
                @else
                    {{ Form::number('calories', null , ['class' => 'form-control', 'placeholder' => '', 'min' => 1, "oninput"=>"validity.valid||(value='')", 'autocomplete' => 'off']) }}
                @endif
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                <label for="description">{{trans('exercise.form.labels.description')}}</label>
                @if(!empty($exerciseData->description))
                    {{ Form::textarea('description', old('description',$exerciseData->description), ['class' => 'form-control', 'placeholder' => 'Enter exercise description', 'id' => 'description', 'autocomplete' => 'off','rows'=> '4']) }}
                @else
                    {{ Form::textarea('description', old('description'), ['class' => 'form-control', 'placeholder' => 'Enter exercise description', 'id' => 'description', 'autocomplete' => 'off','rows'=> '4']) }}
                @endif
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <label class="custom-checkbox"> {{ trans('exercise.form.labels.show_map') }}
                @php
                    $checked = "";
                    if(!empty($exerciseData) && $exerciseData->show_map ==  1){
                        $checked = 'checked="checked"';
                    }
                @endphp
                <input type="checkbox" class="form-control" value="yes" name="show_map" id="show_map" {{$checked}} />
                    <span class="checkmark">
                    </span>
                    <span class="box-line">
                    </span>
            </label>
        </div>
    </div>
</div>
<div class="card-inner">
    <h3 class="card-inner-title">{{ Form::label('tracker_exercises', trans('exercise.form.labels.tracker_exercise')) }}</h3>
    <div>
        <div id="setPermissionList" class="tree-multiselect-box">
            <select id="tracker_exercises" name="tracker_exercises" multiple="multiple" class="form-control">
                @foreach($trackerExerciseData as $tracker => $trackerData)
                    @foreach($trackerData['exercises'] as $exerciseData)
                        <option value="{{ $exerciseData['id'] }}" data-section="{{ $trackerData['name'] }}">{{ $exerciseData['name'] }}</option>
                    @endforeach
               @endforeach
            </select>
        </div>
    </div>
</div>
<div class="card-inner">
@if($edit)
    @if(count($mappedExercises) > 0)
    <h3 class="card-inner-title">{{ trans('exercise.form.labels.mapped_exercises') }}</h3>
    <div>
        @foreach($mappedExercises as $key => $value)
            <div class="col-md-12">
                <div class="callout callout-primary">
                    <div class="m-0">
                        <b>{{ $key }}</b>: {!! $value !!}
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    @endif
@endif
</div>