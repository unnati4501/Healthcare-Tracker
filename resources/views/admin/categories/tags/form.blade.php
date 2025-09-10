<div class="col-lg-6 col-xl-4">
    <div class="form-group">
        {{ Form::label('category', trans('categories.tags.form.labels.category')) }}
        {{ Form::select('category', $categories, ($tag->category_id ?? null), ['class' => 'form-control select2', 'id' => 'category', 'placeholder' => trans('categories.tags.form.placeholder.category'), 'data-placeholder' => trans('categories.tags.form.placeholder.category'), 'disabled' => $edit]) }}
    </div>
</div>
<div class="col-lg-6 col-xl-4">
    <div class="form-group">
        {{ Form::label('name', trans('categories.tags.form.labels.name')) }}
        {{ Form::text('name', old('name', ($tag->name ?? null)), ['class' => 'form-control', 'placeholder' => trans('categories.tags.form.placeholder.name'), 'id' => 'name', 'autocomplete' => 'off']) }}
    </div>
</div>