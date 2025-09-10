<h3>
</h3>
{{-- data-q="{{ $question->question_id }}" data-q-type="{{ $question->question_type_id }}" data-step="{{ $step }}" --}}
<section class="step-box container">
    <div class="row align-items-center">
        <div class="col-lg-12 text-center">
            <div class="ans-main-area question-type-one m-0-a">
                <div class="text-center">
                    <p class="question-text-title">
                        Question ({{ ($step + 1) }} of {{ $total_questions }})
                    </p>
                    <h1 class="question-text">
                        {{ $question->question->title }}
                    </h1>
                </div>
                <div class="text-center w-100 mb-3">
                    <img class="img-fluid m-b-md-30 m-b-15 qus-banner-img placeholder-image" onerror="imgLoadhandler(this)" onload="imgLoadhandler(this)" src="{{ $question->question->question_logo }}"/>
                </div>
                <div class="form-group ans-textarea">
                    {{ Form::textarea("answers[$question->question_id]", old("answers[$question->question_id]", null), ['class' => 'form-control cu-fade slow h-auto', 'rows' => 5, "id" => "answers[$question->question_id]", "placeholder" => "Please enter your answer", "data-key-validation" => "true"]) }}
                    {{ Form::hidden("category[$question->question_id]", $question->category_id, ["id" => "category[$question->question_id]"]) }}
                    {{ Form::hidden("subcategory[$question->question_id]", $question->sub_category_id, ["id" => "subcategory[$question->question_id]"]) }}
                    {{ Form::hidden("qtype[$question->question_id]", $question->question_type_id, ["id" => "qtype[$question->question_id]"]) }}
                    {{ Form::hidden("max_score[$question->question_id]", "", ["id" => "max_score[$question->question_id]"]) }}
                    {{ Form::hidden("option_id[$question->question_id]", "", ["id" => "option_id[$question->question_id]"]) }}
                </div>
            </div>
        </div>
    </div>
</section>