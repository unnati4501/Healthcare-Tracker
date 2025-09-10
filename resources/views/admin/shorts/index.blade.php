@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
<style type="text/css">
    .model-close-custom { position: absolute; top: -10px; right: -13px; padding: 2px 9px; background: #00a7d2; z-index: 2; border-radius: 50%; height: 30px; width: 30px; color: white; font-size: 18px; cursor: pointer; }
</style>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.shorts.breadcrumb', [
    'mainTitle' => trans('shorts.title.index'),
    'breadcrumb' => Breadcrumbs::render('shorts.index'),
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
                {{ Form::open(['route' => 'admin.shorts.index', 'class' => 'form-horizontal', 'method' => 'get', 'role' => 'form', 'id' => 'shortsSearch']) }}
                <div class="search-outer d-md-flex justify-content-between">
                    <div>
                        <div class="form-group">
                            {{ Form::text('shortsName', request()->get('shortsName'), ['class' => 'form-control', 'placeholder' => trans('shorts.filter.name'), 'id' => 'shortsName', 'autocomplete' => 'off']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::select('author', $author, request()->get('author'), ['class' => 'form-control select2','id' => 'author', 'placeholder' => trans('shorts.filter.author'), 'data-placeholder' => trans('shorts.filter.author')] ) }}
                        </div>
                        <div class="form-group">
                            {{ Form::select('subcategory', $shortsSubCategories, request()->get('subcategory'), ['class' => 'form-control select2','id' => 'subcategory', 'placeholder' => trans('shorts.filter.category'), 'data-placeholder' => trans('shorts.filter.category')] ) }}
                        </div>
                        @if($roleGroup == 'zevo')
                        <div class="form-group">
                            {{ Form::select('tag', $tags, (request()->get('tag') ?? null), ['class' => 'form-control select2', 'id' => 'tag', 'placeholder' => trans('shorts.filter.tag'), 'data-placeholder' => trans('shorts.filter.tag'), 'data-allow-clear' => 'true'] ) }}
                        </div>
                        @endif
                    </div>
                    <div class="search-actions align-self-start">
                        <button class="me-md-4 filter-apply-btn" type="submit">
                            {{ trans('buttons.general.apply') }}
                        </button>
                        <a class="filter-cancel-icon" href="{{ route('admin.shorts.index') }}">
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
                        <table class="table custom-table" id="shortsManagment">
                            <thead>
                                <tr>
                                    <th class="d-none">
                                        {{ trans('shorts.table.updated_at') }}
                                    </th>
                                    <th class="sorting_1 no-sort th-btn-4">
                                        {{ trans('shorts.table.logo') }}
                                    </th>
                                    <th>
                                        {{ trans('shorts.table.title') }}
                                    </th>
                                    <th>
                                        {{ trans('shorts.table.visible_to_company') }}
                                    </th>
                                    <th>
                                        {{ trans('shorts.table.duration') }}
                                    </th>
                                    <th>
                                        {{ trans('shorts.table.subcategory_name') }}
                                    </th>
                                    <th>
                                        {{ trans('shorts.table.tag') }}
                                    </th>
                                    <th>
                                        {{ trans('shorts.table.author') }}
                                    </th>
                                    <th>
                                        {{ trans('shorts.table.created_at') }}
                                    </th>
                                    <th>
                                        {{ trans('shorts.table.total_likes') }}
                                    </th>
                                    <th>
                                        {{ trans('shorts.table.view_count') }}
                                    </th>
                                    <th class="th-btn-2 no-sort">
                                        {{ trans('shorts.table.actions') }}
                                    </th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.gridrid -->
    </div>
</section>
<!-- .modals -->
@include('admin.shorts.index-modals')
<!-- /.modals -->
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
<script type="text/javascript">
    var timezone = `{{ $timezone }}`,
        date_format = `{{ $date_format }}`,
        url = {
            datatable: `{{ route('admin.shorts.getshorts') }}`,
            delete: `{{ route('admin.shorts.delete', ':id') }}`
        },
        pagination = {
            value: {{ $pagination }},
            previous: `{!! trans('buttons.pagination.previous') !!}`,
            next: `{!! trans('buttons.pagination.next') !!}`,
        },
        roleGroup = `{{ $roleGroup }}`,
        message = {!! json_encode(trans('shorts.message')) !!};
</script>
<script src="{{ mix('js/shorts/index.js') }}" type="text/javascript">
</script>
@endsection
