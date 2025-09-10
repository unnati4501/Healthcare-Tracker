<div class="col-xl-4">
    <div class="form-group">
        <div>
            <label class="custom-checkbox no-label">
                {{ trans('broadcast.form.labels.instant_broadcast') }}
                {{ Form::checkbox('instant_broadcast', 'on', (!$edit ? true : !($broadcast->type == 'scheduled')), ['class' => 'form-control','id' => "instant_broadcast"]) }}
                <span class="checkmark">
                </span>
                <span class="box-line">
                </span>
            </label>
        </div>
    </div>
</div>
<div class="col-xl-4">
    <div class="form-group">
        {{ Form::label('title', trans('broadcast.form.labels.title')) }}
        {{ Form::text('title', old('title', ($broadcast->title ?? null)), ['class' => 'form-control', 'placeholder' => trans('broadcast.form.placeholder.enter_title'), 'id' => 'title', 'autocomplete' => 'off']) }}
    </div>
</div>
<div class="col-xl-4 {{ $scheduledVisibility }}" id="scheduled_wrapper">
    <div class="form-group">
        {{ Form::label('schedule_date_time', trans('broadcast.form.labels.date_time')) }}
        {{ Form::text('schedule_date_time', old('schedule_date_time', ($scheduled_at ?? null)), ['class' => 'form-control bg-white', 'placeholder' => trans('broadcast.form.placeholder.select_schedule_at'), 'id' => 'schedule_date_time', 'autocomplete' => 'off', 'readonly' => true]) }}
    </div>
</div>
<div class="col-xl-4">
    <div class="form-group">
        {{ Form::label('message', trans('broadcast.form.labels.message')) }}
        {{ Form::textarea('message', old('message', ($broadcast->message ?? null)), ['id' => 'message', 'rows' => 6, 'class' => 'form-control', 'placeholder' => trans('broadcast.form.placeholder.enter_message')]) }}
    </div>
</div>
<div class="col-xl-4">
    <div class="form-group">
        {{ Form::label('group_type', trans('broadcast.form.labels.group_type')) }}
        {{ Form::select('group_type', $groupsType, old('group_type', ($broadcast->group_type ?? null)), ['class' => 'form-control select2', 'placeholder' => trans('broadcast.form.placeholder.select_group_type'), 'data-placeholder' => trans('broadcast.form.placeholder.select_group_type'), 'id' => 'group_type', 'data-allow-clear' => 'true', 'disabled' => $edit]) }}
    </div>
</div>
<div class="col-xl-4">
    <div class="form-group">
        {{ Form::label('group', trans('broadcast.form.labels.group')) }}
        {{ Form::select('group', ($group ?? []), old('group', ($broadcast->group_id ?? null)), ['class' => 'form-control select2', 'placeholder' => trans('broadcast.form.placeholder.select_group'), 'data-placeholder' => trans('broadcast.form.placeholder.select_group'), 'id' => 'group', 'data-allow-clear' => 'true', 'disabled' => true]) }}
    </div>
</div>

