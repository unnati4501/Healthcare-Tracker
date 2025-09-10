@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/easy-responsive-tabs/css/easy-responsive-tabs.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/OwlCarousel2/owl.carousel.min.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/OwlCarousel2/owl.theme.default.min.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/daterangepicker/daterangepicker.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/datepicker/v1.9.0/css/bootstrap-datepicker3.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('dashboard.partials.header')
<!-- /.content-header -->
@endsection

@section('content')
@php
$user = auth::user();
$role = getUserRole($user);
$checkTeamAccess = getCompanyPlanAccess($user, 'team-selection');
$teamSessionBlock = "";
$firstBlockClass = "col-xl-7";
if($role->group == 'company' && !$checkTeamAccess) {
    $teamSessionBlock = "d-none";
    $firstBlockClass = "col-xl-12";
}
@endphp
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <!----------------------------- Dashboard Tabbing Start ------------------------->
        <div class="nav-tabs-wrap" id="dashboardTabs">
            <ul class="nav nav-tabs tabs-line-style dashboard-tabs" role="tablist">
                @if($role->slug != 'counsellor' && $role->slug != 'wellbeing_specialist' && $role->slug != 'wellbeing_team_lead')
                <li class="nav-item main-tabs" data-id="usage" id="usageTab">
                    <a aria-controls="usage" aria-selected="true" class="nav-link active" data-bs-toggle="tab" href="#usage" id="usage-tab" role="tab">
                        {{ trans('dashboard.tabs.usage') }}
                    </a>
                </li>
                @if(($role->group == 'zevo' || $role->group == 'company' || ($role->group == 'reseller' && $company->parent_id != null && $company->allow_app == 1)) && $role->slug != 'wellbeing_team_lead')
                <li class="nav-item main-tabs" data-id="behaviour" id="behaviourTab">
                    <a aria-controls="behaviour" aria-selected="false" class="nav-link" data-bs-toggle="tab" href="#behaviour" id="behaviour-tab" role="tab">
                        {{ trans('dashboard.tabs.behaviour') }}
                    </a>
                </li>
                @endif
                @if($auditTabVisibility == true && $role->slug != 'wellbeing_team_lead')
                <li class="nav-item main-tabs" data-id="audit">
                    <a aria-controls="audit" aria-selected="false" class="nav-link" data-bs-toggle="tab" href="#audit" id="audit-tab" role="tab">
                        {{ trans('dashboard.tabs.audit') }}
                    </a>
                </li>
                @endif
                @if(($role->group == 'zevo' || ($role->group == 'company' && getCompanyPlanAccess($user, 'event')) || ($role->group == 'reseller' && getDTAccessForParentsChildCompany($user, 'event'))) && $role->slug != 'wellbeing_specialist')
                @if(($eventTabVisibility == true && $role->slug != 'wellbeing_team_lead') || (!empty($wsDetails) && $wsDetails->is_cronofy && $wsDetails->responsibilities != 1))
                <li class="nav-item main-tabs" data-id="booking">
                    <a aria-controls="booking" aria-selected="false" class="nav-link" data-bs-toggle="tab" href="#booking" id="booking-tab" role="tab">
                        {{ trans('dashboard.tabs.booking') }}
                    </a>
                </li>
                @endif
                @endif
                @endif
                @if($role->group == 'zevo' && $role->slug == 'counsellor' && $role->slug != 'wellbeing_team_lead')
                <li class="nav-item main-tabs" data-id="eapactivity">
                    <a aria-controls="eapactivity" aria-selected="false" class="nav-link" data-bs-toggle="tab" href="#eapactivity" id="eapactivity-tab" role="tab">
                        {{ trans('dashboard.tabs.digitaltherapy') }}
                    </a>
                </li>
                @endif
                @if($role->slug != 'counsellor' && $dtTabVisibility == true)
                <li class="nav-item main-tabs" data-id="digitaltherapy">
                    <a aria-controls="digitaltherapy" aria-selected="false" class="nav-link" data-bs-toggle="tab" href="#digitaltherapy" id="digitaltherapy-tab" role="tab" data-rolegroup = "{{$role->slug}}">
                        {{ trans('dashboard.tabs.digitaltherapy') }}
                    </a>
                </li>
                @endif
            </ul>
            <div class="tab-content" id="dashboardContent">
                @if($role->slug != 'counsellor' && $role->slug != 'wellbeing_specialist' && $role->slug != 'wellbeing_team_lead')
                <div aria-labelledby="usage-tab" class="tab-pane fade show active" id="usage" role="tabpanel">
                    <!------------------------------- Tab-1 ------------------------------->
                    @include('dashboard.tabs.usage')
                    <!----------------------------- ./Tab-1 ----------------------------->
                </div>
                @if(($role->group == 'zevo' || $role->group == 'company' || ($role->group == 'reseller' && $company->parent_id != null && $company->allow_app == 1)) && $role->slug != 'wellbeing_team_lead')
                <div aria-labelledby="behaviour-tab" class="tab-pane fade" id="behaviour" role="tabpanel">
                    <!----------------------------- Tab-2 ----------------------------->
                    @include('dashboard.tabs.behaviour')
                    <!----------------------------- ./Tab-2 ----------------------------->
                </div>
                @endif
                @if($auditTabVisibility == true && $role->slug != 'wellbeing_team_lead')
                <div aria-labelledby="audit-tab" class="tab-pane fade" id="audit" role="tabpanel">
                    <!----------------------------- Tab-3 ----------------------------->
                    @include('dashboard.tabs.audit')
                    <!----------------------------- ./Tab-3 ----------------------------->
                </div>
                @endif
                @if(($role->group == 'zevo' || ($role->group == 'company' && getCompanyPlanAccess($user, 'event')) || ($role->group == 'reseller' && getDTAccessForParentsChildCompany($user, 'event'))) && $role->slug != 'wellbeing_specialist')
                @if(($eventTabVisibility == true && $role->slug != 'wellbeing_team_lead') || (!empty($wsDetails) && $wsDetails->is_cronofy && $wsDetails->responsibilities != 1))
                <div aria-labelledby="booking-tab" class="tab-pane fade" id="booking" role="tabpanel">
                    <!----------------------------- Tab-4 ----------------------------->
                    @include('dashboard.tabs.booking')
                    <!----------------------------- ./Tab-4 ----------------------------->
                </div>
                @endif
                @endif
                @endif
                @if($role->group == 'zevo' && $role->slug != 'wellbeing_team_lead')
                <div aria-labelledby="eapactivity-tab" class="tab-pane fade" id="eapactivity" role="tabpanel">
                    <!----------------------------- Tab-5 ----------------------------->
                    @include('dashboard.tabs.eap-activity')
                    <!----------------------------- ./Tab-5 ----------------------------->
                </div>
                @endif
                @if(($role->slug != 'counsellor' && $dtTabVisibility == true) || (!empty($wsDetails) && $wsDetails->is_cronofy && $wsDetails->responsibilities != 2))
                <div aria-labelledby="digitaltherapy-tab" class="tab-pane fade" id="digitaltherapy" role="tabpanel">
                    <!----------------------------- Tab-5 ----------------------------->
                    @include('dashboard.tabs.digital-therapy')
                    <!----------------------------- ./Tab-5 ----------------------------->
                </div>
                @endif
            </div>
        </div>
    </div>
    <!-- /.container-fluid -->
