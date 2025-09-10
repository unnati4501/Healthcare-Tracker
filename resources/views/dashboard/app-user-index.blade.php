@extends('layouts.app')
@section('content-header')
<!-- Content Header (Page header) -->
@if($dtTabVisibility == true)
@include('dashboard.partials.header')
@endif
<!-- /.content-header -->
@endsection
@section('content')
@if($dtTabVisibility == false)
<div class="content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md">
                <h1 class="m-0 text-dark">
                    Welcome 
                </h1>
            </div>
        </div>
    </div>
</div>
@endif
<section class="content">
    <div class="container-fluid">
        @if((!empty($wsDetails) && $wsDetails->is_cronofy == false) || ($role->slug == 'health_coach' && empty($wcDetails)) || ($role->slug == 'health_coach' && !empty($wcDetails) && $wcDetails->is_cronofy == false))
        <div class="card form-card">
            <div class="card-body">
                <h4 class="text-primary">Hello {{auth::user()->first_name}},</h4>
                <p class="mb-0">Please complete the below steps to verify your account.</p>
                <div class="row flex-row-reverse">
                    <div class="col-md-6">
                        <img src="{{asset('assets/dist/img/Authentication-vector.svg')}}" alt="" class="verify-block-image">
                    </div>
                    <div class="col-md-6">
                        <div class="verify-block-outer">
                            <div>
                                <span>Step 1: </span>
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="{{ route('admin.users.editProfile') }}" title="Verify Details"><button class="btn btn-primary">Verify Details</button></a>
                                    @if((!empty($wsDetails->is_profile) && $wsDetails->is_profile == true) || (!empty($wcDetails->is_profile) && $wcDetails->is_profile == true))
                                        <i class="far fa-2x fa-check-circle"></i>
                                    @endif
                                </div>

                            </div>
                            <div>
                                <span>Step 2: </span>
                                <div class="d-flex justify-content-between align-items-center">
                                    @if((!empty($wsDetails->is_authenticate) && $wsDetails->is_authenticate == true) || (!empty($wcDetails->is_authenticate) && $wcDetails->is_authenticate == true))
                                        @if($calendarCount >= 2)
                                        <button class="btn btn-primary" disabled>Authenticate Calendars</button>
                                        @else
                                        <a href="{{ route('admin.cronofy.index') }}" title="Authenticate Calendars"><button class="btn btn-primary">Authenticate Calendars</button></a>
                                        @endif
                                        <i class="far fa-2x fa-check-circle"></i>
                                    @elseif((!empty($wsDetails->is_profile) && $wsDetails->is_profile == true) || (!empty($wcDetails->is_profile) && $wcDetails->is_profile == true))
                                        <a href="{{ route('admin.cronofy.index') }}" title="Authenticate Calendars"><button class="btn btn-primary">Authenticate Calendars</button></a>
                                    @else
                                        <button class="btn btn-primary" disabled>Authenticate Calendars</button>
                                    @endif
                                </div>
                            </div>
                            <div>
                                <span>Step 3: </span>
                                <div class="d-flex justify-content-between align-items-center">
                                    @if((!empty($wsDetails->is_profile) && $wsDetails->is_authenticate == true) || (!empty($wcDetails->is_authenticate) && $wcDetails->is_authenticate == true))
                                        <a href="{{ route('admin.cronofy.availability') }}" title="Set Availability"><button class="btn btn-primary">Set Availability</button></a>
                                    @else
                                        <button class="btn btn-primary" disabled>Set Availability</button>
                                    @endif
                                    @if((!empty($wsDetails->is_availability) && $wsDetails->is_availability == true) || (!empty($wcDetails->is_availability) && $wcDetails->is_availability == true))
                                        <i class="far fa-2x fa-check-circle"></i>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @if((!empty($wsDetails) && $wsDetails->is_profile && $wsDetails->is_availability && $wsDetails->is_authenticate) || (!empty($wcDetails) && $wcDetails->is_profile && $wcDetails->is_availability && $wcDetails->is_authenticate))
                        <div class="border-top pt-3 text-end">
                                <a href="{{ route('admin.cronofy.updateDashboad') }}" title="Authenticate Calendars"><button class="btn btn-primary" type="button">Submit</button></a>
                                <a href="#" class="tooltip-icon ms-3 align-middle font-16" title="Clicking on Submit will verify your account & allow you to explore all  features." data-bs-toggle="tooltip" data-placement="bottom">
                                <i class="fal fa-info-circle"></i>
                            </a>
                        </div>
                        @endif
                    </div>

                </div>

            </div>
        </div>
        @else
        <div class="nav-tabs-wrap" id="dashboardTabs">
            <ul class="nav nav-tabs tabs-line-style dashboard-tabs" role="tablist">
                @if($role->slug == 'wellbeing_specialist' && (!empty($wsDetails) && $wsDetails->is_cronofy && $wsDetails->responsibilities != 1))
                <li class="nav-item main-tabs" data-id="booking">
                    <a aria-controls="booking" aria-selected="false" class="nav-link" data-bs-toggle="tab" href="#booking" id="booking-tab" role="tab" >
                        {{ trans('dashboard.tabs.booking') }}
                    </a>
                </li>
                @endif
                @if(($role->slug != 'counsellor' && $dtTabVisibility == true & $role->slug != 'wellbeing_specialist') || (!empty($wsDetails) && $wsDetails->is_cronofy && $wsDetails->responsibilities != 2))
                <li class="nav-item main-tabs" data-id="digitaltherapy">
                    <a aria-controls="digitaltherapy" aria-selected="true" class="nav-link" data-bs-toggle="tab" href="#digitaltherapy" id="digitaltherapy-tab" role="tab" data-rolegroup = "{{$role->slug}}">
                        {{ trans('dashboard.tabs.digitaltherapy') }}
                    </a>
                </li>
                @endif
                
            </ul>
            <div class="tab-content" id="dashboardContent">
                @if($role->slug == 'wellbeing_specialist' && (!empty($wsDetails) && $wsDetails->is_cronofy && $wsDetails->responsibilities != 1))
                <div aria-labelledby="booking-tab" class="tab-pane" id="booking" role="tabpanel">
                    @include('dashboard.tabs.booking')
                </div>
                @endif
                @if(($role->slug != 'counsellor' && $dtTabVisibility == true && $role->slug != 'wellbeing_specialist') || (!empty($wsDetails) && $wsDetails->is_cronofy && $wsDetails->responsibilities != 2))
                <div aria-labelledby="digitaltherapy-tab" class="tab-pane fade" id="digitaltherapy" role="tabpanel">
                    <!----------------------------- Tab-5 ----------------------------->
                    @include('dashboard.tabs.digital-therapy')
                    <!----------------------------- ./Tab-5 ----------------------------->
                </div>
                @endif
            </div>
        </div>
        @endif
        @if($dtTabVisibility == false)
        <div class="card">
            <div class="card-body">
               <div class="welcome-area">
                   <h4 class="text-center pt-4">
                       Hi, {{ ucfirst(Auth::user()->first_name) }}
                   </h4>
                   <h1 class="text-center mt-3">
                       Welcome to
                       <span class="text-primary">
                           {{ config("app.name") }}
                       </span>
                   </h1>
                   <div class="text-center mt-3">
                       This Portal provides back-end access and you may not require it. Please follow the instructions below.
                   </div>
                   @if($role->slug == 'user')
                   @if(!empty($company) && $company->allow_portal)
                   <div class="text-center mt-3">
                       Please login using the Portal
                   </div>
                   <div class="text-center mt-3">
                       <a class="btn btn-primary" href="{{ addhttp($portal_domain) }}" target="_blank">
                           <span class="align-middle">
                               Login
                           </span>
                       </a>
                   </div>
                   @endif

                   @if(!empty($company) && $company->allow_portal && $company->allow_app)
                   <div class="text-center text-muted mt-3">
                       OR
                   </div>
                   @endif

                   @if(!empty($company) && $company->allow_app)
                   <div class="text-center mt-3">
                       Please login using the mobile app
                   </div>
                   <div class="text-center mt-3 mb-4">
                       You can download the {{ config("app.name") }} app from the below link
                   </div>
                   <div class="app-store-area">
                       <div class="row">
                           <div class="col-sm-6 mb-3">
                               <div class="app-store-box">
                                   <div class="app-store-icon">
                                       <i class="fab fa-android">
                                       </i>
                                   </div>
                                   <div class="company-name">
                                       {{ config("app.name") }}
                                   </div>
                                   <a href="{{ config('zevolifesettings.app_store_link.android') }}" target="_blank" title="Android">
                                       Android
                                   </a>
                               </div>
                           </div>
                           <div class="col-sm-6 mb-3">
                               <div class="app-store-box">
                                   <div class="app-store-icon">
                                       <i class="fab fa-apple">
                                       </i>
                                   </div>
                                   <div class="company-name">
                                       {{ config("app.name") }}
                                   </div>
                                   <a href="{{ config('zevolifesettings.app_store_link.ios') }}" target="_blank" title="Android">
                                       Apple
                                   </a>
                               </div>
                           </div>
                       </div>
                   </div>
                   @endif
                   @else
                   <div class="mt-3 welcome-msg">
                       Dashboard Coming Soon
                   </div>
                   @endif
               </div>
           </div>
       </div>
       @endif
       
    </div>
