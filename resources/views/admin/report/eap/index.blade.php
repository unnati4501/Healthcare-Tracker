@extends('layouts.app')

@section('after-styles')
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/datepicker/v1.9.0/css/bootstrap-datepicker3.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.report.breadcrumb',[
    'mainTitle'  => trans('eapfeedback.title.index_title'),
    'breadcrumb' => 'eapfeedback.index',
    'back'       => false,
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card search-card">
            <div class="card-body pb-0">
                <h4 class="d-md-none">
                    {{ trans('eapfeedback.title.filter') }}
                </h4>
                {{ Form::open(['route' => 'admin.reports.eap-feedback', 'class' => 'form-horizontal', 'method' => 'get', 'role' => 'form', 'id' => 'feedbackSearch']) }}
                <div class="search-outer d-md-flex justify-content-between">
                    <div>
                        <div class="form-group">
                            {{ Form::select('company', $companies, request()->get('company'), ['class' => 'form-control select2', 'id' => 'company', 'placeholder' => trans('eapfeedback.filter.select_company'), 'data-placeholder' => trans('eapfeedback.filter.select_company')]) }}
                        </div>
                        <div class="form-group">
                            {{ Form::text('counsellor', request()->get('counsellor'), ['class' => 'form-control', 'id' => 'counsellor','placeholder' => trans('eapfeedback.filter.search_by_counsellor_name'), 'id' => 'counsellorTextSearch', 'autocomplete' => 'off']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::select('feedback', $feedback, request()->get('feedback'), ['class' => 'form-control select2', 'id' => 'feedback', 'data-allow-clear' => 'false']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::select('timeDuration', $timeDuration, request()->get('timeDuration'), ['class' => 'form-control select2', 'id' => 'timeDuration', 'data-allow-clear' => 'false']) }}
                        </div>
                    </div>
                    <div class="search-actions align-self-start">
                        <button class="me-md-4 filter-apply-btn" type="submit">
                            {{trans('buttons.general.apply')}}
                        </button>
                        <a class="filter-cancel-icon" href="{{ route('admin.reports.eap-feedback') }}">
                            <i class="far fa-times">
                            </i>
                            <span class="d-md-none ms-2 ms-md-0">
                                {{trans('buttons.general.reset')}}
                            </span>
                        </a>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="text-center" id="graph-loader">
                    <i class="fa fa-spinner fa-spin">
                    </i>
                    {{ trans('eapfeedback.message.graph_loading') }}
                </div>
                <div id="graph-area">
                </div>
                <div class="card-table-outer" id="eapFeedbackManagment-wrap">
                    {{-- <div class="text-end">
                        <a class="action-icon btn btn-primary" href="javaScript:void(0)" data-end="" data-start="" id="counsellorFeedbackExport" data-title="Export Counselloer Feedback" title="{{ trans('challenges.buttons.tooltips.export') }}">
                            <i aria-hidden="true" class="far fa-download">Export Excel
                            </i>
                        </a>
                    </div> --}}
                    <div class="dt-buttons">
                        <button class="btn btn-primary" data-end="" data-start="" id="counsellorFeedbackExport" data-title="Export Counselloer Feedback" type="button">
                            <span>
                                <i class="far fa-envelope me-3 align-middle">
                                </i>
                                {{trans('buttons.general.export')}}
                            </span>
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table custom-table" id="eapFeedbackManagment">
                            <thead>
                                <tr>
                                    <th>
                                        {{trans('eapfeedback.table.counsellor_name')}}
                                    </th>
                                    <th>
                                        {{trans('eapfeedback.table.counsellor_email')}}
                                    </th>
                                    <th>
                                        {{trans('eapfeedback.table.company_name')}}
                                    </th>
                                    <th>
                                        {{trans('eapfeedback.table.duration')}}
                                    </th>
                                    <th class="text-center ignore-export" style="width: 8%;">
                                        {{trans('eapfeedback.table.emoji')}}
                                    </th>
                                    <th>
                                        {{trans('eapfeedback.table.feedback_type')}}
                                    </th>
                                    <th>
                                        {{trans('eapfeedback.table.feedback_text')}}
                                    </th>
                                    <th>
                                        {{trans('eapfeedback.table.date_time')}}
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
    </div>
</section>
@include('admin.report.export_modal')
@endsection

@section('after-scripts')
<script src="{{asset('assets/plugins/moment/moment.min.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/moment/moment-timezone-with-data-10-year-range.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datatables/jszip.min.js?var='.rand())}}">
</script>
{{-- <script src="{{asset('assets/plugins/datepicker/bootstrap-datepicker.js?var='.rand())}}">
</script> --}}
<script src="{{ asset('assets/plugins/datepicker/bootstrap-datepicker.min.js?var='.rand()) }}">
</script>
<!-- App Graph Script Code -->
@include('admin.report.eap.graph')
<script type="text/javascript">

    var timezone = `{{ $timezone }}`,
        date_format = `{{ $date_format }}`,
        loginemail = '{{ $loginemail }}',
        startDate = '{{ config('zevolifesettings.challenge_set_date.before') }}',
        endDate = '{{ config('zevolifesettings.challenge_set_date.after') }}';
    var url = {
        datatable: `{{ route('admin.reports.eap-feedback') }}`,
        counsellorFeedbackExportUrl:`{{route('admin.reports.exportCounsellorFeedbackReport')}}`,
    },
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
        entry_per_page: `{!! trans('buttons.pagination.entry_per_page') !!}`,
    },
    button = {
        export: '<i class="far fa-file-excel me-3 align-middle"></i> {{ trans('customersatisfaction.buttons.export_to_excel') }}',
    },
    message = {
        failed_load_graph: `{{trans('eapfeedback.message.failed_load_graph')}}`,
        failed_load_category: `{{trans('eapfeedback.message.failed_load_category')}}`,
    };
</script>
<script src="{{ mix('js/customersatisfaction/eapfeedback.js') }}" type="text/javascript">
</script>
@endsection
