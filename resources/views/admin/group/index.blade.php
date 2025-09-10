@extends('layouts.app')

@section('after-styles')
<!-- DataTables -->
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
<style type="text/css">
.hidden {
    display: none !important;
}
</style>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.group.breadcrumb',[
    'appPageTitle' => trans('group.title.index_title'),
    'breadcrumb' => 'group.index',
    'create'     => true,
    'back'       => false,
    'edit'       => false,
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="nav-tabs-wrap">
            <ul class="nav nav-tabs tabs-line-style" id="groupsTab" role="tablist">
                <li class="nav-item">
                    <a aria-controls="mainGroups" aria-selected="true" class="nav-link active" data-bs-toggle="tab" href="#mainGroups" id="mainGroups-tab" role="tab">
                        {{ trans('group.title.main_groups') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a aria-controls="otherGroups" aria-selected="false" class="nav-link" data-bs-toggle="tab" href="#otherGroups" id="otherGroups-tab" role="tab">
                        {{ trans('group.title.other_groups') }}
                    </a>
                </li>
            </ul>
            <div class="tab-content" id="groupsTabContent">
                <div aria-labelledby="mainGroups-tab" class="tab-pane fade show active" id="mainGroups" role="tabpanel">
                    <!-- Card -->
                    <div class="card search-card">
                        <div class="card-body pb-0">
                            <h4 class="d-md-none">
                                {{ trans('group.title.filter') }}
                            </h4>
                            <form>
                                <div class="search-outer d-md-flex justify-content-between">
                                    <div>
                                        <div class="form-group">
                                            {{ Form::text('groupName', request()->get('groupName'), ['class' => 'form-control', 'placeholder' => trans('group.filter.search_by_name'), 'id' => 'groupName', 'autocomplete' => 'off']) }}
                                        </div>
                                        <div class="form-group">
                                            {{ Form::select('sub_category', $subCategories, request()->get('sub_category'), ['class' => 'form-control select2','id'=>'sub_category',"style"=>"width: 100%;", 'placeholder' => '', 'data-placeholder'=> trans('group.filter.select_sub_category'), 'autocomplete' => 'off'] ) }}
                                        </div>
                                        <div class="form-group">
                                            {{ Form::select('group_type', $groupTypes, request()->get('group_type'), ['class' => 'form-control select2','id'=>'group_type',"style"=>"width: 100%;", 'placeholder' => '', 'data-placeholder'=>trans('group.filter.select_group_type'), 'autocomplete' => 'off'] ) }}
                                        </div>
                                    </div>
                                    <div class="search-actions align-self-start">
                                        <button class="me-md-4 filter-apply-btn" id="mainGroupsSearch" type="button">
                                            {{trans('buttons.general.apply')}}
                                        </button>
                                        <a class="filter-cancel-icon" id="resetSearch" href="javascript:;">
                                            <i class="far fa-times">
                                            </i>
                                            <span class="d-md-none ms-2 ms-md-0">
                                                {{trans('buttons.general.cancel')}}
                                            </span>
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="card-table-outer" id="mainGroupTable-wrap">
                                <div class="table-responsive">
                                    <table class="table custom-table" id="mainGroupsManagment">
                                        <thead>
                                            <tr>
                                                <th style="display: none">
                                                    {{trans('group.table.updated_at')}}
                                                </th>
                                                <th class="no-sort th-btn-4">
                                                    {{trans('group.table.logo')}}
                                                </th>
                                                <th>
                                                    {{trans('group.table.subcategory')}}
                                                </th>
                                                <th>
                                                    {{trans('group.table.groupname')}}
                                                </th>
                                                <th>
                                                    {{trans('group.table.created_by')}}
                                                </th>
                                                <th>
                                                    {{trans('group.table.members')}}
                                                </th>
                                                <th>
                                                    {{trans('group.table.type')}}
                                                </th>
                                                <th>
                                                    {{trans('group.table.action')}}
                                                </th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div aria-labelledby="otherGroups-tab" class="tab-pane fade" id="otherGroups" role="tabpanel">
                    <div class="card search-card">
                        <div class="card-body pb-0">
                            <h4 class="d-md-none">
                                {{trans('group.title.filter')}}
                            </h4>
                            <form>
                                <div class="search-outer d-md-flex justify-content-between">
                                    <div>
                                        <div class="form-group">
                                            {{ Form::text('groupName2', request()->get('groupName2'), ['class' => 'form-control', 'placeholder' => trans('group.filter.search_by_name'), 'id' => 'groupName2', 'autocomplete' => 'off']) }}
                                        </div>
                                        <div class="form-group">
                                            {{ Form::select('sub_category2', $otherGroupSubCategories, request()->get('sub_category2'), ['class' => 'form-control select2','id'=>'sub_category2',"style"=>"width: 100%;", 'placeholder' => '', 'data-placeholder'=>trans('group.filter.select_sub_category'), 'autocomplete' => 'off'] ) }}
                                        </div>
                                        <div class="form-group">
                                            {{ Form::select('is_archived', [1 => 'Yes', 0 => 'No'], request()->get('is_archived'), ['class' => 'form-control select2','id'=>'is_archived',"style"=>"width: 100%;", 'placeholder' => '', 'data-placeholder'=>trans('group.filter.is_archived'), 'autocomplete' => 'off'] ) }}
                                        </div>
                                    </div>
                                    <div class="search-actions align-self-start">
                                        <button class="me-md-4 filter-apply-btn" id="otherGroupsSearch" type="button">
                                            {{trans('buttons.general.apply')}}
                                        </button>
                                        <a class="filter-cancel-icon" id="resetSearch" href="javascript:;">
                                            <i class="far fa-times">
                                            </i>
                                            <span class="d-md-none ms-2 ms-md-0">
                                                {{trans('buttons.general.reset')}}
                                            </span>
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="card-table-outer" id="otherGroupsTable-wrap">
                                <div class="table-responsive">
                                    <table class="table custom-table" id="otherGroupsManagment">
                                        <thead>
                                            <tr>
                                                <th style="display: none">
                                                    {{trans('group.table.updated_at')}}
                                                </th>
                                                <th class="no-sort th-btn-2">
                                                    {{trans('group.table.logo')}}
                                                </th>
                                                <th>
                                                    {{trans('group.table.subcategory')}}
                                                </th>
                                                <th>
                                                    {{trans('group.table.groupname')}}
                                                </th>
                                                <th>
                                                    {{trans('group.table.members')}}
                                                </th>
                                                <th>
                                                    {{trans('group.table.archived')}}
                                                </th>
                                                <th>
                                                    {{trans('group.table.action')}}
                                                </th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Delete Model Popup -->
@include('admin.group.delete-model')
@endsection
<!-- include datatable css -->
@section('after-scripts')
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}">
</script>
<script type="text/javascript">
    var url = {
        datatable: `{{ route('admin.groups.getGroups') }}`,
        delete: `{{ route('admin.groups.delete', ':id') }}`,
    },
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    },
    message = {
        groupDeleted: `{{ trans('group.modal.group_deleted') }}`,
        groupInUse: `{{ trans('group.modal.group_in_use') }}`,
        unableToDeleteGroup: `{{ trans('group.modal.unable_to_delete_group') }}`,
    };
</script>
<script src="{{ asset('js/group/index.js') }}" type="text/javascript">
</script>
@endsection
