@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.surveycategories.breadcrumb', [
    'mainTitle' => trans('surveycategories.title.index'),
    'breadcrumb' => Breadcrumbs::render('surveycategories.index'),
    'create' => !($surveyCategoryCount >= $surveyCategoryMaxCount)
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
                {{ Form::open(['route' => 'admin.surveycategories.index', 'class' => 'form-horizontal', 'method' => 'get', 'category' => 'form', 'id' => 'surveycategorySearch']) }}
                <div class="search-outer d-md-flex justify-content-between">
                    <div>
                        <div class="form-group">
                            {{ Form::text('categoryName', request()->get('categoryName'), ['class' => 'form-control', 'placeholder' => trans('surveycategories.filter.category'), 'id' => 'categoryName', 'autocomplete' => 'off']) }}
                        </div>
                    </div>
                    <div class="search-actions align-self-start">
                        <button class="me-md-4 filter-apply-btn" type="submit">
                            {{ trans('buttons.general.apply') }}
                        </button>
                        <a class="filter-cancel-icon" href="{{ route('admin.surveycategories.index') }}">
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
                        <table class="table custom-table" id="surveyCategoryManagment">
                            <thead>
                                <tr>
                                    <th class="d-none">
                                        {{ trans('surveycategories.table.updated_at') }}
                                    </th>
                                    <th>
                                    </th>
                                    <th>
                                        {{ trans('surveycategories.table.category_name') }}
                                    </th>
                                    <th>
                                        {{ trans('surveycategories.table.total_sub_cat') }}
                                    </th>
                                    <th class="th-btn-3 no-sort">
                                        {{ trans('surveycategories.table.actions') }}
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
<div category="dialog" class="modal fade" data-id="0" id="delete-model-box" tabindex="-1">
    <div category="document" class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ trans('surveycategories.modal.delete.title') }}
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    {{ trans('surveycategories.modal.delete.message') }}
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
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}" type="text/javascript">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}" type="text/javascript">
</script>
<script type="text/javascript">
    var message = {!! json_encode(trans('surveycategories.messages')) !!},
        url = {
            datatable: `{{ route('admin.surveycategories.getCategories') }}`,
            delete: `{{ route('admin.surveycategories.delete', ':id') }}`
        },
        pagination = {
            value: {{ $pagination }},
            previous: `{!! trans('buttons.pagination.previous') !!}`,
            next: `{!! trans('buttons.pagination.next') !!}`,
        };
</script>
<script src="{{ mix('js/surveycategories/index.js') }}" type="text/javascript">
</script>
@endsection
