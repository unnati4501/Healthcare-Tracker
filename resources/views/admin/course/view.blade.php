@extends('layouts.app')

@section('before-styles')
<link href="{{ asset('assets/plugins/step/jquery.steps.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content')
<div aria-hidden="false" aria-labelledby="surveyPreviewModalLabel" class="modal fade full-screen-popup" data-keyboard="false" data-show="true" id="surveyPreview" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-area mclass-modal-area">
                <div class="modal-header">
                    <a class="modal-title-nevigation active preSurvey" data-tab="preSurvey" href="javascript:void(0);">
                        {{ trans('masterclass.view.labels.pre_survey') }}
                    </a>
                    <a class="modal-title-nevigation ms-auto surveyLessons" data-tab="surveyLessons" href="javascript:void(0);">
                        {{ trans('masterclass.view.labels.lessons') }}
                    </a>
                    <a class="modal-title-nevigation ms-auto postSurvey" data-tab="postSurvey" href="javascript:void(0);">
                        {{ trans('masterclass.view.labels.post_survey') }}
                    </a>
                    <a class="modal-title-nevigation-close-btn close" href="{{ route('admin.masterclass.index') }}" title="{{ trans('masterclass.view.tooltip.close_preview') }}">
                        <i class="fal fa-times">
                        </i>
                    </a>
                    @if($lessons->isNotEmpty())
                    <a class="btn btn-outline-primary preview-edit-btn" href="javascript:void(0);" id="edit-lesson" style="display: none;">
                        <i class="fal fa-edit">
                        </i>
                        <span>
                            {{ trans('masterclass.view.buttons.edit_lesson') }}
                        </span>
                    </a>
                    @endif
                </div>
                <div class="modal-body">
                    <section class="container">
                        <div class="user-question-main" id="preSurvey" style="display: none;">
                            @if(!is_null($pre_survey))
                                @foreach($pre_survey->surveyquestions as $key => $question)
                                    @if($question->type == "single_choice")
                                        @include('admin.course.view_single_choice_question', ['question' => $question, 'step' => $key, 'survey' => $pre_survey])
                                    @elseif($question->type == "multiple_choice")
                                        @include('admin.course.view_multiple_choice_question', ['question' => $question, 'step' => $key, 'survey' => $pre_survey])
                                    @endif
                                @endforeach
                            @else
                            <section class="step-box">
                                <div class="row align-items-center">
                                    <div class="col-lg-12 text-center">
                                        <div class="ans-main-area question-type-one m-0-a">
                                            <div class="no-pre-survey">
                                                <div class="alert alert-danger text-center" role="alert">
                                                    {{ trans('masterclass.view.messages.no_pre_survey') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                            @endif
                        </div>
                        <div class="user-question-main" id="surveyLessons" style="display: none;">
                            @if($lessons->isNotEmpty())
                                @foreach($lessons as $key => $lesson)
                                    @include('admin.course.view_lessons', ['lesson' => $lesson, 'step' => $key, 'total_lessons' => $total_lessons])
                                @endforeach
                            @else
                            <section class="step-box">
                                <div class="row align-items-center">
                                    <div class="col-lg-12 text-center">
                                        <div class="ans-main-area question-type-one m-0-a">
                                            <div class="no-pre-survey">
                                                <div class="alert alert-danger text-center" role="alert">
                                                    {{ trans('masterclass.view.messages.no_lessons') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                            @endif
                        </div>
                        <div class="user-question-main" id="postSurvey" style="display: none;">
                            @if(!is_null($post_survey))
                                @foreach($post_survey->surveyquestions as $key => $question)
                                    @if($question->type == "single_choice")
                                        @include('admin.course.view_single_choice_question', ['question' => $question, 'step' => $key, 'survey' => $post_survey])
                                    @elseif($question->type == "multiple_choice")
                                        @include('admin.course.view_multiple_choice_question', ['question' => $question, 'step' => $key, 'survey' => $post_survey])
                                    @endif
                                @endforeach
                            @else
                            <section class="step-box">
                                <div class="row align-items-center">
                                    <div class="col-lg-12 text-center">
                                        <div class="ans-main-area question-type-one m-0-a">
                                            <div class="no-pre-survey">
                                                <div class="alert alert-danger text-center" role="alert">
                                                    {{ trans('masterclass.view.messages.no_post_survey') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                            @endif
                        </div>
                    </section>
                    <div class="thank-you-area-main" id="masterclass-preview">
                        <div class="thank-you-inner animated cu-fade slow">
                            <div class="thank-you-text-area border-0 bg-transparent">
                                <div class="success-msg">
                                    {{ $course->title }}
                                </div>
                                <div class="mb-3 author-name-area">
                                    <span class="me-3">
                                        <i class="fas fa-user text-primary">
                                        </i>
                                        <span class="gray-600">
                                            {{ $creator_data['name'] }}
                                        </span>
                                    </span>
                                    <span class="">
                                        <i class="fas fa-tag text-primary">
                                        </i>
                                        <span class="gray-600">
                                            {{ $course->subcategory->name }}
                                        </span>
                                    </span>
                                </div>
                                <div class="text-area">
                                    {!! $course->instructions !!}
                                </div>
                            </div>
                            <div class="suggestion-form">
                                @if($course->trailer_type == 1)
                                <div class="mus-player">
                                    <img class="mb-0" src="{{ $course->getTrailerBackground(['w' => 640, 'h' => 320, 'zc' => 1]) }}"/>
                                    <audio class="audio-player" controls="" controlslist="nodownload">
                                        <source src="{{ $course->getFirstMediaUrl('trailer_audio') }}">
                                        </source>
                                    </audio>
                                </div>
                                @elseif($course->trailer_type == 2)
                                <video class="begin-video" controls="" controlslist="nodownload" poster="{{ $course->getVideoTrailerBackground(['w' => 1280, 'h' => 720, 'conversion' => 'th_lg', 'zc' => 1]) }}">
                                    <source src="{{ $course->getFirstMediaUrl('trailer_video') }}" type="video/mp4">
                                        Your browser does not support the video element.
                                    </source>
                                </video>
                                @elseif($course->trailer_type == 3)
                                <iframe allowfullscreen="" frameborder="0" height="350" src="https://www.youtube.com/embed/{{ $course->getFirstMedia('track')->getCustomProperty('ytid') }}?playsinline=1&rel=0&showinfo=0&color=white" width="100%">
                                </iframe>
                                @elseif($course->trailer_type == 4)
                                <iframe allowfullscreen="" frameborder="0" height="350" src="https://player.vimeo.com/video/{{ $course->getFirstMedia('track')->getCustomProperty('vmid') }}?playsinline=1&rel=0&showinfo=0&color=white" width="100%">
                                </iframe>
                                @endif
                                <div class="mt-4 text-center">
                                    <button class="btn btn-effect btn-primary mm-w-100" id="brginClass" type="button">
                                        {{ trans('masterclass.view.buttons.begin') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" data-id="0" id="finish_preview" role="dialog" style="z-index: 1111;" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <p>
                    {{ trans('masterclass.view.modals.preview_ended.message') }}
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-primary closePreview" type="button">
                    {{ trans('masterclass.view.tooltip.close_preview') }}
                </button>
                <button class="btn btn-primary" id="revisit_masterclass" type="button">
                    {{ trans('masterclass.view.buttons.revisit') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
@section('after-scripts')
<script src="{{ asset('assets/plugins/step/jquery.steps.js?var='.rand()) }}">
</script>
<script type="text/javascript">
    var pre_survey_title = `{{ (!is_null($pre_survey) ? $pre_survey->title : $course->title . 'preview') }}`,
        masterclass_name = `{{ $course->title }} preview`,
        post_survey_title = `{{ (!is_null($post_survey) ? $post_survey->title : $course->title . 'preview') }}`,
        url = {
            cancelPrview: `{{ route('admin.masterclass.manageLessions', $course->id) }}`,
            editLessonUrl: `{{ route('admin.masterclass.editLession', [':id']) }}`,
        },
        buttonsText = {!! json_encode(trans('masterclass.view.buttons')) !!};

    $.fn.steps.setStep = function(step) {
        var currentIndex = $(this).steps('getCurrentIndex');
        for (var i = 0; i < Math.abs(step - currentIndex); i++) {
            if (step > currentIndex) {
                $(this).steps('next');
            } else {
                $(this).steps('previous');
            }
        }
    };

    function stopMedia(selector) {
        $('audio, video, iframe', selector).each(function(index, el) {
            if($(el).is('iframe')) {
                $(el).attr('src', $(el).attr('src'));
            } else {
                $(el).get(0).pause();
                $(el).get(0).currentTime = 0;
            }
        });
    }

    $(window).on("load", function() {
        stopMedia('#surveyPreview');
    });

    $(document).ready(function() {
        stopMedia('#surveyPreview');

        $('#surveyPreview').modal('show');
        $(document).on('click', '#brginClass', function(e) {
            stopMedia(`#masterclass-preview`);
            $('.modal-title-nevigation').removeClass('active');
            $('.modal-title-nevigation.preSurvey').addClass('active');
            $('#masterclass-preview').fadeOut('fast');
            $('#preSurvey').fadeIn('slow');
        });

        @if(!is_null($pre_survey))
        $("#preSurvey").steps({
            headerTag: "h3",
            bodyTag: "section",
            transitionEffect: "fade",
            autoFocus: true,
            enableAllSteps: true,
            enableCancelButton: true,
            labels: {
                next: buttonsText.preSurvey.next,
                previous: buttonsText.preSurvey.previous,
                finish: buttonsText.preSurvey.finish,
                cancel: buttonsText.preSurvey.cancel,
            },
            onStepChanging: function (event, currentIndex, newIndex) {
                $('#edit-lesson').hide();
                return true;
            },
            onFinished: function (event, currentIndex) {
                $('.modal-title-nevigation').removeClass('active');
                $('.modal-title-nevigation.surveyLessons').addClass('active');
                $('#preSurvey').fadeOut('fast');
                $('#surveyLessons').fadeIn('slow');
                $('#edit-lesson').show();
            },
            onCanceled: function (event) {
                window.location.href = "{{ route('admin.masterclass.manageLessions', $course->id) }}";
            },
        });
        @endif

        @if($lessons->isNotEmpty())
        $("#surveyLessons").steps({
            headerTag: "h3",
            bodyTag: "section",
            transitionEffect: "fade",
            autoFocus: true,
            enableAllSteps: true,
            enableCancelButton: true,
            labels: {
                next: buttonsText.surveyLessons.next,
                previous: buttonsText.surveyLessons.previous,
                finish: buttonsText.surveyLessons.finish,
                cancel: buttonsText.surveyLessons.cancel,
            },
            onStepChanging: function (event, currentIndex, newIndex) {
                stopMedia(`[data-step="${currentIndex}"]`);
                return true;
            },
            onFinishing: function (event, currentIndex) {
                stopMedia(`[data-step="${currentIndex}"]`);
                return true;
            },
            onFinished: function (event, currentIndex) {
                $('.modal-title-nevigation').removeClass('active');
                $('.modal-title-nevigation.postSurvey').addClass('active');
                $('#surveyLessons').fadeOut('fast');
                $('#postSurvey').fadeIn('slow');
                $('#edit-lesson').hide();
            },
            onCanceled: function (event) {
                window.location.href = "{{ route('admin.masterclass.manageLessions', $course->id) }}";
            },
        });
        @endif

        @if(!is_null($post_survey))
        $("#postSurvey").steps({
            headerTag: "h3",
            bodyTag: "section",
            transitionEffect: "fade",
            autoFocus: true,
            enableAllSteps: true,
            enableCancelButton: true,
            labels: {
                next: buttonsText.postSurvey.next,
                previous: buttonsText.postSurvey.previous,
                cancel: buttonsText.postSurvey.cancel,
            },
            onStepChanging: function (event, currentIndex, newIndex) {
                $('#edit-lesson').hide();
                return true;
            },
            onCanceled: function (event) {
                window.location.href = url.cancelPrview;
            },
            onFinished: function (event, currentIndex) {
                $('#finish_preview').modal('show');
            },
        });
        @endif

        var lid = window.location.hash;
        if(lid != undefined) {
            lid = lid.substring(1);
            var stepObj = $(`#surveyLessons section[data-lid="${lid}"]`),
                step = $(stepObj).data('step');
            if(stepObj.length > 0 && step != undefined) {
                $('#masterclass-preview').fadeOut('fast');
                $('#surveyLessons').fadeIn('slow');
                $('.modal-title-nevigation').removeClass('active');
                $('.modal-title-nevigation.surveyLessons').addClass('active');
                $("#surveyLessons").steps("setStep", step);
                $('#edit-lesson').show();
            }
            window.location.hash = "";
        }

        $(document).on('click', '.modal-title-nevigation', function(e) {
            var tab = $(this).data('tab');
            $('.user-question-main').hide();

            $('.modal-title-nevigation').removeClass('active');
            $('.modal-title-nevigation.' + tab).addClass('active');

            if(tab == "preSurvey") {
                $('#edit-lesson').hide();
                @if(!is_null($pre_survey))
                $("#preSurvey").steps("setStep", 0);
                @endif
            } else if(tab == "surveyLessons") {
                @if($lessons->isNotEmpty())
                $('#edit-lesson').show();
                $("#surveyLessons").steps("setStep", 0);
                @endif
            } else if(tab == "postSurvey") {
                $('#edit-lesson').hide();
                @if(!is_null($post_survey))
                $("#postSurvey").steps("setStep", 0);
                @endif
            }

            $('#' + tab).fadeIn('slow');
        });
        $(document).on('click', '.closePreview', function(e) {
            e.preventDefault();
            window.location.href = "{{ route('admin.masterclass.index') }}";
        });
        $(document).on('click', '#revisit_masterclass', function(e) {
            @if(!is_null($pre_survey))
            $("#preSurvey").steps("setStep", 0);
            @endif
            @if($lessons->isNotEmpty())
            $("#surveyLessons").steps("setStep", 0);
            @endif
            @if(!is_null($post_survey))
            $("#postSurvey").steps("setStep", 0);
            @endif

            $('.user-question-main').hide();
            $('.modal-title-nevigation').removeClass('active');
            $('.modal-title-nevigation.preSurvey').addClass('active');
            $('#preSurvey').fadeIn('slow');
            $('#finish_preview').modal('hide');
        });

        $(document).on('hidden.bs.modal', '#surveyPreview', function(e) {
            window.location.href = "{{ route('admin.masterclass.index') }}";        
        });

        @if($lessons->isNotEmpty())
        $(document).on('click', '#edit-lesson', function(e) {
            var lid = ($('#surveyLessons section.current').data('lid') || 0);
            window.location.href = url.editLessonUrl.replace(':id', lid) + '?referrer=preview';
        });
        @endif
    });
</script>
@endsection
