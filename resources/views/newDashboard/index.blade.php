@extends('layouts.app')

@section('after-styles')
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
<link href="{{asset('assets/plugins/easy-responsive-tabs/css/easy-responsive-tabs.css?var='.rand())}}" rel="stylesheet"/>
<link href="{{asset('assets/plugins/OwlCarousel2/owl.carousel.min.css?var='.rand())}}" rel="stylesheet"/>
<link href="{{asset('assets/plugins/OwlCarousel2/owl.theme.default.min.css?var='.rand())}}" rel="stylesheet"/>
<link href="{{asset('assets/plugins/daterangepicker/daterangepicker.css?var='.rand())}}" rel="stylesheet"/>
<link href="{{asset('assets/plugins/datepicker/v1.9.0/css/bootstrap-datepicker3.min.css?var='.rand())}}" rel="stylesheet"/>
@endsection

@section('content')
<!-- Content Header (Page header) -->
<div class="content-header content-header-bg-color">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-3">
                <h1 class="m-0 text-dark">
                    Dashboard
                </h1>
            </div>
            <!-- /.col -->
            <div class="col-md-9 dashboard-breadcrumb-right">
                <div class="row">
                    @if($role->group == 'reseller' && $company->parent_id == null)
                    <div class="form-group col">
                        {{ Form::select('industry_id', $industry, null, ['class' => "form-control select2", 'id'=>'industry_id', 'style'=>"width: 100%;", 'autocomplete' => 'off', 'placeholder' => trans('labels.dashboard.industry'), 'data-placeholder' => trans('labels.dashboard.industry'), 'data-allow-clear' => 'true', 'target-data' => 'company_id']) }}
                    </div>
                    @endif
                    <div class="form-group col" id="company">
                        @if($role->group == 'zevo' || ($role->group == 'reseller' && $company->parent_id == null))
                        {{ Form::select('company_id', $companies, null, ['class' => "form-control select2", 'id'=>'company_id', 'style'=>"width: 100%;", 'autocomplete' => 'off', 'placeholder' => trans('labels.dashboard.company'), 'data-placeholder' => trans('labels.dashboard.company'), 'data-allow-clear' => 'true', 'target-data' => 'department_id']) }}
                        @else
                        <div style="display: none">
                            {{ Form::select('company_id', $companies, Auth::user()->company->first()->id, ['class' => "form-control select2", 'id'=>'company_id', 'style'=>"width: 100%;", 'autocomplete' => 'off', 'placeholder' => trans('labels.dashboard.company'), 'data-placeholder' => trans('labels.dashboard.company'), 'data-allow-clear' => 'true', 'disabled'=>true, 'target-data' => 'department_id']) }}
                        </div>
                        @endif
                    </div>
                    @if($role->group == 'zevo' || $role->group == 'company')
                    <div class="form-group col" id="department">
                        @if($role->group == 'zevo')
                        {{ Form::select('department_id', [], null,['class' => 'form-control select2', 'id'=>'department_id', 'style'=>'width: 100%;', 'autocomplete' => 'off', 'placeholder' => trans('labels.dashboard.department'), 'data-placeholder' => trans('labels.dashboard.department'), 'data-allow-clear' => 'true', 'disabled'=>true]) }}
                        @elseif($role->group == 'company')
                        {{ Form::select('department_id', $departments, null,['class' => 'form-control select2', 'id'=>'department_id', 'style'=>'width: 100%;', 'autocomplete' => 'off', 'placeholder' => trans('labels.dashboard.department'), 'data-placeholder' => trans('labels.dashboard.department'), 'data-allow-clear' => 'true']) }}
                        @endif
                    </div>
                    @else
                    <div class="form-group col" style="display: none">
                        {{ Form::select('department_id', $departments, null,['class' => 'form-control select2', 'id'=>'department_id', 'style'=>'width: 100%;', 'autocomplete' => 'off', 'placeholder' => trans('labels.dashboard.department'), 'data-placeholder' => trans('labels.dashboard.department'), 'data-allow-clear' => 'true']) }}
                    </div>
                    @endif
                    @if($role->group == 'zevo' || $role->group == 'company')
                    <div class="form-group col" id="age">
                        {{ Form::select('age', $age, null,['class' => 'form-control select2 age', 'id'=>'age', 'style'=>'width: 100%;', 'autocomplete' => 'off', 'placeholder' => trans('labels.dashboard.age'), 'data-placeholder' => trans('labels.dashboard.age'), 'data-allow-clear' => 'true']) }}
                    </div>
                    @endif
                    <input id="companiesId" name="companiesId" type="hidden" value="{{$companiesId}}">
                    <input id="roleType" name="roleType" type="hidden" value="{{$resellerType}}">
                </div>
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
    </div>
    <!-- /.container-fluid -->
