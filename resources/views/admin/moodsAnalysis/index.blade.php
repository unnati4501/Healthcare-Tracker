@extends('layouts.app')

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.moodsAnalysis.header', [
  'mainTitle' => trans('moods.analysis.title.dashboard'),
  'breadcrumb' => 'moodAnalysis.index',
  'companies' => $companies,
  'departments' => $departments
])
<!-- /.content-header -->
@endsection

@section('content')
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="mb-4 mt-4">
            <ul class="week-month-year-list justify-content-end" id="duration">
                <li class="active" data-duration="7">
                    {{ trans('moods.analysis.filter.week') }}
                </li>
                <li data-duration="30">
                    {{ trans('moods.analysis.filter.month') }}
                </li>
                <li data-duration="365">
                    {{ trans('moods.analysis.filter.year') }}
                </li>
            </ul>
        </div>
        <div class="row">
            <div class="col-xl-4">
                <div class="card dashboard-card">
                    <!-- /.card-header -->
                    <div class="card-body">
                        <div class="card-header border-0">
                            <h3 class="card-inner-title border-0 pb-0 mb-4">
                                {{ trans('moods.analysis.title.users') }}
                            </h3>
                        </div>
                        <div class="chart-height donut-chart-height">
                            <div class="canvas-wrap doughnut-number-of-users" id="appendNumberOfUsersChart">
                                <canvas class="canvas" id="numberOfUsers">
                                </canvas>
                            </div>
                        </div>
                        <div class="chart-legend mt-4">
                            <ul class="chart-legend">
                                <li>
                                    <div class="legend-color" style="border-color:#E21067">
                                    </div>
                                    <span class="legend-text me-2 w-25">
                                        {{ trans('moods.analysis.labels.total') }}
                                    </span>
                                    <span id="totalUsers">
                                    </span>
                                </li>
                                <li>
                                    <div class="legend-color" style="border-color:#FFD600">
                                    </div>
                                    <span class="legend-text me-2 w-25">
                                        {{ trans('moods.analysis.labels.active') }}
                                    </span>
                                    <span id="activeUsers">
                                    </span>
                                </li>
                                <li>
                                    <div class="legend-color" style="border-color:#50C9B5">
                                    </div>
                                    <span class="legend-text me-2 w-25">
                                        {{ trans('moods.analysis.labels.passive') }}
                                    </span>
                                    <span id="passiveUsers">
                                    </span>
                                </li>
                            </ul>
                        </div>
                        <!-- /.card-body -->
                    </div>
                </div>
            </div>
            <div class="col-xl-8">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between h-100 flex-column">
                            <div class="card-header border-0">
                                <h3 class="card-inner-title border-0 pb-0 mb-4">
                                    {{ trans('moods.analysis.title.moods') }}
                                </h3>
                            </div>
                            <!-- /.card-header -->
                            <div class="chart-height">
                                <div class="canvas-wrap" id="appendMoodsChart">
                                    <canvas class="canvas" id="chartMoodsAnalysis">
                                    </canvas>
                                </div>
                            </div>
                            <!-- /.card-body -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card dashboard-card mt-4 mt-xl-5">
            <div class="card-body">
                <div class="card-header border-0">
                    <h3 class="card-inner-title border-0 pb-0 mb-4">
                        {{ trans('moods.analysis.title.tags') }}
                    </h3>
                </div>
                <!-- /.card-header -->
                <div class="chart-height">
                    <div class="canvas-wrap" id="appendTagsChart">
                        <canvas class="canvas" id="chartTagAnalysis">
                        </canvas>
                    </div>
                </div>
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.container-fluid -->
    </div>
</section>
<!-- /.content -->
@endsection

@section('after-scripts')
<script src="{{ asset('assets/plugins/chart.js/Chart.bundle.min.js?var='.rand()) }}">
</script>
<script type="text/javascript">
    var url = {
        deptData: `{{ route("admin.ajax.companyDepartment", ":id") }}`,
        usersData: `{{ route("admin.moodAnalysis.usersData") }}`,
        moodsData: `{{ route("admin.moodAnalysis.moodsData") }}`,
        tagsData: `{{ route("admin.moodAnalysis.tagsData") }}`,
    };
</script>
<!-- Moods Analysis JS -->
<script src="{{ mix('js/moodsAnalysis.js') }}">
</script>
@endsection
