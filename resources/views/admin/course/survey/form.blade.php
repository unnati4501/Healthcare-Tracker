<div class="row">
    <div class="col-sm-12 col-lg-7">
        <div class="form-group">
            {{ Form::text('title', old('title', ($record->title ?? null)), ['class' => 'form-control', 'placeholder' => trans('masterclass.survey.form.placeholder.title'), 'id' => 'title', 'name' => 'title', 'autocomplete' => 'off', 'maxlength' => 150]) }}
        </div>
    </div>
</div>
<hr class="mt-0"/>
<div class="qus-row-box">
    <div class="questions-block mb-2">
        @forelse ($survey_questions as $key => $question)
            @include('admin.course.survey.question_block', ["id" => $question->getKey(), "oid" => 1, "edit" => true, "question" => $question, 'options' => $question->courseSurveyOptions()->get()])
        @empty
            @include('admin.course.survey.question_block', ["id" => 1, "oid" => 1, "edit" => false, "question" => [], "options" => []])
        @endforelse
    </div>
    @if(!$courseStatus)
    <button class="btn btn-outline-primary mt-5 add_question" type="button">
        <i class="far fa-plus me-1">
        </i>
        {{ trans('masterclass.survey.buttons.add_question') }}
    </button>
    @endif
</div>
@if($questions_count > 0)
<input name="deleted_questions" type="hidden" value=""/>
<input name="deleted_options" type="hidden" value=""/>
@endif
