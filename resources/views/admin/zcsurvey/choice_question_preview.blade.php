<h3>
</h3>
{{-- data-q="{{ $question->question_id }}" data-q-type="{{ $question->question_type_id }}" data-step="{{ $step }}" --}}
<section class="step-box container">
    <div class="row align-items-center">
        <div class="col-lg-12 align-self-center text-center">
            <div class="ans-main-area question-type-one m-0-a">
                <div class="text-center">
                    <p class="question-text-title">
                        Question ({{ ($step + 1) }} of {{ $total_questions }})
                    </p>
                    <h2 class="question-text">
                        {{ $question->question->title }}
                    </h2>
                </div>
                <div class="text-center w-100 mb-3">
                    <img class="img-fluid m-b-md-30 m-b-15 qus-banner-img placeholder-image" onerror="imgLoadhandler(this)" onload="imgLoadhandler(this)" src="{{ $question->question->question_logo }}"/>
                </div>
                <div class="cu-fade slow choices-main-box">
                    @php
                        $options = $question->questionoptions()->where('choice', '!=', 'meta')->get();
                        $maxScore = (int) $question->questionoptions()->max('score');
                    @endphp

                    @foreach($options as $key => $option)
                    <label class="choices-item-box">
                        <input data-oid="{{ $option->id }}" data-skip-on-selection="true" id="answers[{{ $question->question_id }}][{{ $option->id }}]" name="answers[{{ $question->question_id }}]" type="radio" value="{{ $option->score }}">
                            <div class="markarea">
                                <span class="checkmark animated tada faste">
                                </span>
                                <div class="choices-item-img">
                                    @php
                                        $optionImage = $option->option_logo;
                                        if (Str::contains($optionImage, '73a90acaae2b1ccc0e969709665bc62f')) {
                                            $optionImage = asset('assets/dist/img/choice-' . ($key + 1) . '.png');
                                        }
                                    @endphp
                                    <img class="placeholder-image" onerror="imgLoadhandler(this)" onload="imgLoadhandler(this)" src="{{ $optionImage }}"/>
                                </div>
                            </div>
                            <div class="choices-box-title">
                                {{ $option->choice }}
                            </div>
                        </input>
                    </label>
                    @endforeach
                    {{ Form::hidden("category[$question->question_id]", $question->category_id, ["id" => "category[$question->question_id]"]) }}
                    {{ Form::hidden("subcategory[$question->question_id]", $question->sub_category_id, ["id" => "subcategory[$question->question_id]"]) }}
                    {{ Form::hidden("qtype[$question->question_id]", $question->question_type_id, ["id" => "qtype[$question->question_id]"]) }}
                    {{ Form::hidden("max_score[$question->question_id]", $maxScore, ["id" => "max_score[$question->question_id]"]) }}
                    {{ Form::hidden("option_id[$question->question_id]", null, ["id" => "option_id[$question->question_id]"]) }}
                </div>
            </div>
        </div>
    </div>
</section>