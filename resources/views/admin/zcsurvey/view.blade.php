@extends('layouts.app')

@section('before-styles')
<link href="{{ asset('assets/plugins/step/jquery.steps.css?var='.rand()) }}" rel="stylesheet"/>
{{-- <link href="https://fonts.googleapis.com/css?family=Raleway:400,500,600,700&display=swap?var=<?= rand() ?>" rel="stylesheet"/> --}}
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
<div aria-hidden="false" aria-labelledby="surveyPreviewModalLabel" class="modal fade full-screen-popup" data-keyboard="false" data-show="true" id="surveyPreview" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-area">
                <div class="modal-header">
                    <h5 class="modal-title" id="surveyPreviewModalLabel">
                        {{ trans('labels.zcsurvey.surevy_preview') }}
                    </h5>
                    <button class="close closePreview" type="button">
                        <i class="fal fa-times">
                        </i>
                    </button>
                </div>
                <div class="modal-body">
                    <section class="container-fluid">
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
                            @endforeach
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('after-scripts')
<script src="{{ asset('assets/plugins/step/jquery.steps.js?var='.rand()) }}">
</script>
<script type="text/javascript">
    function imgLoadhandler(item) { $(item).removeClass('placeholder-image'); }
    function updateProgrssbar(percentage) {
        $('#horizontalProgressBar')
            .attr('aria-valuenow', percentage)
            .css('width', `${percentage}%`)
            .html(`${Math.floor(percentage)}%`);
        $('#verticalProgressBar')
            .css('height', `${percentage}%`)
            .html(`${Math.floor(percentage)}%`);
    }
    $(document).ready(function() {
        var totalQs = {{ $total_questions }};
        $('#surveyPreview').modal('show');
        $("#userQuestion").steps({
            headerTag: "h3",
            bodyTag: "section",
            transitionEffect: "fade",
            loadingTemplate: `<div class="align-self-center"><i class="fa fa-spinner fa-spin"></i> #text#</div>`,
            labels: {
                loading: "Loading question..."
            },
            autoFocus: true,
            onStepChanging: function (event, currentIndex, newIndex) {
                return true;
            },
            onStepChanged: function (event, currentIndex, priorIndex) {
                updateProgrssbar((currentIndex * 100) / totalQs);
            },
            onFinishing: function(event, currentIndex) {
                updateProgrssbar(100);
                return true;
            },
            onFinished: function (event, currentIndex) {
                window.location.href = "{{ route('admin.zcsurvey.index') }}";
            }
        });
        $(document).on('click', '.closePreview', function(e) {
            e.preventDefault();
            window.location.href = "{{ route('admin.zcsurvey.index') }}";
        });
        $(document).on('change, click', 'input[type="radio"][data-skip-on-selection="true"]', function(e) {
            setTimeout(function() {
                $("#userQuestion").steps('next');
            }, 150);
        });
    });
</script>
@endsection
