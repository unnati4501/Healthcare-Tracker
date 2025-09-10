@extends('layouts.app')

@section('after-styles')
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.course.breadcrumb', [
    'mainTitle' => trans('masterclass.title.index'),
    'breadcrumb' => Breadcrumbs::render('course.index'),
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
                {{ Form::open(['route' => 'admin.masterclass.index', 'class' => 'form-horizontal', 'method' => 'get', 'role' => 'form', 'id' => 'courseSearch']) }}
                <div class="search-outer d-md-flex justify-content-between">
                    <div>
                        <div class="form-group">
                            {{ Form::text('recordName', request()->get('recordName'), ['class' => 'form-control', 'placeholder' => trans('masterclass.filter.title'), 'id' => 'recordName', 'autocomplete' => 'off']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::text('recordCoach', request()->get('recordCoach'), ['class' => 'form-control', 'placeholder' => trans('masterclass.filter.coach'), 'id' => 'recordCoach', 'autocomplete' => 'off']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::select('recordCategory', $subcategories, request()->get('recordCategory'), ['class' => 'form-control select2', 'id' => 'recordCategory', 'placeholder' => trans('masterclass.filter.category'), 'data-placeholder' => trans('masterclass.filter.category')] ) }}
                        </div>
                        @if($roleGroup == 'zevo')
                        <div class="form-group">
                            {{ Form::select('recordTag', $tags, request()->get('recordTag'), ['class' => 'form-control select2', 'id' => 'recordTag', 'placeholder' => trans('masterclass.filter.tag'), 'data-placeholder' => trans('masterclass.filter.tag')] ) }}
                        </div>
                        @endif
                    </div>
                    <div class="search-actions align-self-start">
                        <button class="me-md-4 filter-apply-btn" type="submit">
                            {{ trans('buttons.general.apply') }}
                        </button>
                        <a class="filter-cancel-icon" href="{{ route('admin.masterclass.index') }}">
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
        <!-- listing -->
        <div class="card">
            <div class="card-body">
                <div class="card-table-outer">
                    <div class="table-responsive">
                        <table class="table custom-table" id="courseManagment">
                            <thead>
                                <tr>
                                    <th class="text-center d-none">
                                        {{ trans('masterclass.table.updated_at') }}
                                    </th>
                                    <th class="no-sort th-btn-4">
                                    </th>
                                    <th>
                                        {{ trans('masterclass.table.title') }}
                                    </th>
                                    <th>
                                        {{ trans('masterclass.table.sub_category_name') }}
                                    </th>
                                    <th>
                                        {{ trans('masterclass.table.author') }}
                                    </th>
                                    <th>
                                        {{ trans('masterclass.table.visible_to_company') }}
                                    </th>
                                    <th>
                                        {{ trans('masterclass.table.category_tag') }}
                                    </th>
                                    <th>
                                        {{ trans('masterclass.table.members') }}
                                    </th>
                                    <th>
                                        {{ trans('masterclass.table.lessons') }}
                                    </th>
                                    <th>
                                        {{ trans('masterclass.table.durations') }}
                                    </th>
                                    <th>
                                        {{ trans('masterclass.table.total_likes') }}
                                    </th>
                                    <th>
                                        {{ trans('masterclass.table.status') }}
                                    </th>
                                    <th class="no-sort th-btn-4">
                                        {{ trans('masterclass.table.actions') }}
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
        <!-- /.listing -->
    </div>
</section>
<!-- /.modals -->
@include('admin.course.index-modals');
<!-- /.modals -->
@endsection
@section('after-scripts')
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}" type="text/javascript">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}" type="text/javascript">
</script>
<script type="text/javascript">
    var url = {
        datatable: `{{ route('admin.masterclass.getCourses') }}`,
        delete: `{{ route('admin.masterclass.delete', ':id') }}`,
        publish: `{{ route('admin.masterclass.publish', ':id') }}`,
    },
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    },
    roleGroup = `{{ $roleGroup }}`,
    messages = {!! json_encode(trans('masterclass.messages')) !!};
</script>
<script src="{{ mix('js/masterclass/index.js') }}" type="text/javascript">
</script>
@endsection
