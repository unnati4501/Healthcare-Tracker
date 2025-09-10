@extends('layouts.app')

@section('before-styles')
<link href="{{ asset('assets/plugins/step/jquery.steps.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/datatables/extensions/ColReorder/css/rowReorder.dataTables.min.css?var='.rand()) }}" rel="stylesheet"/>
{{--
<link href="https://fonts.googleapis.com/css?family=Raleway:400,500,600,700&display=swap?var=<?= rand() ?>" rel="stylesheet"/>
--}}
@endsection

@section('after-styles')
<style type="text/css">
    .custom-select.is-invalid, .form-control.is-invalid, .was-validated .custom-select:invalid, .was-validated .form-control:invalid {
        border-color: #dc3545 !important;
    }
    #allquestions tr.disabled {
        cursor: not-allowed;
        pointer-events: none;
        background-color: rgba(52,58,64,.075);
    }
</style>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.zcsurvey.breadcrumb', [
    'mainTitle' => trans('survey.title.edit'),
    'breadcrumb' => Breadcrumbs::render('survey.edit'),
    'back' => false
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content no-default-select2">
    <div class="container-fluid">
        <div class="card form-card height-auto">
            <div class="wizard">
                <div class="wizard-inner">
                    {{ Form::open(['route' => ['admin.zcsurvey.update', $data->getKey()], 'class' => 'form-horizontal', 'method' => 'post', 'role' => 'form', 'id' => 'surveyEdit', 'files' => false]) }}
                    @include('admin.zcsurvey.form', ['edit' => true])
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</section>
<div class="modal fade" data-id="0" id="err-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Survey questions
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body">
                <p id="err-box-message">
                    Please select questions from the list and add to survey.
                </p>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" data-id="0" id="remove-question-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Remove Question?
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    Are you sure to remove question from the survey?
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">
                    {{ trans('buttons.general.cancel') }}
                </button>
                <button class="btn btn-primary" id="remove-question-confirm" type="button">
                    {{ trans('buttons.general.remove') }}
                </button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" data-backdrop="static" data-id="0" id="leave-survey-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Leave survey
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    Are you sure you want to leave survey?
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">
                    {{ trans('buttons.general.cancel') }}
                </button>
                <button class="btn btn-primary" id="leave-survey-confirm" type="button">
                    {{ trans('buttons.general.leave') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('after-scripts')
<script src="{{ asset('assets/plugins/step/jquery.steps.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datatables/extensions/ColReorder/js/dataTables.rowReorder.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/moment/moment.min.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/moment/moment-timezone-with-data-10-year-range.js?var='.rand()) }}">
</script>
{!! $validator = JsValidator::formRequest('App\Http\Requests\Admin\EditZCSurveyRequest', '#surveyEdit') !!}
<script id="question_data_block_template" type="text/html">
    @include('admin.zcsurvey.questions_hidden_field_block', ["id" => ":id", "valquestion_id" => "val:question_id", "valcategory" => "val:category", "valsubcategory" => "val:subcategory", "valquestions_type" => "val:questions_type"])
</script>
<script type="text/javascript">
    var url = {
            _cancelURL: "{{ route('admin.zcsurvey.index') }}",
            _getSubCategoriesUrl: "{{ route('admin.zcsurvey.getSurveySubCategories', ':id') }}",
            _getQuestions: "{{ route('admin.zcsurvey.getQuestions', ':id') }}",
            _getSingleQuestion: "{{ route('admin.zcquestionbank.show',':id') }}"
        },
        buttons = {
            next: "{{ trans('labels.buttons.next') }}",
            previous: "{{ trans('labels.buttons.previous') }}",
            finish: "{{ trans('labels.buttons.submit') }}",
            cancel: "{{ trans('labels.buttons.cancel') }}",
        },
        xhr = {
            _getSubCategoriesXHR: undefined,
            _getQuestionsXHR: undefined,
        },
        datatables = {
            allquestions: undefined,
            surveyquestions: undefined,
        },
        zsconfig = {
            maxQuestions: {{ config('zevolifesettings.zc_survey.max_survey_question', 0) }},
        },
        pagination = {
            previous: `{!! trans('buttons.pagination.previous') !!}`,
            next: `{!! trans('buttons.pagination.next') !!}`,
        },
        $surveyAddForm = $("#surveyEdit"),
        stepObj,
        timezone = `{{ $timezone }}`,
        date_format = `{{ $date_format }}`,
        pagination = {{ $pagination }},
        orderQ = 0,
        surveyId = {{ ($data->id ?? 0) }},
        validateTitle = function() {
            $('#title').valid();
        }
</script>
<script src="{{ mix('js/zcsurvey/zcSurveyEdit.js') }}">
</script>
@endsection