</section>
<!-- /.content -->
<script id="auditTabCategoryTabTemplate" type="text/html">
    <div class="item text-center #active_class#" data-id="#id#">
        <a>
            <img class="ms-auto me-auto" src="#category_image#" />
            <span class="d-block category-name">
                #category_name#
            </span>
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
        locUrl: '{{ route('admin.ajax.companyLocation', ':id') }}',
        departmentLocUrl: '{{ route('admin.ajax.departmentLocation', ':id') }}',
        locDepartmentUrl: '{{ route('admin.ajax.locationFromDepartments', ':id') }}',
        industryCompany: '{{ route('admin.ajax.industryCompany', ':id') }}',
        showMeditationHours: '{{ route('admin.ajax.showmeditationhours', ':id') }}',
        booking: '{{ route('dashboard.getBookingTabData') }}',
        eapActivity: '{{ route('dashboard.getEapActivityTabData') }}',
        digitalTherapy: '{{ route('dashboard.getDigitalTherapyTabData') }}',
        role: '{{$role}}'
    },
    defaultImage = '{{ asset('assets/dist/img/user1-128x128.jpg') }}',
    companyScoreColorCode = {
        red: "{{ config('zevolifesettings.zc_survey_score_color_code.red') }}",
        yellow: "{{ config('zevolifesettings.zc_survey_score_color_code.yellow') }}",
        green: "{{ config('zevolifesettings.zc_survey_score_color_code.green') }}",
        grey: "{{ config('zevolifesettings.zc_survey_score_color_code.grey') }}",
    };
</script>
<!-- V2 Dashboard JS -->
<script src="{{ mix('js/dashboard.js') }}">
</script>
@endsection
