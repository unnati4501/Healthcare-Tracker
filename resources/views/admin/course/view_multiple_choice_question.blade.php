<h3>
</h3>
<section class="step-box" data-q-type="{{ $question->type }}" data-step="{{ $step }}">
    <div class="row align-items-center">
        <div class="col-lg-12 align-self-center text-center">
            <div class="ans-main-area question-type-one m-0-a">
                <div class="text-center">
                    <p class="question-text-title mb-4">
                        {{ $survey->title }}
                    </p>
                    <h2 class="question-text">
                        {{ $question->title }}
                    </h2>
                </div>
                <div class="text-center w-100 mb-3">
                    <img class="img-fluid m-b-md-30 m-b-15 qus-banner-img" src="{{ $question->getLogo(['w' => 640, 'h' => 320]) }}"/>
                </div>
                <div class="cu-fade slow masterclass-check-area">
                    @php
                        $options = $question->coursesurveyoptions()->get();
                    @endphp

                    @foreach($options as $key => $option)
                    <label class="custom-checkbox">
                        {{ $option->choice }}
                        <input id="answers[{{ $question->question_id }}][{{ $option->id }}]" name="answers[{{ $question->question_id }}][{{ $option->id }}]" type="checkbox" value="{{ $option->id }}">
                            <span class="checkmark">
                            </span>
                            <span class="checkbox-line">
                            </span>
                        </input>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>