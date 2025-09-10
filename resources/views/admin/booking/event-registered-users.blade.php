@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.booking.breadcrumb', [
    'mainTitle' => trans('marketplace.users.title.index', [
        'event_name' => $event->name
    ]),
    'breadcrumb' => Breadcrumbs::render('booking.book_event.users'),
    'back' => true

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
                {{ Form::open(['route' => ['admin.bookings.registered-users', $bookingLog->id], 'class' => 'form-horizontal', 'method' => 'get', 'role' => 'form', 'id' => 'eventRegisteredUsersSearch']) }}
                <div class="search-outer d-md-flex justify-content-between">
                    <div>
                        <div class="form-group">
                            {{ Form::text('name', request()->get('name'), ['class' => 'form-control', 'placeholder' => trans('marketplace.users.filter.name'), 'id' => 'name', 'autocomplete' => 'off']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::text('email', request()->get('email'), ['class' => 'form-control', 'placeholder' => trans('marketplace.users.filter.email'), 'id' => 'email', 'autocomplete' => 'off']) }}
                        </div>
                    </div>
                    <div class="search-actions align-self-start">
                        <button class="me-md-4 filter-apply-btn" type="submit">
                            {{ trans('buttons.general.apply') }}
                        </button>
                        <a class="filter-cancel-icon" href="{{ route('admin.bookings.registered-users', $bookingLog->id) }}">
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
        <!-- .grid -->
        <div class="card">
            <div class="card-body">
                <div class="card-table-outer">
                    <div class="table-responsive">
                        <table class="table custom-table" id="eventRegUsersManagement">
                            <thead>
                                <tr>
                                    <th>
                                        {{ trans('marketplace.users.table.user_name') }}
                                    </th>
                                    <th>
                                        {{ trans('marketplace.users.table.email') }}
                                    </th>
                                    <th>
                                        {{ trans('marketplace.users.table.registration_date') }}
                                    </th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.grid -->
    </div>
</section>
@endsection
@section('after-scripts')
<script src="{{asset('assets/plugins/moment/moment.min.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{asset('assets/plugins/moment/moment-timezone-with-data-10-year-range.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}" type="text/javascript">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}" type="text/javascript">
</script>

<script src="{{asset('assets/plugins/datatables/jszip.min.js?var='.rand())}}">
</script>
<script type="text/javascript">
    var timezone = `{{ $timezone }}`,
        date_format = `{{ $date_format }}`,
        pagination = {
            value: {{ $pagination }},
            previous: `{!! trans('buttons.pagination.previous') !!}`,
            next: `{!! trans('buttons.pagination.next') !!}`,
            entry_per_page: `{!! trans('buttons.pagination.entry_per_page') !!}`,
        },
        button = {
            export: '<i class="far fa-file-excel me-3 align-middle"></i> {{ trans('marketplace.users.buttons.export_to_excel') }}',
        },
        labels = {
            exportFile: `{{ $event->name }} Registered Users`,
        },
        url = {
            datatable: `{{ route('admin.bookings.get-registered-users', $bookingLog->id) }}`,
        };
</script>
<script src="{{ mix('js/marketplace/reg-users.js') }}" type="text/javascript">
</script>
@endsection
