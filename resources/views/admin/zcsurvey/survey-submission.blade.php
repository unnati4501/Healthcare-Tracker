@extends('layouts.survey')

@section('before-styles')
<link href="{{ asset('assets/plugins/step/jquery.steps.css?var='.rand()) }}" rel="stylesheet"/>
<style type="text/css">
    #userQuestion .wiz-tablist { display: none; }
    .user-question-main.wizard .content .body.question-section {
        display: flex;
        min-height: calc(100vh - 145px);
        position: static;
        width: 100%;
        height:100%;
        float: none;
    }
    .question-section .step-box {
        flex-grow: 1;
        flex-shrink: 0;
    }
</style>
@endsection

@section('content')
@if($alreadySubmitted == true)
<div class="thank-you-area-main" id="alreadySubmitted">
    <div class="thank-you-inner animated cu-fade slow">
        <nav class="navbar navbar-expand-lg navbar-light border-bottom">
            <div class="container">
                <div class="ms-auto me-auto">
                    <span class="user-side-logo">
                        <img alt="{{ config('app.name', env('APP_NAME')) }}" src="{{ $branding->company_logo }}"/>
                    </span>
                </div>
            </div>
        </nav>
        <div class="thank-you-imagearea pt-5">
            <img class="error-msg-img mb-4" src="{{ asset('assets/dist/img/icons/filled.svg') }}"/>
        </div>
        <h5 class="mt-4 mt-xl-5 mb-4 mb-xl-5 fw-normal ps-3 pe-3">
            {{ __('Are you sure you want to resubmit the survey?') }}
        </h5>
        <div class="thank-you-imagearea">
            <button class="btn btn-primary btn-effect mm-w-100" id="confirm-button" type="button">
                Yes
            </button>
            <button class="btn btn-effect btn-outline-secondary me-2 mm-w-100" id="cancel-button" type="button">
                No
            </button>
        </div>
    </div>
</div>
@endif
<section class="container-fluid" id="submit-survey" @if($alreadySubmitted == true) style="display: none;" @endif>
    {{ Form::open(['route' => ["submitSurvey", $surveyId], 'method' => 'post', 'role' => 'form', 'id' => 'submitsurvey']) }}
    <div class="d-lg-none mobile-progress mb-4">
        <div class="progress">
            <div aria-valuemax="100" aria-valuemin="0" aria-valuenow="0" class="progress-bar" id="horizontalProgressBar" role="progressbar" style="width: 0%;">
            </div>
        </div>
    </div>
    <div class="survey-progressbar d-none d-lg-block">
        <div class="progress vertical-progress">
            <span class="progress-bar" id="verticalProgressBar" style="height: 0%;">
            </span>
        </div>
    </div>
    <div class="user-question-main" id="userQuestion">
        @foreach($questions as $key => $question)
        <h3>
        </h3>
        <section class="step-box question-section justify-content-center" data-mode="async" data-q="{{ $question->question_id }}" data-q-type="{{ $question->question_type_id }}" data-step="{{ $key }}" data-url="{{ route('getSurveyQuestion', [encrypt($question->id . ':' . $key . ':' . $total_questions), $question->id]) }}">
        </section>
        {{-- @if($question->question_type_id == 1)
            @include('admin.zcsurvey.freetext_question_preview', ['question' => $question, 'step' => $key])
        @elseif($question->question_type_id == 2)
            @include('admin.zcsurvey.choice_question_preview', ['question' => $question, 'step' => $key])
        @endif --}}
        @endforeach
    </div>
    {{ Form::close() }}
