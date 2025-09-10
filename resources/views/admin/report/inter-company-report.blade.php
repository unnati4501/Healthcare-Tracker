@extends('layouts.app')

@section('after-styles')
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/datepicker/v1.9.0/css/bootstrap-datepicker3.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.report.breadcrumb',[
    'mainTitle'  => trans('intercompany.title.inter-company-report'),
    'breadcrumb' => 'report.intercompany',
    'back'       => false,
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content no-default-select2">
<div class="container-fluid">
    <!-- Nav tabs -->
    <div class="nav-tabs-wrap">
        <section class="col-lg-12">
            <ul class="nav nav-tabs tabs-line-style" id="myTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" type="button" data-for="company" data-bs-toggle="tab" href="#" id="tab-company" role="tab">
                        {{ trans('intercompany.title.company') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-for="team" type="button" data-bs-toggle="tab" href="#" id="tab-team" role="tab">
                        {{ trans('intercompany.title.team') }}
                    </a>
                </li>
            </ul>
            <!--/.direct-chat -->
        </section>
    </div>
    <div class="card search-card">
        <div class="card-body pb-0">
            <h4 class="d-md-none">{{ trans('intercompany.title.filter') }}</h4>
            {{ Form::open(['route' => 'admin.reports.intercompanyreport', 'class' => 'form-horizontal', 'method'=>'get','role' => 'form', 'id'=>'interCompanyReportSearch']) }}
            <div class="search-outer d-md-flex justify-content-between">
                <div>
                    <div class="form-group">
                        {{ Form::select('challenge', $challenges, request()->get('challenge'), ['class' => 'form-control select2', 'id'=>'challenge', 'style'=>'width: 100%;', 'autocomplete' => 'off', 'placeholder' => trans('intercompany.filter.select_challenge'), 'data-placeholder' =>  trans('intercompany.filter.select_challenge'), 'data-allow-clear' => 'true']) }}
                    </div>
                    <div class="form-group">
                        {{ Form::select('company', [], request()->get('company'), ['class' => 'form-control select2', 'id'=>'company', 'style'=>'width: 100%;', 'autocomplete' => 'off', 'placeholder' =>  trans('intercompany.filter.select_company'), 'data-placeholder' => trans('intercompany.filter.select_company'), 'data-allow-clear' => 'true']) }}
                    </div>
                </div>
                <div class="search-actions align-self-start">
                    <button class="me-md-4 filter-apply-btn" type="submit">
                        {{trans('buttons.general.apply')}}
                    </button>
                    <a class="filter-cancel-icon" href="{{ route('admin.reports.intercompanyreport') }}">
                        <i class="far fa-times">
                        </i>
                        <span class="d-md-none ms-2 ms-md-0">{{trans('buttons.general.reset')}}</span>
                    </a>
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="card-table-outer" id="ICReportCompany-wrap">
                <div class="dt-buttons ICReportCompany-export">
                    <button class="btn btn-primary" data-end="" data-start="" data-tab="ic-company" id="exportInterCompanyReport" data-title="Export Intercompany Report" type="button">
                        <span>
                            <i class="far fa-envelope me-3 align-middle">
                            </i>
                            {{trans('buttons.general.export')}}
                        </span>
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table custom-table" id="ICReportCompany">
                        <thead>
                            <tr>
                                <th>
                                    {{ trans('intercompany.table.rank') }}
                                </th>
                                <th>
                                    {{ trans('intercompany.table.company_name') }}
                                </th>
                                <th>
                                    {{ trans('intercompany.table.total_teams') }}
                                </th>
                                <th>
                                    {{ trans('intercompany.table.total_users') }}
                                </th>
                                <th>
                                    {{ trans('intercompany.table.points') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-center no-data-table" colspan="5">{{ trans('intercompany.message.please_select_challenge') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-table-outer" id="ICReportTeam-wrap">
                <div class="dt-buttons ICReportTeam-export hidden">
                    <button class="btn btn-primary" data-end="" data-start="" data-tab="ic-team" id="exportInterCompanyReport" data-title="Export Intercompany Team Report" type="button">
                        <span>
                            <i class="far fa-envelope me-3 align-middle">
                            </i>
                            {{trans('buttons.general.export')}}
                        </span>
                    </button>
                </div>
                <div class="table-responsive hidden">
                    <table class="table custom-table" id="ICReportTeam">
                        <thead>
                            <tr>
                                <th>
                                    {{ trans('intercompany.table.rank') }}
                                </th>
                                <th>
                                    {{ trans('intercompany.table.team_name') }}
                                </th>
                                <th>
                                    {{ trans('intercompany.table.company_name') }}
                                </th>
                                <th>
                                    {{ trans('intercompany.table.points') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-center no-data-table" colspan="5">{{ trans('intercompany.message.please_select_challenge') }}</td>
                            </tr>
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
<style type="text/css">
    .hidden { display: none; }
</style>
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
<script src="{{ asset('assets/plugins/datepicker/bootstrap-datepicker.min.js?var='.rand()) }}">
</script>
<script type="text/javascript">
    var timezone = '{{ $timezone }}',
        loginemail = '{{ $loginemail }}';
    var url = {
        getICReportChallengeData: `{{ route('admin.reports.getICReportChallengeData') }}`,
        getICReportChallengeComapnies: `{{ route("admin.reports.getICReportChallengeComapnies") }}`,
        interCompanyReportExportUrl:`{{route('admin.reports.exportIntercompanyReport')}}`,
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
        failed_load_companies: `{{ trans('intercompany.message.failed_load_companies') }}`,
        please_select_challenge: `{{ trans('intercompany.message.please_select_challenge') }}`,
    };
</script>
<script src="{{ mix('js/intercompany/index.js') }}" type="text/javascript">
</script>
@endsection
