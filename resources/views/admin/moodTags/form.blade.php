<div class="row">
    <div class="col-lg-6 col-xl-4">
        <div class="form-group">
            {{ Form::label('tag', trans('moods.tags.form.labels.name')) }}
            {{ Form::text('tag', old('tag', ($record->tag ?? null)), ['class' => 'form-control', 'placeholder' => trans('moods.tags.form.placeholders.name'), 'id' => 'tag', 'autocomplete' => 'off']) }}
        </div>
    </div>
</div>