</section>
<div class="thank-you-area-main" id="survey_submitted" style="display: none;">
    <div class="thank-you-inner animated cu-fade slow">
        <nav class="navbar navbar-expand-lg navbar-light border-bottom">
            <div class="container">
                <div class="ms-auto me-auto">
                    <span class="user-side-logo">
                        <img alt="{{ config('app.name', env('APP_NAME')) }}" src="{{ $branding->company_logo }}"/>
                    </span>
                </div>
            </div>
        </nav>
        <div class="thank-you-text-area">
            @if($alreadySubmitted == false)
            <div class="success-msg">
                {{ __("Thank you for your survey submission")  }}
            </div>
            <div class="thank-you-imagearea">
                <img class="error-msg-img mb-4" src="{{ asset('assets/dist/img/icons/filled.svg') }}"/>
            </div>
            <p class="">
                {{ __('If you have feedback or suggestions, then please provide these below.') }}
            </p>
            @else
             <div class="success-msg">
                {{ __("Thank you for your survey submission")  }}
            </div>
            <div class="thank-you-imagearea">
                <img class="error-msg-img mb-4" src="{{ asset('assets/dist/img/icons/filled.svg') }}"/>
            </div>
            @endif
        </div>
        @if($alreadySubmitted)
        {{ Form::open(['route' => ['storeSurveyReview', $surveyId], 'class' => 'suggestion-form', 'method'=>'post', 'role' => 'form', 'id' => 'surveyReviewForm']) }}
        <div class="arrow-effect">
            <i class="fal fa-long-arrow-down">
            </i>
        </div>
        <div class="form-group pt-5">
            {{-- {{ Form::label('survey_comments', __('Type comments, suggestion or feedback here.')) }} --}}
            {{ Form::textarea('survey_comments', null, ['class' => 'form-control', 'placeholder' => __('Type comments, suggestion or feedback here.'), 'id' => 'survey_comments', 'rows' => 4]) }}
        </div>
        <div class="text-center">
            <button class="btn btn-primary" title="Submit" type="submit">
                {{ __('Submit') }}
            </button>
        </div>
        {{ Form::close() }}
        @endif
    </div>
</div>
<div class="thank-you-area-main" id="feedback_submitted" style="background-color: #ffffff; display: none;">
    <nav class="navbar navbar-expand-lg navbar-light border-bottom">
        <div class="container">
            <div class="ms-auto me-auto">
                <span class="user-side-logo">
                    <img alt="{{ config('app.name', env('APP_NAME')) }}" src="{{ $branding->company_logo }}"/>
                </span>
            </div>
        </div>
    </nav>
    <section class="container">
        <div class="feedback-box-wrapper">
            <div class="user-question-main question-feedback-page" id="userFeedback">
                <section class="step-box">
                    <div class="question-feedback-area mt-5">
                        <div class="ans-main-area question-type-one m-0-a">
                            <div class="text-center">
                                <img class="mb-4" src="{{ asset('assets/dist/img/feedback/feedback-thank-you.svg') }}"/>
                                <h1 class="question-text mb-4">
                                    {{ __('We really appreciate your feedback.') }}
                                </h1>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </section>
</div>
@endsection
@section('after-scripts')
{!! $validator = JsValidator::formRequest('App\Http\Requests\Admin\StoreZcSurveyResponse', '#submitsurvey') !!}
{!! $validator = JsValidator::formRequest('App\Http\Requests\Admin\StoreZcSurveyReviewSuggestion', '#surveyReviewForm') !!}
<script type="text/javascript">
    function imgLoadhandler(item) { $(item).removeClass('placeholder-image'); }
    var totalQuestions = {{ $total_questions }},
        surveyId = "{{ $surveyId }}",
        sessionDataExsit = sessionStorage.getItem(`${surveyId}`),
        _aary = {};

    $(document).ready(function(){
        $('#confirm-button').click(function(){
            $('#alreadySubmitted').hide();
            $('#submit-survey').show();
        });
        $('#cancel-button').click(function(){
            window.location.href = '{{ $surveyBrandingURL }}';
        });
    });
</script>
<script src="{{ asset('assets/plugins/step/jquery.steps.js?var='.rand()) }}">
</script>
<script src="{{ mix('js/zcsurvey/zcSurveySubmit.js') }}">
</script>
@endsection
