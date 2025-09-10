@extends('layouts.app')

@section('after-styles')
<!-- DataTables -->
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.cronofy.sessionlist.breadcrumb', [
  'mainTitle' => trans('calendly.title.manage'),
  'breadcrumb' => 'cronofy.sessionlist.index',
  'book' => (access()->allow('create-sessions')) ? true : false
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        @if($role->slug == 'wellbeing_team_lead' || $role->slug == 'wellbeing_specialist')
        <div class="nav-tabs-wrap">
            <ul class="nav nav-tabs tabs-line-style" id="myTab" role=" tablist">
            <li class="nav-item">
                <a aria-controls="Single" class="nav-link active" aria-selected="true" data-for="single" href="{{ request()->fullUrlWithQuery(['tab' => 'single']) }}" id="tab-single-session" role="tab">
                    {{ trans('Cronofy.session_list.title.single_session') }}
                </a>
            </li>
            <li class="nav-item">
                <a aria-controls="Group" class="nav-link" aria-selected="false" data-for="group" href="{{ request()->fullUrlWithQuery(['tab' => 'group', 'user' => '']) }}" id="tab-group-session" role="tab">
                    {{ trans('Cronofy.session_list.title.group') }}
                </a>
            </li>
            </ul>
        </div>
        @endif
        <div class="card search-card">
            <div class="card-body pb-0">
                {{ Form::open(['route' => 'admin.cronofy.sessions.index', 'class' => 'form-horizontal', 'method'=>'get','role' => 'form']) }}
                <div class="search-outer d-md-flex justify-content-between">
                    <div>
                        @if($role->slug == 'wellbeing_specialist' || $role->slug == 'wellbeing_team_lead' || $role->slug == 'super_admin' || ($role->group == 'reseller' && is_null($company->parent_id)))
                        <div class="form-group">
                            {{ Form::select('company', $companies, request()->get('company'), ['class' => 'form-control select2', 'id' => 'company', 'placeholder' => "", 'data-placeholder' => trans('Cronofy.session_list.filters.company'), 'data-allow-clear' => 'true']) }}
                        </div>
                        @endif
                        @if($role->group == 'company' || $role->slug == 'super_admin' || $role->group == 'reseller' || $role->slug == 'wellbeing_team_lead')
                        <div class="form-group">
                            {{ Form::select('ws', $getWellbeingSpecialist, request()->get('ws'), ['class' => 'form-control select2', 'id' => 'ws', 'placeholder' => "", 'data-placeholder' => trans('Cronofy.session_list.filters.wellbeing_sp'), 'data-allow-clear' => 'true']) }}
                        </div>
                        @endif
                        <div class="form-group">
                           {{ Form::select('service', $service, request()->get('service'), ['class' => 'form-control select2', 'id' => 'service', 'placeholder' => "", 'data-placeholder' => trans('Cronofy.session_list.filters.service'), 'data-allow-clear' => 'true']) }}
                        </div>
                        <div class="form-group subcategories">
                            {{ Form::select('sub_category', $subcategories, request()->get('sub_category'), ['class' => 'form-control select2', 'id' => 'sub_category', 'placeholder' => "", 'data-placeholder' => trans('Cronofy.session_list.filters.sub_category'), 'data-allow-clear' => 'true',  ((!empty(request()->get('service'))) ? "" : "disabled")]) }}
                        </div>
                        @if($role->slug == 'wellbeing_team_lead' || $role->slug == 'wellbeing_specialist')
                        <div class="form-group usersearch">
                            {{ Form::text('user', request()->get('user'), ['class' => 'form-control', 'placeholder' => trans('Cronofy.session_list.filters.client_name_email'), 'id' => 'user', 'autocomplete' => 'off']) }}
                        </div>
                        <input type="hidden" id="tab" name="tab" value="single">
                        @else
                        <input type="hidden" id="tab" name="tab" value="none">
                        @endif
                        {{-- <div class="form-group">
                            {{ Form::select('duration', $duration, request()->get('duration'), ['class' => 'form-control select2', 'id' => 'duration', 'placeholder' => "", 'data-placeholder' => trans('Cronofy.session_list.filters.time'), 'data-allow-clear' => 'true']) }}
                        </div> --}}
                        <div class="form-group">
                            {{ Form::select('status', $status, request()->get('status'), ['class' => 'form-control select2', 'id' => 'status', 'placeholder' => "", 'data-placeholder' => trans('Cronofy.session_list.filters.status'), 'data-allow-clear' => 'true']) }}
                        </div>
                    </div>
                    <div class="search-actions align-self-start">
                        <button class="me-md-4 filter-apply-btn" type="submit">
                            {{ trans('buttons.general.apply') }}
                        </button>
                        @php 
                        if(!empty(request()->get('tab')) && request()->get('tab') == 'group'){
                            $href = route('admin.cronofy.sessions.index')."?tab=".request()->query('tab') ?? 'single';
                        }else{
                            $href = route('admin.cronofy.sessions.index');
                        }
                        @endphp
                        <a class="filter-cancel-icon" href="{{ $href }}">
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
                <div class="card-table-outer" id="sessionManagement-wrap">
                    @if($role->slug != 'wellbeing_team_lead')    
                    <div class="table-responsive">
                        <table class="table custom-table" id="sessionManagement">
                            <thead>
                                <tr>
                                    <th>
                                        {{ trans('Cronofy.session_list.table.user') }}
                                    </th>
                                    <th>
                                        {{ trans('Cronofy.session_list.table.client_email') }}
                                    </th>
                                    <th>
                                        {{ trans('Cronofy.session_list.table.client_timezone') }}
                                    </th>
                                    <th style="display: {{ ($role->group != 'company' || $role->slug == 'super_admin' || $role->slug == 'wellbeing_specialist' || $role->slug == 'wellbeing_team_lead') ? 'table-cell' : 'none' }};">
                                        {{ trans('Cronofy.session_list.table.company') }}
                                    </th>
                                    <th>
                                        {{ trans('Cronofy.session_list.table.service') }}
                                    </th>
                                    <th>
                                        {{ trans('Cronofy.session_list.table.sub_category') }}
                                    </th>
                                    <th class="text-center">
                                        {{ trans('Cronofy.session_list.table.participants') }}
                                    </th>
                                    <th style="display: {{ ($role->group == 'company' || $role->slug == 'super_admin' || $role->slug == 'wellbeing_team_lead' || $role->group == 'reseller') ? 'table-cell' : 'none' }};">
                                        {{ trans('Cronofy.session_list.table.wellbeing_specialist') }}
                                    </th>
                                    <th>
                                        {{ trans('Cronofy.session_list.table.datetime') }}
                                    </th>
                                    <th>
                                        {{ trans('Cronofy.session_list.table.status') }}
                                    </th>
                                    <th class="text-center th-btn-3 no-sort">
                                        {{ trans('Cronofy.session_list.table.action') }}
                                    </th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    @else        
                    <div class="table-responsive">
                        <table class="table custom-table d-none" id="sessionManagementWbtl">
                            <thead>
                                <tr>
                                    <th>
                                        {{ trans('Cronofy.session_list.table.user') }}
                                    </th>
                                    <th>
                                        {{ trans('Cronofy.session_list.table.client_email') }}
                                    </th>
                                    <th>
                                        {{ trans('Cronofy.session_list.table.client_timezone') }}
                                    </th>
                                    <th>
                                        {{ trans('Cronofy.session_list.table.company') }}
                                    </th>
                                    <th>
                                        {{ trans('Cronofy.session_list.table.wellbeing_specialist') }}
                                    </th>
                                    <th>
                                        {{ trans('Cronofy.session_list.table.service') }}
                                    </th>
                                    <th>
                                        {{ trans('Cronofy.session_list.table.sub_category') }}
                                    </th>
                                    
                                    <th class="text-center">
                                        {{ trans('Cronofy.session_list.table.participants') }}
                                    </th>
                                    <th>
                                        {{ trans('Cronofy.session_list.table.datetime') }}
                                    </th>
                                    <th>
                                        {{ trans('Cronofy.session_list.table.status') }}
                                    </th>
                                    <th class="text-center th-btn-3 no-sort">
                                        {{ trans('Cronofy.session_list.table.action') }}
                                    </th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