</div>
<!-- /.content-header -->
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <!----------------------------- Dashboard Tabbing Start ------------------------->
        <div class="responsive-tabbing">
            <div id="dashboardTabs">
                <ul class="resp-tabs-list dashboard-tabs">
                    <li class="active" id="usage">
                        Usage
                    </li>
                    @if($role->group == 'zevo' || $role->group == 'company' || ($role->group == 'reseller' && $company->parent_id != null && $company->allow_app == 1))
                    <li id="behaviour">
                        Behaviour
                    </li>
                    @endif
                    {{--
                    <li id="psychological">
                        Psychological
                    </li>
                    --}}
                    @if($tab4Visibility == true)
                    <li id="audit">
                        Audit
                    </li>
                    @endif
                    @if($role->group != 'company')
                    <li id="booking">
                        Booking
                    </li>
                    @endif
                </ul>
                <div class="resp-tabs-container dashboard-tabs">
                    <!------------------------------- Tab-1 ------------------------------->
                    @include('newDashboard.partials.usage')
                    <!----------------------------- ./Tab-1 ----------------------------->
                    @if($role->group == 'zevo' || $role->group == 'company' || ($role->group == 'reseller' && $company->parent_id != null && $company->allow_app == 1))
                    <!----------------------------- Tab-2 ----------------------------->
                    @include('newDashboard.partials.behaviour')
                    <!----------------------------- ./Tab-2 ----------------------------->
                    @endif
                    <!----------------------------- Tab-3 ----------------------------->
                    {{-- @include('newDashboard.partials.psychological') --}}
                    <!----------------------------- ./Tab-3 ----------------------------->
                    @if($tab4Visibility == true)
                    <!----------------------------- Tab-4 ----------------------------->
                    @include('newDashboard.partials.audit')
                    <!----------------------------- ./Tab-4 ----------------------------->
                    @endif
                    @if($role->group != 'company')
                    <!----------------------------- Tab-5 ----------------------------->
                    @include('newDashboard.partials.booking')
                    <!----------------------------- ./Tab-5 ----------------------------->
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- /.container-fluid -->
</section>
<!-- /.content -->
<script id="auditTabCategoryTabTemplate" type="text/html">
    <div class="item text-center #active_class#" data-id="#id#">
        <a>
            <span class="d-block category-name">#category_name#</span>
            {{-- <span data-id="#id#" class="badge badge-success score_badge text-center font-13" style="background-color: #background-color#;">#percentage#%</span> --}}
        </a>
    </div>
</script>
<script id="auditTabSubCategoryTabTemplate" type="text/html">
    <div class="col-lg-4 col-md-6 col-xl-3">
        <div class="speed-chart-area mb-3">
            <div class="d-flex justify-content-center align-items-center">
                <div class="h-100">
                    <sapn class="score-status bg-green d-flex align-items-center" style="background-color: #background-color#;">
                        #sub_category_name#
                    </sapn>
                </div>
            </div>
            <br/>
            <canvas data-subcategorywisecompanyscoregaugechart-#id#="" class="gaugeChartSubScore">
            </canvas>
            <div class="speed-chart-text">
                <span class="score-counter color-green" style="color: #background-color#;">
                    #sub_category_percentage#%
                </span>
            </div>
        </div>
    </div>
</script>
@endsection
@section('after-scripts')
<!-- Datatable -->
<script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand()) }}">
</script>
<!-- jQuery Knob -->
<script src="{{ asset('assets/plugins/knob/jquery.knob.js?var='.rand()) }}">
</script>
<!-- jQuery chart -->
<script src="{{ asset('assets/plugins/chart.js/Chart.bundle.min.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/chart.js/chartjs-plugin-labels.js?var='.rand()) }}">
</script>
<!-- Gauge chart -->
<script src="{{ asset('assets/plugins/gauge/gauge.min.js?var='.rand()) }}">
</script>
<!-- DateRangePicker -->
<script src="{{ asset('assets/plugins/moment/moment.min.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/daterangepicker/daterangepicker.js?var='.rand()) }}">
</script>
<!-- Datepicker -->
<script src="{{ asset('assets/plugins/datepicker/bootstrap-datepicker.min.js?var='.rand()) }}">
</script>
<!-- Carousel -->
<script src="{{ asset('assets/plugins/OwlCarousel2/owl.carousel.min.js?var='.rand()) }}">
</script>

<!-- easy-responsive-tabs -->
<!-- https://webthemez.com/demo/easy-responsive-tabs/Index.html#parentHorizontalTab1|ChildVerticalTab_13 -->
<script src="{{ asset('assets/plugins/easy-responsive-tabs/js/easyResponsiveTabs.js?var='.rand()) }}">
</script>
<!-- jQuery New Dashboard Chart -->
{{--
<script src="{{ asset('assets/dist/js/new-dashboard-chart.js') }}">
</script>
--}}
{{-- @php
    $role     = getUserRole();
@endphp
@if($role->group == 'reseller')
<script type="text/javascript">
    $(document).ready(function(){
        console.log($('#audit').click());
    });
</script>
@endif; --}}
<script type="text/javascript">
    var urls = {
        usage: '{{ route('dashboard.getAppUsageTabData') }}',
        behaviour: '{{ route('dashboard.getPhysicalTabData') }}',
        psychological: '{{ route('dashboard.getPsychologicalTabData') }}',
        audit: '{{ route('dashboard.getAuditTabData') }}',
        deptUrl: '{{ route('admin.ajax.companyDepartment', ':id') }}',
        industryCompany: '{{ route('admin.ajax.industryCompany', ':id') }}',
        showMeditationHours: '{{ route('admin.ajax.showmeditationhours', ':id') }}',
        booking: '{{ route('dashboard.getBookingTabData') }}',
    },
    defaultImage = '{{ asset('assets/dist/img/user1-128x128.jpg') }}',
    companyScoreColorCode = {
        red: "{{ config('zevolifesettings.zc_survey_score_color_code.red') }}",
        yellow: "{{ config('zevolifesettings.zc_survey_score_color_code.yellow') }}",
        green: "{{ config('zevolifesettings.zc_survey_score_color_code.green') }}",
        grey: "{{ config('zevolifesettings.zc_survey_score_color_code.grey') }}",
    };
</script>
<script type="text/javascript">

</script>
<!-- V2 Dashboard JS -->
<script src="{{ mix('js/v2dashboard.js') }}">
</script>
@endsection
