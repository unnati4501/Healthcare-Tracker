<div class="card-inner">
    <div class="row">
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('logo', trans('personalChallenge.form.labels.logo')) }}
                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('personalChallenge.logo') }}">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
                <div class="custom-file custom-file-preview">
                    <label class="file-preview-img" for="profileImage" style="display: flex;">
                        @if(!empty($challengeData->personal_challenge_logo))
                        <img height="200" id="previewImg" src="{{$challengeData->personal_challenge_logo}}" width="200"/>
                        @else
                        <img height="200" id="previewImg" src="{{asset('assets/dist/img/boxed-bg.png')}}" width="200"/>
                        @endif
                    </label>
                    {{ Form::file('logo', ['class' => 'custom-file-input form-control', 'id' => 'logo', 'data-width' => config('zevolifesettings.imageConversions.personalChallenge.logo.width'), 'data-height' => config('zevolifesettings.imageConversions.personalChallenge.logo.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.personalChallenge.logo'), 'autocomplete' => 'off','title'=>''])}}
                    <label class="custom-file-label" for="logo">
                        @if(!empty($challengeData))
                            {{$challengeData->logo_name}}
                        @else
                            {{ trans('personalChallenge.form.placeholders.logo') }}
                        @endif
                    </label>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                @if($planAccess)
                {{ Form::label('name', trans('personalChallenge.form.labels.name') ) }}
                @else
                {{ Form::label('name', trans('personalChallenge.form.labels.name_goal') ) }}
                @endif
                @if(!empty($challengeData->title))
                    {{ Form::text('name', old('name',$challengeData->title), ['class' => 'form-control', 'placeholder' => $placeholderName, 'id' => 'name', 'autocomplete' => 'off']) }}
                @else
                    {{ Form::text('name', old('name'), ['class' => 'form-control', 'placeholder' => $placeholderName, 'id' => 'name', 'autocomplete' => 'off']) }}
                @endif
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                <div>
                    {{ Form::label('duration', trans('personalChallenge.form.labels.duration')) }}
                    <div class="input-group">
                        @if(!empty($challengeData->duration))
                        {!! Form::number('duration', old('duration',$challengeData->duration), ['class' => 'form-control', 'placeholder' => trans('personalChallenge.form.placeholders.duration'), 'min' => 1, 'max' => 365, "oninput"=>"validity.valid||(value='')", "onkeypress"=>"return event.charCode >= 48 && event.charCode <= 57", 'autocomplete' => 'off', 'disabled' => 'disabled']) !!}
                    @else
                        {!! Form::number('duration', old('duration'), ['class' => 'form-control', 'placeholder' => trans('personalChallenge.form.placeholders.duration'), 'min' => 1, 'max' => 365, "oninput"=>"validity.valid||(value='')", "onkeypress"=>"return event.charCode >= 48 && event.charCode <= 57", 'autocomplete' => 'off']) !!}
                    @endif
                        <span class="input-group-text">
                            {{ trans('personalChallenge.form.labels.days') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('description', trans('personalChallenge.form.labels.description')) }}
            @if(!empty($challengeData->description))
                {!! Form::textarea('description', old('description',$challengeData->description), ['id' => 'description', 'rows' => 5, 'class' => 'form-control','placeholder'=> trans('personalChallenge.form.placeholders.description'),'spellcheck'=>'false']) !!}
            @else
                {!! Form::textarea('description', old('description'), ['id' => 'description', 'rows' => 5, 'class' => 'form-control','placeholder'=> trans('personalChallenge.form.placeholders.description'),'spellcheck'=>'false']) !!}
            @endif
            </div>
        </div>
        <div class="col-lg-6 col-xl-8">
            <div class="form-group">
                @if($planAccess)
                {{ Form::label('challenge_type', trans('personalChallenge.form.labels.challenge_type')) }}
                @else
                {{ Form::label('challenge_type', trans('personalChallenge.form.labels.goal_type')) }}
                @endif
                <div>
                    @if(!empty($challengeData->challenge_type))
                    <label class="custom-radio">
                        {{ trans('personalChallenge.form.placeholders.routineplan') }}
                        {{ Form::radio('challengetype', 'routine', $challengeData->challenge_type == 'routine', ['class' => 'form-control challengetype', 'id' => 'routine', 'autocomplete' => 'off', 'disabled' => 'disabled']) }}
                        <span class="checkmark">
                        </span>
                        <span class="box-line">
                        </span>
                    </label>
                    <label class="custom-radio">
                        {{ trans('personalChallenge.form.placeholders.personalfitnesschallenge') }}
                        {{ Form::radio('challengetype', 'challenge', $challengeData->challenge_type == 'challenge', ['class' => 'form-control challengetype', 'id' => 'challenge', 'autocomplete' => 'off', 'disabled' => 'disabled']) }}
                        <span class="checkmark">
                        </span>
                        <span class="box-line">
                        </span>
                    </label>
                    <label class="custom-radio">
                        {{ trans('personalChallenge.form.placeholders.habbitplan') }}
                        {{ Form::radio('challengetype', 'habit', $challengeData->challenge_type == 'habit', ['class' => 'form-control challengetype', 'id' => 'habit', 'autocomplete' => 'off', 'disabled' => 'disabled']) }}
                        <span class="checkmark">
                        </span>
                        <span class="box-line">
                        </span>
                    </label>
                    @else
                    <label class="custom-radio">
                        {{ trans('personalChallenge.form.placeholders.routineplan') }}
                        {{ Form::radio('challengetype', 'routine', true, ['class' => 'form-control challengetype', 'id' => 'routine', 'autocomplete' => 'off']) }}
                        <span class="checkmark">
                        </span>
                        <span class="box-line">
                        </span>
                    </label>
                    <label class="custom-radio">
                        {{ trans('personalChallenge.form.placeholders.personalfitnesschallenge') }}
                        {{ Form::radio('challengetype', 'challenge', false, ['class' => 'form-control challengetype', 'id' => 'challenge', 'autocomplete' => 'off']) }}
                        <span class="checkmark">
                        </span>
                        <span class="box-line">
                        </span>
                    </label>
                    <label class="custom-radio">
                        {{ trans('personalChallenge.form.placeholders.habbitplan') }}
                        {{ Form::radio('challengetype', 'habit', false, ['class' => 'form-control challengetype', 'id' => 'habit', 'autocomplete' => 'off']) }}
                        <span class="checkmark">
                        </span>
                        <span class="box-line">
                        </span>
                    </label>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-6">
            <div class="form-group">
                {{ Form::label('type', trans('personalChallenge.form.labels.type')) }}
                <div class="routine challengetypeTabs" style="{{ (isset($challengeData) && $challengeData->challenge_type == 'challenge') ? 'display:none' : ''}}">
                    @if(!empty($challengeData->type))
                    <label class="custom-radio">
                        {{ trans('personalChallenge.form.placeholders.to-do') }}
                        {{ Form::radio('type', 'to-do', $challengeData->type == 'to-do', ['class' => 'form-control', 'id' => 'todo', 'autocomplete' => 'off', 'disabled' => 'disabled']) }}
                        <span class="checkmark">
                        </span>
                        <span class="box-line">
                        </span>
                    </label>
                    <label class="custom-radio" style="{{ (isset($challengeData) && $challengeData->challenge_type == 'habit') ? 'display:none' : ''}}">
                        {{ trans('personalChallenge.form.placeholders.streak') }}
                        {{ Form::radio('type', 'streak', $challengeData->type == 'streak', ['class' => 'form-control', 'id' => 'streak', 'autocomplete' => 'off', 'disabled' => 'disabled']) }}
                        <span class="checkmark">
                        </span>
                        <span class="box-line">
                        </span>
                    </label>
                    @else
                    <label class="custom-radio">
                        {{ trans('personalChallenge.form.placeholders.to-do') }}
                        {{ Form::radio('type', 'to-do', true, ['class' => 'form-control', 'id' => 'todo', 'autocomplete' => 'off']) }}
                        <span class="checkmark">
                        </span>
                        <span class="box-line">
                        </span>
                    </label>
                    <label class="custom-radio">
                        {{ trans('personalChallenge.form.placeholders.streak') }}
                        {{ Form::radio('type', 'streak', false, ['class' => 'form-control', 'id' => 'streak', 'autocomplete' => 'off']) }}
                        <span class="checkmark">
                        </span>
                        <span class="box-line">
                        </span>
                    </label>
                    @endif
                </div>
                <div class="challenge challengetypeTabs" style="{{ (isset($challengeData) && $challengeData->challenge_type != 'challenge') ? 'display:none' : (!isset($challengeData) ? 'display: none' : '')}}">
                    @if(!empty($challengeData->type))
                    <label class="custom-radio">
                        {{ trans('personalChallenge.form.placeholders.steps') }}
                        {{ Form::radio('type', 'steps', $challengeData->type == 'steps', ['class' => 'form-control fitnesstype', 'id' => 'steps', 'autocomplete' => 'off', 'disabled' => 'disabled']) }}
                        <span class="checkmark">
                        </span>
                        <span class="box-line">
                        </span>
                    </label>
                    <label class="custom-radio">
                        {{ trans('personalChallenge.form.placeholders.distance') }}
                        {{ Form::radio('type', 'distance', $challengeData->type == 'distance', ['class' => 'form-control fitnesstype', 'id' => 'distance', 'autocomplete' => 'off', 'disabled' => 'disabled']) }}
                        <span class="checkmark">
                        </span>
                        <span class="box-line">
                        </span>
                    </label>
                    <label class="custom-radio">
                        {{ trans('personalChallenge.form.placeholders.meditation') }}
                        {{ Form::radio('type', 'meditations', $challengeData->type == 'meditations', ['class' => 'form-control fitnesstype', 'id' => 'meditations', 'autocomplete' => 'off', 'disabled' => 'disabled']) }}
                        <span class="checkmark">
                        </span>
                        <span class="box-line">
                        </span>
                    </label>
                    @else
                    <label class="custom-radio">
                        {{ trans('personalChallenge.form.placeholders.steps') }}
                        {{ Form::radio('type', 'steps', false, ['class' => 'form-control fitnesstype', 'id' => 'steps', 'autocomplete' => 'off']) }}
                        <span class="checkmark">
                        </span>
                        <span class="box-line">
                        </span>
                    </label>
                    <label class="custom-radio">
                        {{ trans('personalChallenge.form.placeholders.distance') }}
                        {{ Form::radio('type', 'distance', false, ['class' => 'form-control fitnesstype', 'id' => 'distance', 'autocomplete' => 'off']) }}
                        <span class="checkmark">
                        </span>
                        <span class="box-line">
                        </span>
                    </label>
                    <label class="custom-radio">
                        {{ trans('personalChallenge.form.placeholders.meditation') }}
                        {{ Form::radio('type', 'meditations', false, ['class' => 'form-control fitnesstype', 'id' => 'meditations', 'autocomplete' => 'off']) }}
                        <span class="checkmark">
                        </span>
                        <span class="box-line">
                        </span>
                    </label>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-6 challenge challengetypeTabs" style="{{ (isset($challengeData) && $challengeData->challenge_type != 'challenge') ? 'display:none' : (!isset($challengeData) ? 'display: none' : '')}}">
            <div class="form-group">
                <div>
                    {{ Form::label('Target Value', trans('personalChallenge.form.labels.target_value')) }}
                    <div class="input-group">
                        @if(isset($challengeData))
                            {{ Form::number('target_value', old('target_value',$challengeData->target_value), ['class' => 'form-control', 'placeholder' => trans('personalChallenge.form.labels.target_value'), 'id' => 'target_value', 'autocomplete' => 'off', 'disabled' => 'disabled']) }}
                        @else
                            {{ Form::number('target_value', old('target_value'), ['class' => 'form-control', 'placeholder' => trans('personalChallenge.form.labels.target_value'), 'id' => 'target_value', 'autocomplete' => 'off']) }}
                        @endif
                        <span class="input-group-text target-value-type" id="counts" style="{{ (isset($challengeData) && $challengeData->type != 'steps') ? 'display:none' : ''}}">
                            {{ trans('personalChallenge.form.labels.counts') }}
                        </span>
                        <span class="input-group-text target-value-type" id="meter" style="{{ (isset($challengeData) && $challengeData->type != 'distance') ? 'display:none' : (!isset($challengeData) ? 'display: none' : '')}}">
                            {{ trans('personalChallenge.form.labels.meter') }}
                        </span>
                        <span class="input-group-text target-value-type" id="minutes" style="{{ (isset($challengeData) && $challengeData->type != 'meditations') ? 'display:none' : (!isset($challengeData) ? 'display: none' : '')}}">
                            {{ trans('personalChallenge.form.labels.counts') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-6" id="isRecursive" style="{{ (isset($challengeData) && $challengeData->challenge_type == 'habit') ? 'display:none' : ''}}">
            <div class="form-group">
                <label class="custom-checkbox no-label">
                    {{ trans('personalChallenge.form.labels.is_recursive') }}
                    @if(isset($challengeData))
                    {{ Form::checkbox('is_recursive', 1, old('is_recursive', isset($challengeData) && $challengeData->recursive), array('disabled')) }}
                    @else
                        {{ Form::checkbox('is_recursive', 1, old('is_recursive')) }}
                    @endif
                    <span class="checkmark">
                    </span>
                    <span class="box-line">
                    </span>
                </label>
            </div>
        </div>
    </div>
</div>
<div class="card-inner routine challengetypeTabs" style="{{ (isset($challengeData) && $challengeData->challenge_type == 'challenge') ? 'display:none' : ''}}">
    <h3 class="card-inner-title">
        {{ trans('personalChallenge.form.labels.tasks') }}
    </h3>
    <div class="form-group col-md-8" id="hideTaskAdd" style="display: none">
        <table class="table custom-table no-hover task-table gap-adjust no-border">
            <tbody>
                <tr>
                    <td class="th-btn-4">
                        {{ Form::text('task', null, ['id' => 'task', 'class' => 'form-control single_task_required', 'placeholder' => trans('personalChallenge.form.placeholders.task'), 'maxlength' => 50]) }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="form-group col-md-8" id="showTaskAdd">
        <table class="table custom-table no-hover task-table gap-adjust no-border ingriadiant-make-editable" id="ingriadiantTbl">
            <tbody>
                @forelse ($tasksData ?? [] as $key => $task)
                <tr>
                    <td>
                        {{ Form::text("ingredients[$key]", $task, ['id' => "ingredients[$key]", 'class' => 'form-control ingredients_required', 'placeholder' => trans('personalChallenge.form.placeholders.tasks')]) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td>
                        {{ Form::text('ingredients[0]', null, ['class' => 'form-control ingredients_required', 'placeholder' => trans('personalChallenge.form.placeholders.tasks'), 'maxlength' => 50]) }}
                    </td>
                    <td style="{{ (isset($challengeData) && $challengeData->challenge_type == 'habit') ? 'display:none' : ''}}">
                        <a class="add-task action-icon ingriadiant-remove text-danger" href="javascript::void(0)" title="{{ trans('personalChallenge.form.tooltips.delete') }}">
                            <i class="far fa-trash">
                            </i>
                        </a>
                        <a class="add-task action-icon text-info" href="javascript::void(0)" id="ingriadiantAdd" title="{{ trans('personalChallenge.form.tooltips.add') }}">
                            <i class="far fa-plus text-success">
                            </i>
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <script id="ingredientsTemplate" type="text/html">
        <tr>
           <td>
            {{ Form::text('ingredients[:ingdCount]', null, ['class' => 'form-control ingredients_required', 'placeholder' => trans('personalChallenge.form.placeholders.tasks'), 'maxlength' => 50]) }}
           </td>
           <td class="show_del">
               <a class="add-task action-icon ingriadiant-remove text-danger" href="javascript::void(0)" title="{{ trans('personalChallenge.form.tooltips.delete') }}">
                   <i class="far fa-trash"></i>
               </a>
                <a class="add-task action-icon text-info" id="ingriadiantAdd" href="javascript::void(0)" title="{{ trans('personalChallenge.form.tooltips.add') }}">
                    <i class="far fa-plus text-success">
                    </i>
                </a>
           </td>
       </tr>
    </script>
</div>