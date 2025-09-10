@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.roles.breadcrumb', [
  'mainTitle' => trans('roles.title.index'),
  'breadcrumb' => 'role.index',
  'create' => true
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
                {{ Form::open(['route' => 'admin.roles.index', 'class' => 'form-horizontal', 'method' => 'get', 'role' => 'form', 'id' => 'roleSearch']) }}
                <div class="search-outer d-md-flex justify-content-between">
                    <div>
                        <div class="form-group">
                            {{ Form::text('roleName', request()->get('roleName'), ['class' => 'form-control', 'placeholder' => trans('roles.filter.name'), 'id' => 'roleName', 'autocomplete' => 'off']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::select('roleGroup', $roleGroupData, request()->get('roleGroup'), ['class' => 'form-control select2', 'id' => 'roleGroup', 'placeholder' => trans('roles.filter.role'), 'data-placeholder' => trans('roles.filter.role')] ) }}
                        </div>
                    </div>
                    <div class="search-actions align-self-start">
                        <button class="me-md-4 filter-apply-btn" type="submit">
                            {{ trans('buttons.general.apply') }}
                        </button>
                        <a class="filter-cancel-icon" href="{{ route('admin.roles.index') }}">
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
        <!-- grid -->
        <div class="card">
            <div class="card-body">
                <div class="card-table-outer">
                    <div class="table-responsive">
                        <table class="table custom-table" id="roleManagment">
                            <thead>
                                <tr>
                                    <th class="text-center d-none">
                                        {{ trans('roles.table.updated_at') }}
                                    </th>
                                    <th>
                                        {{ trans('roles.table.name') }}
                                    </th>
                                    <th>
                                        {{ trans('roles.table.group') }}
                                    </th>
                                    <th>
                                        {{ trans('roles.table.desc') }}
                                    </th>
                                    <th class="th-btn-2 no-sort">
                                        {{trans('roles.table.actions')}}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.grid -->
    </div>
</section>
<div class="modal fade" data-id="0" id="delete-model-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ trans('roles.modal.delete.title') }}
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    {{ trans('roles.modal.delete.message') }}
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">
                    {{ trans('buttons.general.cancel') }}
                </button>
                <button class="btn btn-primary" id="delete-model-box-confirm" type="button">
                    {{ trans('buttons.general.delete') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
@section('after-scripts')
<script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand()) }}" type="text/javascript">
</script>
<script type="text/javascript">
    var url = {
        datatable: `{{ route('admin.roles.getRoles') }}`,
        delete: `{{ route('admin.roles.delete', ':id') }}`,
    },
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    },
    message = {
        deleted: `{{ trans('roles.messages.deleted') }}`,
        in_user: `{{ trans('roles.messages.in_user') }}`,
        delete_fail: `{{ trans('roles.messages.delete_fail') }}`,
    };
</script>
<script src="{{ asset('js/roles/index.js') }}" type="text/javascript">
</script>
@endsection