</section>
@endsection
@section('after-scripts')
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
@if((!empty($wsDetails) && $wsDetails->is_cronofy == false) || ($role->slug == 'health_coach' && empty($wcDetails)) || ($role->slug == 'health_coach' && !empty($wcDetails) && $wcDetails->is_cronofy == false))
<script type="text/javascript">
    $(document).ready(function() {
        $('body.sidebar-mini').addClass('sidebar-collapse');
    });
</script>
@else
<script type="text/javascript">
    var urls = {
        digitalTherapy: '{{ route('dashboard.getDigitalTherapyTabData') }}',
        booking: '{{ route('dashboard.getBookingTabData') }}',
        role : '{{$role}}'
    },
    defaultImage = '{{ asset('assets/dist/img/user1-128x128.jpg') }}',
    companyScoreColorCode = {
        red: "{{ config('zevolifesettings.zc_survey_score_color_code.red') }}",
        yellow: "{{ config('zevolifesettings.zc_survey_score_color_code.yellow') }}",
        green: "{{ config('zevolifesettings.zc_survey_score_color_code.green') }}",
        grey: "{{ config('zevolifesettings.zc_survey_score_color_code.grey') }}",
    };
</script>
<script src="{{ mix('js/dashboard.js') }}">
</script>
@endif
@endsection

