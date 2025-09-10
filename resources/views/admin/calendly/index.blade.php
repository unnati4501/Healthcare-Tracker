@extends('layouts.app')

@section('after-styles')
<!-- DataTables -->
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.calendly.breadcrumb', [
  'mainTitle' => trans('calendly.title.manage'),
  'breadcrumb' => 'calendly.index',
  'book' => $role->slug=='counsellor' ? true : false
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card search-card">
            <div class="card-body pb-0">
                {{ Form::open(['route' => 'admin.sessions.index', 'class' => 'form-horizontal', 'method'=>'get','role' => 'form']) }}
                <div class="search-outer d-md-flex justify-content-between">
                    <div>
                        <div class="form-group">
                            {{ Form::text('email', request()->get('email'), ['class' => 'form-control', 'placeholder' => 'Search by client email', 'id' => 'email', 'autocomplete' => 'off']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::text('user', request()->get('user'), ['class' => 'form-control', 'placeholder' => 'Search by client name', 'id' => 'user', 'autocomplete' => 'off']) }}
                        </div>
                        @if($company_col_visibility)
                        <div class="form-group">
                            {{ Form::select('company', $companies, request()->get('company'), ['class' => 'form-control select2', 'id' => 'company', 'placeholder' => "", 'data-placeholder' => "Select company", 'data-allow-clear' => 'true']) }}
                        </div>
                        @endif
                        <div class="form-group">
                            {{ Form::select('duration', $duration, request()->get('duration'), ['class' => 'form-control select2', 'id' => 'duration', 'placeholder' => "", 'data-placeholder' => "Select time", 'data-allow-clear' => 'true']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::select('status', $status, request()->get('status'), ['class' => 'form-control select2', 'id' => 'status', 'placeholder' => "", 'data-placeholder' => "Select status", 'data-allow-clear' => 'true']) }}
                        </div>
                    </div>
                    <div class="search-actions align-self-start">
                        <button class="me-md-4 filter-apply-btn" type="submit">
                            {{ trans('buttons.general.apply') }}
                        </button>
                        <a class="filter-cancel-icon" href="{{ route('admin.sessions.index') }}">
                            <i class="far fa-times">
                            </i>
                        </a>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="card-table-outer" id="calendlyManagement-wrap">
                    <div class="table-responsive">
                        <table class="table custom-table" id="calendlyManagement">
                            <thead>
                                <tr>
                                    <th>
                                        {{ trans('calendly.table.updated_at') }}
                                    </th>
                                    <th>
                                        {{ trans('calendly.table.name') }}
                                    </th>
                                    <th>
                                        {{ trans('calendly.table.user') }}
                                    </th>
                                    <th>
                                        {{ trans('calendly.table.email') }}
                                    </th>
                                    <th>
                                        {{ trans('calendly.table.counsellor') }}
                                    </th>
                                    <th>
                                        {{ trans('calendly.table.company') }}
                                    </th>
                                    <th>
                                        {{ trans('calendly.table.duration') }}
                                    </th>
                                    <th>
                                        {{ trans('calendly.table.datetime') }}
                                    </th>
                                    <th>
                                        {{ trans('calendly.table.status') }}
                                    </th>
                                    <th class="th-btn-4 no-sort">
                                        {{ trans('calendly.table.action') }}
                                    </th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
<!-- include datatable css -->
@section('after-scripts')
<!-- DataTables -->
<script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand()) }}">
</script>
<script type="text/javascript">
    var url = {
        datatable: `{{ route('admin.sessions.getSessions') }}`,
        complete: `{{route('admin.sessions.complete','/')}}`
    },
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    },
    companyVisibility = `{{ $company_col_visibility }}`,
    message = {
        completed: `{{ trans('calendly.messages.completed') }}`,
        somethingWentWrong: `{{ trans('calendly.messages.something_wrong_try_again') }}`,
    };
</script>
<script src="{{ mix('js/calendly/index.js') }}">
</script>
@endsection
