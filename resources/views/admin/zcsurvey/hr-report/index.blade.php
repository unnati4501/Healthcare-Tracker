@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/daterangepicker/daterangepicker.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/datepicker/v1.9.0/css/bootstrap-datepicker3.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.zcsurvey.hr-report.breadcrumb', [
    'mainTitle' => trans('survey.hr_report.title.index'),
    'breadcrumb' => Breadcrumbs::render('survey.hr_report.index'),
    'freetxtBtn' => true
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
                {{ Form::open(['route' => 'admin.hrReport.index', 'class' => 'form-horizontal', 'method' => 'get', 'role' => 'form', 'id' => 'hrReportSearch']) }}
                <div class="search-outer d-md-flex justify-content-between">
                    <div>
                        <div class="form-group {{ $companyColVisibility }}">
                            {{ Form::select('company', $company, request()->get('company'), ['class' => 'form-control select2', 'id' => 'company', 'data-allow-clear' => 'true', 'placeholder' => trans('survey.hr_report.filter.company'), 'data-placeholder' => 'Select company'] ) }}
                        </div>
                        <div class="form-group daterange-outer" >
                            <div class="input-daterange justify-content-between mb-0" id="monthranges">
                                <div class="datepicker-wrap me-0 mb-0">
                                    {{ Form::text('monthrange', null, ['id' => 'globalFromMonth', 'class' => 'form-control bg-white', 'placeholder' => trans('survey.hr_report.filter.from'), 'readonly' => true]) }}
                                    <i class="far fa-calendar">
                                    </i>
                                </div>
                                <span class="input-group-addon text-center">
                                    -
                                </span>
                                <div class="datepicker-wrap me-0 mb-0">
                                    {{ Form::text('monthrange', null, ['id' => 'globalToMonth', 'class' => 'form-control bg-white', 'placeholder' => trans('survey.hr_report.filter.to'), 'readonly' => true]) }}
                                    <i class="far fa-calendar">
                                    </i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="search-actions align-self-start">
                        <button class="me-md-4 filter-apply-btn" id="hrReportSearchSubmitFrm" type="submit">
                            {{ trans('buttons.general.apply') }}
                        </button>
                        <a class="filter-cancel-icon" href="{{ route('admin.hrReport.index') }}">
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
        <!-- .table -->
        <div class="card">
            <div class="card-body">
                <div class="card-table-outer">
                    <div class="table-responsive">
                        <table class="table custom-table" id="hrReportManagement">
                            <thead>
                                <tr>
                                    <th width="{{ $column_width }}%">
                                        {{ trans('labels.hr_report.department_name') }}
                                    </th>
                                    @foreach($categories as $key => $category)
                                    <th class="score-cell" data-cat-id="{{ $category['id'] }}" width="{{ $column_width }}%">
                                        {{ $category['display_name'] }}
                                    </th>
                                    @endforeach
                                    <th width="{{ $column_width }}%">
                                        Department score
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                            @if(!empty($categories))
                            <tfoot>
                                <tr>
                                    <td>
                                    </td>
                                    @foreach($categories as $key => $category)
                                    <td>
                                        <div class="round-score" style="background-color: {{ getScoreColor($category['category_percent']) }};">
                                            {{ $category['category_percent'] }}%
                                        </div>
                                    </td>
                                    @endforeach
                                    <td class="text-center">
                                    </td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.table -->
        <!-- .graph -->
        <div class="card" id="report-details-block" style="display: none;">
            <div class="card-body">
                <div class="card-inner">
                    <h3 class="card-inner-title" id="card-title">
                    </h3>
                    <div class="text-center" id="details_loader" style="display: none;">
                        <i class="fas fa-spinner fa-lg fa-spin">
                        </i>
                        <span class="ms-1">
                            {{ trans('survey.hr_report.messages.loading') }}
                        </span>
                    </div>
                    <div class="text-center" id="details_no_data" style="display: none;">
                        <span class="ms-1">
                            {{ trans('survey.hr_report.messages.empty_graph') }}
                        </span>
                    </div>
                    <div id="chart-area" style="display: none;">
                        <div class="d-sm-flex justify-content-end">
                            <div class="form-group daterange-outer input-daterange w-auto" id="detailsmonthranges">
                                <div class="input-group input-daterange dateranges mb-0">
                                    {{ Form::text('monthrange', null, ['id' => 'detailsFromMonth', 'class' => 'form-control', 'placeholder' => trans('survey.hr_report.filter.from'), 'readonly' => true]) }}
                                    <span class="input-group-addon text-center">
                                        -
                                    </span>
                                    {{ Form::text('monthrange', null, ['id' => 'detailsToMonth', 'class' => 'form-control', 'placeholder' => trans('survey.hr_report.filter.to'), 'readonly' => true]) }}
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="exercise-hours-chart-area">
                                    <canvas height="200" id="departmentScoreLineGraph" width="600">
                                    </canvas>
                                </div>
                            </div>
                        </div>
                        <hr/>
                        <div class="row" id="subCategoryWiseDepartmentGraph">
                        </div>
                        <div class="text-center" id="no_data_subcategory" style="display: none;">
                            <span class="ms-1">
                                {{ trans('survey.hr_report.messages.no_subcategory') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer border-top text-end">
                <div class="card-tools">
                    <button class="btn btn-outline-primary back-to-report" type="button">
                        {{ trans('survey.hr_report.buttons.back_to_report') }}
                    </button>
                </div>
            </div>
        </div>
        <!-- /.graph -->
    </div>
</section>
<script id="subCategoryTabTemplate" type="text/html">
    <div class="col-lg-4 col-md-6 col-xl-3">
        <div class="speed-chart-area mb-3">
            <div class="d-flex justify-content-center align-items-center">
                <sapn class="badge badge-pill chart-badge" style="background-color: #background-color#;">
                    #sub_category_name#
                </sapn>
            </div>
            <div class="chart-wrap survey-chart-gap">
                <canvas data-subcategorywisedepartmentscoregaugechart-#id#="" class="gaugeChartSubScore" height="100" width="200">
                </canvas>
                <span class="chart-value chart-value-lg" style="color: #background-color#;">
                    #sub_category_percentage#%
                </span>
            </div>
        </div>
    </div>
</script>
@endsection
@section('after-scripts')
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}" type="text/javascript">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}" type="text/javascript">
</script>
<script src="{{ asset('assets/plugins/moment/moment.min.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{ asset('assets/plugins/daterangepicker/daterangepicker.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{ asset('assets/plugins/datepicker/bootstrap-datepicker.min.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{ asset('assets/plugins/chart.js/Chart.bundle.min.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{ asset('assets/plugins/gauge/gauge.min.js?var='.rand()) }}" type="text/javascript">
</script>
<script type="text/javascript">
    var charts = {
        gaugeChartOptions: {
            animationSpeed: 32,
            angle: 0.0,
            lineWidth: 0.25,
            pointer: {
                length: 0.6,
                strokeWidth: 0.025,
                color: '#000000'
            },
            strokeColor: '#f2f4f4',
            highDpiSupport: true
        },
        departmentScoreLineGraph: {
            selector: $('#departmentScoreLineGraph')[0],
            object: undefined,
            options: {
                type: "line",
                gridLines: {
                    display: true,
                    drawBorder: true,
                    drawOnChartArea: false,
                },
                data: {
                    labels: [],
                    datasets: [{
                        label: "",
                        data: [],
                        fill: false,
                        lineTension: 0,
                        borderWidth: 2,
                        borderColor: "rgb(0, 165, 209)",
                        pointBackgroundColor: "rgb(0, 165, 209)"
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem, data) {
                                return (`${tooltipItem.yLabel}%`);
                            }
                        }
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                max: 100.00,
                            }
                        }]
                    },
                    legend: {
                        display: false
                    }
                }
            }
        },
        subCategoryWiseDepartmentGaugeCharts: []
    },
    companyScoreColorCode = {
        red: "{{ config('zevolifesettings.zc_survey_score_color_code.red') }}",
        yellow: "{{ config('zevolifesettings.zc_survey_score_color_code.yellow') }}",
        green: "{{ config('zevolifesettings.zc_survey_score_color_code.green') }}"
    },
    urls = {
        detailsUrl: "{{ route('admin.hrReport.getHrReporDetails', [':companyId', ':departmentId', ':categoryId']) }}",
        datatable: `{{ route('admin.hrReport.gethrReportsData') }}`,
    },
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    },
    categories = [],
    categories = '{!! json_encode($categories) !!}',
    columns = [{
        data: 'department_name',
        name: 'department_name',
        render: function (data, type, row) {
            if(row.role == "zevo") {
                return `<p>${row.department_name}<br/>(<span>${row.company_name}</span>)</p>`;
            } else {
                return `<p>${row.department_name}</p>`;
            }
        }
    }],
    requestParams = {!! json_encode($requestParams) !!},
    dtPayload = {
        categories: categories,
        company: $('#company').val()
    };
</script>
<script src="{{ mix('js/hr-report/index.js') }}" type="text/javascript">
</script>
@endsection
