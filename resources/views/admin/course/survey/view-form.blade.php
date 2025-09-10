<div class="qustion-main-area">
    <div class="row">
        <div class="form-group col-sm-12 col-lg-7">
            {{ Form::text('title', old('title', ($record->title ?? null)), ['class' => 'form-control', 'placeholder' => 'Enter Title', 'id' => 'title', 'name' => 'title', 'autocomplete' => 'off', 'max' => 50]) }}
        </div>
    </div>
    <hr class="mt-0"/>
    <div class="qus-row-box">
        <div class="questions-block mb-2">
            @foreach ($survey_questions as $question)
                @include('admin.course.survey.question_block', ["id" => $question->getKey(), "oid" => 1, "view" => true, "edit" => true, "question" => $question, 'options' => $question->courseSurveyOptions()->get()])
            @endforeach
        </div>
    </div>
</div>