<!-- partipate model Popup -->
@include('admin.cronofy.sessionlist.visibleparticipate-model')
<!-- One to One and Group Session Model popup -->
@include('admin.cronofy.sessionlist.sessiontype-model')
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
        datatable: `{{ route('admin.cronofy.sessions.get-sessions') }}`,
        complete: `{{route('admin.cronofy.sessions.complete','/')}}`,
    },
    ajaxUrl = {
        getSubCategories: '{{ route("admin.cronofy.sessions.get-sub-categories", ":id") }}',
        getWSUserList: '{{ route("admin.cronofy.sessions.get-ws-users-list", ":id") }}',
    },
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    },
    companyVisibility = `{{ $company_col_visibility }}`, 
    roleGroup = `{{ $role->group }}`,
    roleSlug = `{{ $role->slug }}`,
    isParentCompany = `{{ $role->group == 'reseller' ? $company->parent_id : null}}`,
    message = {
        completed           : `{{ trans('Cronofy.session_list.messages.completed') }}`,
        somethingWentWrong  : `{{ trans('Cronofy.session_list.messages.something_wrong_try_again') }}`,
        noDataExists        : `{{ trans('Cronofy.session_list.messages.no_data_exists') }}`,
    };
</script>
<script src="{{ mix('js/cronofy/sessionlist/index.js') }}">
</script>
@endsection
