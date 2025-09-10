@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/OwlCarousel2/owl.carousel.min.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/OwlCarousel2/owl.theme.default.min.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{asset('assets/plugins/toastr/toastr.min.css?var='.rand())}}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@if($role->slug == 'wellbeing_specialist')
@include('admin.booking.breadcrumb', [
    'mainTitle' => trans('event.title.index'),
    'breadcrumb' => Breadcrumbs::render('booking.index'),
])
@else
@include('admin.booking.breadcrumb', [
    'mainTitle' => trans('marketplace.title.booking'),
    'breadcrumb' => Breadcrumbs::render('booking.index'),
])
@endif
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
                <div aria-labelledby="booked-tab" id="booked-tab">
                    <!-- search-block -->
                    <div class="card search-card">
                        <div class="card-body pb-0">
                            <h4 class="d-md-none">
                                {{ trans('buttons.general.filter') }}
                            </h4>
                            {{ Form::open(['route' => 'admin.bookings.index', 'class' => 'form-horizontal', 'method' => 'get', 'role' => 'form', 'id' => 'bookedEventSearch']) }}
                            <div class="search-outer d-md-flex justify-content-between">
                                <div>
                                    <div class="form-group">
                                        {{ Form::text('bookeTabdEventName', request()->get('bookeTabdEventName'), ['class' => 'form-control', 'placeholder' => trans('marketplace.filter.name'), 'id' => 'bookeTabdEventName', 'autocomplete' => 'off']) }}
                                    </div>
                                    <div class="form-group">
                                        {{ Form::select('bookedTabEventCategory', $categories, request()->get('bookedTabEventCategory'), ['class' => 'form-control select2', 'id' => 'bookedTabEventCategory', 'placeholder' => trans('marketplace.filter.category'), 'data-placeholder' => trans('marketplace.filter.category'), 'data-allow-clear' => 'true']) }}
                                    </div>
                                    <div class="form-group">
                                        @if($companyDisable)
                                        {{ Form::text('', $company->name, ['class' => 'form-control', 'placeholder' => 'Search company', 'disabled' => true]) }}
                                        {{ Form::hidden('bookedTabEventCompany', $company->id, ['id' => 'bookedTabEventCompany']) }}
                                        @else
                                        {{ Form::select('bookedTabEventCompany', $comapanies, request()->get('bookedTabEventCompany'), ['class' => 'form-control select2', 'id' => 'bookedTabEventCompany', 'placeholder' => trans('marketplace.filter.company'), 'data-placeholder' => trans('marketplace.filter.company'), 'data-allow-clear' => 'true']) }}
                                        @endif
                                    </div>
                                    @if ($role->slug != 'wellbeing_specialist')
                                        <div class="form-group">
                                            {{ Form::select('bookedTabEventPresenter', $presenters, request()->get('bookedTabEventPresenter'), ['class' => 'form-control select2', 'id' => 'bookedTabEventPresenter', 'placeholder' => trans('marketplace.filter.presenter'), 'data-placeholder' => trans('marketplace.filter.presenter'), 'data-allow-clear' => 'true']) }}
                                        </div>
                                    @endif
                                    <div class="form-group">
                                        {{ Form::select('eventStatus', $statuses, request()->get('eventStatus'), ['class' => 'form-control select2', 'id' => 'eventStatus', 'placeholder' => trans('marketplace.filter.status'), 'data-placeholder' => trans('marketplace.filter.status'), 'data-allow-clear' => 'true']) }}
                                    </div>
                                </div>
                                <div class="search-actions align-self-start">
                                    <button class="me-md-4 filter-apply-btn" type="submit">
                                        {{ trans('buttons.general.apply') }}
                                    </button>
                                    <a class="filter-cancel-icon" href="{{ route('admin.bookings.index') }}" id="resetBookedEventSearchBtn">
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
                    <!-- table -->
                    <div class="card" id="booked-tab-result-block">
                        <div class="card-body">
                            <div class="card-table-outer" id="teamActivities-wrap">
                                @if($role->slug == 'health_coach')
                                    <div class="dt-buttons">
                                        <button class="btn btn-primary" id="exportBookingsbtn" type="button">
                                            <span>
                                                <i class="far fa-envelope me-3 align-middle">
                                                </i>
                                                {{trans('buttons.general.export')}}
                                            </span>
                                        </button>
                                    </div>
                                @endif
                                <div class="table-responsive">
                                    <table class="table custom-table" id="bookedEvents">
                                        <thead>
                                            <tr>
                                                <th class="no-sort th-btn-4">
                                                </th>
                                                <th>
                                                    {{ trans('marketplace.table.event_name') }}
                                                </th>
                                                <th>
                                                    {{ trans('marketplace.table.company') }}
                                                </th>
                                                <th>
                                                    {{ trans('marketplace.table.category') }}
                                                </th>
                                                <th style="display: {{ ($role->group == 'company' || $role->slug == 'super_admin' || $role->group == 'reseller') ? 'table-cell' : 'none' }};">
                                                    {{ trans('marketplace.table.presenter') }}
                                                </th>
                                                <th class="th-btn-5">
                                                    {{ trans('marketplace.table.date_time') }}
                                                </th>
                                                <th class="text-center">
                                                    {{ trans('marketplace.table.registered_users') }}
                                                </th>
                                                <th class="text-center">
                                                    {{ trans('marketplace.table.status') }}
                                                </th>
                                                <th class="th-btn-2 no-sort">
                                                    {{ trans('marketplace.table.actions') }}
                                                </th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center bg-white" id="booked-tab-process-block" style="display: none;">
                        <p class="m-2 p-2">
                            <span class="far fa-spinner fa-spin">
                            </span>
                            {{ trans('marketplace.messages.loading_events') }}
                        </p>
                    </div>
                    <!-- /.table -->
                </div>
    </div>
