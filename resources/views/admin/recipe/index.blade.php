@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.recipe.breadcrumb', [
  'mainTitle' => trans('recipe.title.index'),
  'breadcrumb' => Breadcrumbs::render('recipe.index'),
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
                {{ Form::open(['route' => 'admin.recipe.index', 'class' => 'form-horizontal', 'method' => 'GET','role' => 'form', 'id' => 'recipeSearch']) }}
                <div class="search-outer d-md-flex justify-content-between">
                    <div>
                        <div class="form-group">
                            {{ Form::text('recipename', request()->get('recipename'), ['class' => 'form-control', 'placeholder' => trans('recipe.filter.name'), 'id' => 'recipename', 'autocomplete' => 'off']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::text('username', request()->get('username'), ['class' => 'form-control', 'placeholder' => trans('recipe.filter.user'), 'id' => 'username', 'autocomplete' => 'off']) }}
                        </div>
                        <div class="form-group">
                            @if($statusColVisibility == true)
                        {{ Form::select('status', [ 0 => trans('recipe.filter.pending'), 1 => trans('recipe.filter.approved')], request()->get('status'), ['class' => 'form-control select2', 'id' => 'status', 'placeholder' => trans('recipe.filter.status'), 'data-placeholder' => trans('recipe.filter.status'), 'data-allow-clear' => 'true']) }}
                        @elseif($companyColVisibility == true)
                        {{ Form::select('company', ($company ?? []), request()->get('company'), ['class' => 'form-control select2', 'id' => 'company', 'placeholder' => trans('recipe.filter.company'), 'data-placeholder' => trans('recipe.filter.company'), 'data-allow-clear' => 'true']) }}
                        @endif
                        </div>
                        @if($roleGroup == 'zevo')
                        <div class="form-group">
                            {{ Form::select('tag', $tags, (request()->get('tag') ?? null), ['class' => 'form-control select2', 'id' => 'tag', 'placeholder' => trans('recipe.filter.tag'), 'data-placeholder' => trans('recipe.filter.tag'), 'data-allow-clear' => 'true'] ) }}
                        </div>
                        @endif
                        <div class="form-group">
                            {{ Form::select('type', $recipeTypes, (request()->get('type') ?? null), ['class' => 'form-control select2', 'id' => 'type', 'placeholder' => trans('recipe.filter.type'), 'data-placeholder' => trans('recipe.filter.type'), 'data-allow-clear' => 'true'] ) }}
                        </div>
                    </div>
                    <div class="search-actions align-self-start">
                        <button class="me-md-4 filter-apply-btn" type="submit">
                            {{ trans('buttons.general.apply') }}
                        </button>
                        <a class="filter-cancel-icon" href="{{ route('admin.recipe.index') }}">
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
            <div class="card-header detailed-header small-gap">
                @if($statusColVisibility == true)
                <div class="d-flex flex-wrap">
                    <div>
                        <span>
                            {{ trans('recipe.filter.pending_count') }}
                            <strong class="badge badge-warning text-white p-2 align-middle ms-2 border-rounded" id="count_unapproved">
                                {{ $unapproved }}
                            </strong>
                        </span>
                    </div>
                    <div>
                        <span>
                            {{ trans('recipe.filter.approved_count') }}
                            <strong class="badge badge-success text-white p-2 align-middle ms-2 border-rounded" id="count_approved">
                                {{ $approved }}
                            </strong>
                        </span>
                    </div>
                </div>
                @endif
            </div>
            <div class="card-body">
                <div class="card-table-outer">
                    <div class="table-responsive">
                        <table class="table custom-table" id="receipeManagment">
                            <thead>
                                <tr>
                                    <th class="d-none">
                                        {{ trans('recipe.table.updated_at') }}
                                    </th>
                                    <th class="th-btn-2 text-center">
                                    </th>
                                    <th>
                                        {{ trans('recipe.table.recipe') }}
                                    </th>
                                    @if($companyColVisibility == true)
                                    <th>
                                        {{ trans('recipe.table.company') }}
                                    </th>
                                    <th>
                                        {{ trans('recipe.table.visible_to_company') }}
                                    </th>
                                    @endif
                                    <th>
                                        {{ trans('recipe.table.username') }}
                                    </th>
                                    <th>
                                        {{ trans('recipe.table.tag') }}
                                    </th>
                                    <th>
                                        {{ trans('recipe.table.type') }}
                                    </th>
                                    <th>
                                        {{ trans('recipe.table.created_at') }}
                                    </th>
                                    @if($statusColVisibility == true)
                                    <th>
                                        {{ trans('recipe.table.status') }}
                                    </th>
                                    @endif
                                    <th class="th-btn-3 no-sort">
                                        {{ trans('recipe.table.actions') }}
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
<!-- /.modals -->
@include('admin.recipe.index-modals')
<!-- /.modals -->
@endsection
@section('after-scripts')
<script src="{{asset('assets/plugins/moment/moment.min.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{asset('assets/plugins/moment/moment-timezone-with-data-10-year-range.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand()) }}" type="text/javascript">
</script>
<script type="text/javascript">
    var timezone = `{{ $timezone }}`,
        date_format = `{{ $date_format }}`,
        url = {
            datatable: `{{ route('admin.recipe.getRecipes') }}`,
            delete: `{{ route('admin.recipe.delete', ':id') }}`,
            approve: `{{ route('admin.recipe.approve', ':id') }}`,
        },
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    },
    roleGroup = `{{ $roleGroup }}`,
    messages = {!! json_encode(trans('recipe.messages')) !!};
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $('#receipeManagment').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: url.datatable,
                data: {
                    recipename: $('#recipename').val(),
                    username: $('#username').val(),
                    sub_category: $('#sub_category').val(),
                    status: $('#status').val(),
                    company: $('#company').val(),
                    tag: $('#tag').val(),
                    type: $('#type').val(),
                    getQueryString: window.location.search
                },
            },
            columns: [{
                data: 'updated_at',
                name: 'updated_at',
                visible: false
            }, {
                data: 'logo',
                searchable: false,
                sortable: false,
                className: 'text-center',
                render: function(data, type) {
                    return `<div class="table-img table-img-l"><img src="${data}"/></div>`;
                }
            }, {
                data: 'title',
                name: 'title'
            }
            @if($companyColVisibility == true)
            , {
                data: 'company_name',
                name: 'company_name'
            }, {
                data: 'companiesName',
                name: 'companiesName',
                sortable: false
            }
            @endif
            , {
                data: 'username',
                name: 'username'
            }, {
                data: 'category_tag',
                name: 'category_tag',
                visible: (roleGroup == 'zevo'),
            }, {
                data: 'recipe_type',
                name: 'recipe_type',
            }, {
                data: 'created_at',
                name: 'created_at',
                searchable: false,
                render: function(data) {
                    return moment.utc(data).tz(timezone).format(date_format);
                }
            }
            @if($statusColVisibility == true)
            , {
                data: 'status',
                name: 'status',
                searchable: false,
                sortable: false
            }
            @endif
            , {
                data: 'actions',
                name: 'actions',
                searchable: false,
                sortable: false,
            }],
            paging: true,
            pageLength: pagination.value,
            dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
            lengthChange: false,
            searching: false,
            ordering: true,
            @if($statusColVisibility == true)
            order: [],
            @else
            order: [
                [0, 'desc']
            ],
            @endif
            info: true,
            autoWidth: false,
            columnDefs: [{
                targets: 'no-sort',
                orderable: false,
            }],
            language: {
                paginate: {
                    previous: pagination.previous,
                    next: pagination.next,
                }
            },
        });
    });
</script>
<script src="{{ mix('js/recipe/index.js') }}" type="text/javascript">
</script>
@endsection
