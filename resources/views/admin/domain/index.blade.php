@extends('layouts.app')

@section('after-styles')
<!-- DataTables -->
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.domain.breadcrumb', [
    'appPageTitle' => trans('domain.title.index_title'),
    'breadcrumb' => 'domains.index',
    'create' => true
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card search-card">
            <div class="card-body pb-0">
                <h4 class="d-md-none">
                    {{ trans('domain.title.search') }}
                </h4>
                {{ Form::open(['route' => 'admin.domains.index', 'class' => 'form-horizontal', 'method'=>'get','role' => 'form', 'id'=>'domainSearch']) }}
                <div class="search-outer d-md-flex justify-content-between">
                    <div>
                        <div class="form-group">
                            {{ Form::text('domainName', request()->get('domainName'), ['class' => 'form-control', 'placeholder' => trans('domain.filter.search_by_domain'), 'id' => 'domainName', 'autocomplete' => 'off']) }}
                        </div>
                    </div>
                    <div class="search-actions align-self-start">
                        <button type="submit" class="me-md-4 filter-apply-btn">{{trans('buttons.general.apply')}}</button>
                        <a class="filter-cancel-icon" href="{{ route('admin.domains.index') }}">
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
        <div class="card">
            <!-- /.card-header -->
            <div class="card-body">
                <div class="card-table-outer">
                    <div class="table-responsive">
                        <table id="domainManagment" class="table custom-table">
                            <thead>
                                <tr>
                                    <th style="display: none">
                                        {{ trans('domain.table.updated_at') }}
                                    </th>
                                    <th>{{trans('domain.table.domain')}}</th>
                                    <th class="th-btn-3 no-sort">
                                        {{trans('domain.table.action')}}
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
    </div>
</section>
<!-- Delete model popup -->
@include('admin.domain.delete-modal')
@endsection
<!-- include datatable css -->
@section('after-scripts')
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}">
</script>
<script type="text/javascript">
    var url = {
        datatable: `{{ route('admin.domains.getDomains') }}`,
        delete: `{{route('admin.domains.delete','/')}}`,
    },
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    },
    message = {
        domain_deleted: `{{ trans('domain.message.domain_deleted') }}`,
        domain_in_use: `{{ trans('domain.message.domain_in_use') }}`,
        unable_to_delete_domain: `{{ trans('domain.message.unable_to_delete_domain') }}`,
        select_group: `{{ trans('domain.filter.select_group') }}`,
    };
</script>
<script src="{{ asset('js/domain/index.js') }}" type="text/javascript">
</script>
@endsection