</section>
@include('admin.booking.copy_event')
@include('admin.booking.export_modal')
@include('admin.booking.cancel_model')
@endsection
@section('after-scripts')
<script src="{{ asset('js/external/jquery.form.min.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{asset('assets/plugins/moment/moment.min.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/moment/moment-timezone-with-data-10-year-range.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}">
</script>
<script src="{{ asset('assets/plugins/OwlCarousel2/owl.carousel.min.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/toastr/toastr.min.js?var='.rand())}}" type="text/javascript">
</script>
<script type="text/javascript">
    var timezone = '{{ $timezone }}',
        loginemail = '{{ $loginemail }}',
        roleSlug =  '{{ $role->slug }}',
        labels = {
            loadMore: "{{ trans('marketplace.messages.load_more_events') }}",
            loadingText: "<span class='far fa-spinner fa-spin'></span>",
            eventDetailsCopied: "{{ trans('marketplace.messages.event_copied') }}"
        },
        urls = {
            eventsByCategory: "{{ route('admin.marketplace.get-events') }}",
            getBookedEvents: "{{ route('admin.bookings.get-booked-events') }}",
            cancelEvents: "{{ route('admin.bookings.cancel-event') }}",
        },
        dataTableConf = {
            pagination: {
                value: {{ $pagination }},
                previous: `{!! trans('buttons.pagination.previous') !!}`,
                next: `{!! trans('buttons.pagination.next') !!}`,
            },
        },
        visibleCompany      = {{ !empty($company) && ($company->is_reseller || !is_null($company->parent_id)) ? 'true' : 'false' }},
        visibleHealthCoach  = {{ !empty($role) && ($role->slug != 'health_coach') ? 'true' : 'false' }};
        visibleWellbeingSpecialist  = {{ !empty($role) && ($role->slug != 'wellbeing_specialist') ? 'true' : 'false' }};
</script>
<script src="{{ mix('js/marketplace/bookings.js') }}">
</script>
@endsection
