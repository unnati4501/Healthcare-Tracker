<div class="row">
    <div class="col-xl-12">
        <div class="row">
            <div class="col-lg-12">
                <div class="form-group col-sm-12">
                    {{ Form::label('title', trans('labels.course.title')) }}
                    @if(!empty($record->title))
                        {{ Form::text('title', old('title', $record->title), ['class' => 'form-control', 'placeholder' => 'Enter Course Module', 'id' => 'title', 'autocomplete' => 'off']) }}
                    @else
                        {{ Form::text('title', old('title'), ['class' => 'form-control', 'placeholder' => 'Enter Course Module', 'id' => 'title', 'autocomplete' => 'off']) }}
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>