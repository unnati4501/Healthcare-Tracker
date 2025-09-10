@extends('layouts.app')

@section('after-styles')
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.course.lession.breadcrumb', [
    'mainTitle' => trans('masterclass.survey.title.edit', ['type' => ucfirst($record->type)]),
    'breadcrumb' => Breadcrumbs::render('course.survey.edit', $record->course_id),
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content no-default-select2">
    <div class="container-fluid">
        <div class="card form-card">
            {{ Form::open(['route' => ['admin.masterclass.updateSurvey', $record->id], 'class' => 'form-horizontal zevo_form_submit', 'method' => 'PATCH', 'role' => 'form', 'id' => 'surveyEdit', 'name' => 'surveyEdit', 'files' => true]) }}
            <div class="card-body">
                <div class="qustion-main-area">
                    @include('admin.course.survey.form')
                </div>
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{{ route('admin.masterclass.manageLessions', $record->course_id) }}">
                        {{ trans('buttons.general.cancel') }}
                    </a>
                    <button class="btn btn-primary" id="zevo_submit_btn" onclick="formSubmit(event);" type="submit">
                        {{ trans('buttons.general.update') }}
                    </button>
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</section>
<div class="modal fade" data-backdrop="static" data-id="0" id="remove-survey-question-model-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ trans('masterclass.survey.modal.delete_question.title') }}
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                </button>
            </div>
            <div class="modal-body">
                <p>
                    {{ trans('masterclass.survey.modal.delete_question.message') }}
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">
                    {{ trans('buttons.general.cancel') }}
                </button>
                <button class="btn btn-primary" id="remove-survey-question-model-box-confirm" type="button">
                    {{ trans('buttons.general.delete') }}
                </button>
            </div>
        </div>
    </div>
</div>
<script id="survey_questions_temp" type="text/html">
    @include('admin.course.survey.question_block', ["id" => ":id", "oid" => 1, "edit" => false, "question" => [], "options" => []])
</script>
<script id="survey_option_temp" type="text/html">
    @include('admin.course.survey.option_block', ["id" => ":id", "oid" => ":oid", "edit" => false, 'option' => []])
</script>
@endsection

@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\EditSurveyQuestionRequest','#surveyEdit') !!}
<script type="text/javascript">
    var queCount = {{ ($questions_count + 1) ?? 0 }},
        optionCount = 1,
        _deleted_questions = [],
        _deleted_options = [],
        messages = {!! json_encode(trans('masterclass.survey.messages')) !!};
        messages.choosefile = `{{ trans('masterclass.survey.form.placeholder.choose_file') }}`,
        messages.upload_image_dimension = '{{ trans('masterclass.survey.messages.upload_image_dimension') }}';
    function formSubmit(event) {
        if($('#surveyEdit').valid()) {
            $('.single-question-block').removeClass('error-block');

            var _return = true;
            $('.question-type').each(function(index, question_type) {
                var _parent = $(question_type).parents('.single-question-block'),
                    _id = _parent.data('id');
                if($(`.question-option-${_id} tbody tr`).length < 2) {
                    _parent.addClass('error-block');
                    $('.toast').remove();
                    toastr.error(messages.min_options)
                    $('html, body').animate({
                        scrollTop: _parent.offset().top - 100
                    }, 500);
                    _return = false;
                    return false;
                }
            });

            if(!_return) {
                event.preventDefault();
            }
        }
    }

    function setDeleteVisibility(element) {
        if (!$(element).parents('tr').is(':first-child') && $(element).parents('tr').is(':last-child')) {
            if ($(element).parents("tbody").find('tr').length > 1) {
                $(element).parent().parent().next().toggleClass("show_del", $(element).val().length == 0);
            }
        }
    }
</script>
<script src="{{ mix('js/masterclass/survey.js') }}" type="text/javascript">
</script>
@endsection
