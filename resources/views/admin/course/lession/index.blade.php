@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/datatables/extensions/ColReorder/css/rowReorder.dataTables.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.course.lession.breadcrumb', [
    'mainTitle' => trans('masterclass.lesson.title.index', ['masterclass' => $course->title]),
    'breadcrumb' => Breadcrumbs::render('course.lesson.index'),
    'create' => true,
    'backToMC' => true,
    'allow_add_survey_button' => false,
    'allow_remove_survey_button' => false,
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <!-- search-block -->
        <div class="card search-card">
            <div class="card-body pb-0">
                <h4 class="d-md-none">
                    {{ trans('buttons.general.filter') }}
                </h4>
                {{ Form::open(['route' => ['admin.masterclass.manageLessions', $course->id], 'class' => 'form-horizontal', 'method' => 'GET', 'role' => 'form', 'id' => 'courseLessionSearch']) }}
                <div class="search-outer d-md-flex justify-content-between">
                    <div>
                        <div class="form-group">
                            {{ Form::text('recordName', request()->get('recordName'), ['class' => 'form-control', 'placeholder' => trans('masterclass.lesson.filter.title'), 'id' => 'recordName', 'autocomplete' => 'off']) }}
                        </div>
                    </div>
                    <div class="search-actions align-self-start">
                        <button class="me-md-4 filter-apply-btn" type="submit">
                            {{ trans('buttons.general.apply') }}
                        </button>
                        <a class="filter-cancel-icon" href="{{ route('admin.masterclass.manageLessions', $course->id) }}">
                            <i class="far fa-times">
                            </i>
                            <span class="d-md-none ms-2 ms-md-0">
                                {{ trans('buttons.general.reset') }}
                            </span>
                        </a>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
        <a class="btn btn-primary filter-btn" href="javascript:void(0);">
            <i class="far fa-filter me-2 align-middle">
            </i>
            <span class="align-middle">
                {{ trans('buttons.general.filter') }}
            </span>
        </a>
        <!-- /.search-block -->
        <!-- listing -->
        <div class="card">
            <div class="card-body">
                <div class="card-table-outer course-lesson-table-outer">
                    <div class="table-responsive">
                        <table class="table custom-table" id="courseLessonManagment">
                            <thead>
                                <tr>
                                    <th>
                                        {{ trans('masterclass.lesson.table.order') }}
                                    </th>
                                    <th class="d-none">
                                        {{ trans('masterclass.lesson.table.id') }}
                                    </th>
                                    <th>
                                        {{ trans('masterclass.lesson.table.title') }}
                                    </th>
                                    <th class="no-sort">
                                        {{ trans('masterclass.lesson.table.duration') }}
                                    </th>
                                    <th class="no-sort">
                                        {{ trans('masterclass.lesson.table.status') }}
                                    </th>
                                    <th class="th-btn-3 no-sort">
                                        {{ trans('masterclass.lesson.table.action') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.listing -->
    </div>
</section>
<!-- survey-header -->
@include('admin.course.lession.breadcrumb', [
    'mainTitle' => trans('masterclass.survey.title.index', ['masterclass' => $course->title]),
    'allow_add_survey_button' => $allow_add_survey_button,
    'allow_remove_survey_button' => $allow_remove_survey_button,
])
<!-- /.survey-header -->
<section class="content">
    <div class="container-fluid">
        <!-- listing -->
        <div class="card">
            <div class="card-body">
                <div class="card-table-outer course-survey-table-outer">
                    <div class="table-responsive">
                        <table class="table custom-table" id="courseSurveyManagment">
                            <thead>
                                <tr>
                                    <th class="d-none">
                                        {{ trans('masterclass.survey.table.updated_at') }}
                                    </th>
                                    <th class="no-sort">
                                        {{ trans('masterclass.survey.table.survey_type') }}
                                    </th>
                                    <th class="no-sort">
                                        {{ trans('masterclass.survey.table.title') }}
                                    </th>
                                    <th class="th-btn-4 no-sort">
                                        {{ trans('masterclass.survey.table.status') }}
                                    </th>
                                    <th class="th-btn-2 no-sort">
                                        {{ trans('masterclass.survey.table.action') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.listing -->
    </div>
</section>
<!-- /.modals -->
@include('admin.course.lession.modals')
<!-- /.modals -->
@endsection

@section('after-scripts')
<script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/datatables/extensions/ColReorder/js/dataTables.rowReorder.min.js?var='.rand())}}">
</script>
<script type="text/javascript">
    var pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    },
    messages = {!! json_encode(trans('masterclass.lesson.messages')) !!},
    url = {
        datatable: `{{ route('admin.masterclass.getLessions', $course->id) }}`,
        reorder: `{{ route('admin.masterclass.reorderingLesson', $course->id) }}`,
        dtSurvey: `{{ route('admin.masterclass.getServeys', [$course->id]) }}`,
        deleteSurvey: `{{ route('admin.masterclass.deleteSurveys', $course->id) }}`,
        deleteLesson: `{{ route('admin.masterclass.deleteLession', ':id') }}`,
        publishLesson: `{{ route('admin.masterclass.publishLesson', ':id') }}`,
    },
    enableOrdering = {!! (($course->status == true) ? 'true' : 'false') !!},
    enableRowReorder = {!! (($course->status == true) ? 'false' : 'true') !!};
</script>
<script type="text/javascript" src="{{ mix('js/masterclass/lesson-index.js') }}"></script>
@endsection
