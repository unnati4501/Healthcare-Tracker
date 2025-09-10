@extends('layouts.app')

@section('after-styles')
<!-- DataTables -->
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.clientlist.breadcrumb', [
  'mainTitle' => trans('clientlist.title.index'),
  'breadcrumb' => Breadcrumbs::render('clientlist.index'),
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <!-- .search-block -->
        <div class="card search-card">
            <div class="card-body pb-0">
                {{ Form::open(['route' => 'admin.clientlist.index', 'class' => 'form-horizontal', 'method' => 'get', 'role' => 'frmClientList']) }}
                <div class="search-outer d-md-flex justify-content-between">
                    <div>
                        <div class="form-group">
                            {{ Form::text('name', request()->get('name'), ['class' => 'form-control', 'placeholder' => trans('clientlist.filters.name'), 'id' => 'name', 'autocomplete' => 'off']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::text('email', request()->get('email'), ['class' => 'form-control', 'placeholder' => trans('clientlist.filters.email'), 'id' => 'email', 'autocomplete' => 'off']) }}
                        </div>
                        @if($role == 'super_admin')
                        <div class="form-group">
                            {{ Form::select('company', $companies, request()->get('company'), ['class' => 'form-control select2', 'id' => 'company', 'placeholder' => trans('clientlist.filters.company'), 'data-placeholder' => trans('clientlist.filters.company'), 'data-allow-clear' => 'true']) }}
                        </div>
                        @endif
                    </div>
                    <div class="search-actions align-self-start">
                        <button class="me-md-4 filter-apply-btn" type="submit">
                            {{ trans('buttons.general.apply') }}
                        </button>
                        <a class="filter-cancel-icon" href="{{ route('admin.clientlist.index') }}">
                            <i class="far fa-times">
                            </i>
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
        <!-- .grid strt -->
        <div class="card">
            <div class="card-body">
                <div class="card-table-outer">
                    <div class="table-responsive">
                        <table class="table custom-table" id="clientListManagement">
                            <thead>
                                <tr>
                                    <th>
                                        {{ trans('clientlist.table.client_name') }}
                                    </th>
                                    <th>
                                        {{ trans('clientlist.table.email') }}
                                    </th>
                                    <th>
                                        {{ trans('clientlist.table.company_name') }}
                                    </th>
                                    <th class="text-center">
                                        {{ trans('clientlist.table.completed_session') }}
                                    </th>
                                    <th class="text-center">
                                        {{ trans('clientlist.table.upcoming') }}
                                    </th>
                                    <th class="no-sort th-btn-2">
                                        {{ trans('clientlist.table.action') }}
                                    </th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.grid strt -->
    </div>
</section>
@endsection
@section('after-scripts')
<!-- DataTables -->
<script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand()) }}">
</script>
<script type="text/javascript">
    var url = {
        datatable: `{{ route('admin.clientlist.get-clients') }}`,
    },
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    },
    role = `{{ $role }}`;
</script>
<script src="{{ mix('js/clientlist/index.js') }}">
</script>
@endsection
