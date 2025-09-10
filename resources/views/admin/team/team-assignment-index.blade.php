@extends('layouts.app')
@section('after-styles')
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.team.breadcrumb', [
    'appPageTitle' => trans('team-assignment.title.index_title'),
    'breadcrumb' => 'team.teamassignment',
    'create' => false,
    'setLimit' => false,
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card form-card">
            {{ Form::open(['route' => 'admin.team-assignment.update', 'class' => 'form-horizontal', 'method'=>'post','role' => 'form', 'id'=>'teamAssignmentFrm']) }}
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="department-box department-box-left p-0">
                            <div class="department-box-select department-box-select-left mb-0 p-2">
                                {{ trans('team-assignment.title.select_from') }}
                            </div>
                            <div class="department-team-select-area p-3 draggable-border">
                                <div class="form-group">
                                    {{ Form::label('fromdepartment', trans('team-assignment.form.labels.select_department')) }}
                                    {{ Form::select('fromdepartment', $department, request()->query('fromdepartment'), ['class' => 'form-control select2', 'id'=>'fromdepartment', 'style'=>'width: 100%;', 'autocomplete' => 'off', 'placeholder' => trans('team-assignment.form.placeholder.select_department'), 'data-placeholder' => trans('team-assignment.form.placeholder.select_department'), 'data-allow-clear' => 'true']) }}
                                </div>
                                <div class="form-group mb-0">
                                    {{ Form::label('fromteam', trans('team-assignment.form.labels.select_team')) }}
                                    {{ Form::select('fromteam', [], null, ['class' => 'form-control select2', 'id'=>'fromteam', 'style'=>'width: 100%;', 'autocomplete' => 'off', 'placeholder' => trans('team-assignment.form.placeholder.select_team'), 'data-placeholder' => trans('team-assignment.form.placeholder.select_team'), 'data-allow-clear' => 'true']) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="department-box department-box-right p-0">
                            <div class="department-box-select department-box-select-right mb-0 p-2">
                                {{ trans('team-assignment.title.select_to') }}
                            </div>
                            <div class="department-team-select-area p-3 draggable-border">
                                <div class="form-group">
                                    {{ Form::label('todepartment', trans('team-assignment.form.labels.select_department')) }}
                                    {{ Form::select('todepartment', $department, request()->query('todepartment'), ['class' => 'form-control select2', 'id'=>'todepartment', 'style'=>'width: 100%;', 'autocomplete' => 'off', 'placeholder' => trans('team-assignment.form.placeholder.select_department'), 'data-placeholder' => trans('team-assignment.form.placeholder.select_department'), 'data-allow-clear' => 'true']) }}
                                </div>
                                <div class="form-group mb-0">
                                    {{ Form::label('toteam', trans('labels.team-assignment.select_team')) }}
                                    {{ Form::select('toteam', [], null, ['class' => 'form-control select2', 'id'=>'toteam', 'style'=>'width: 100%;', 'autocomplete' => 'off', 'placeholder' => trans('team-assignment.form.placeholder.select_team'), 'data-placeholder' => trans('team-assignment.form.placeholder.select_team'), 'data-allow-clear' => 'true']) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="">
                    <div class="row mt-4 d-none" id="teamAssignment">
                        <div class="col-md-6 draggable-wrapper" id="fromTeamMembersList">
                            <div class="draggable-outer">
                                <p class="name p-2 bg-light mb-0 ps-3 pe-3">
                                </p>
                                <div class="">
                                    {{ Form::text('fromsearch', null, ['class' => 'form-control search-member mt-2 mb-2', 'data-control' => 'fromTeamMembersList', 'placeholder' => trans('team-assignment.filter.search_by_name')]) }}
                                    <ul class="draggable draggable-inner-height">
                                    </ul>
                                    <label class="count-wrapper mb-0 mt-3 d-block pt-2 border-top">
                                        {{ trans('team-assignment.filter.count') }} -
                                        <span class="count" id="fromCount">
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 draggable-wrapper" id="toTeamMembersList">
                            <div class="draggable-outer">
                                <p class="name p-2 bg-light mb-0 ps-3 pe-3">
                                </p>
                                <div class="">
                                    {{ Form::text('tosearch', null, ['class' => 'form-control search-member mt-2 mb-2', 'data-control' => 'toTeamMembersList', 'placeholder' => trans('team-assignment.filter.search_by_name')]) }}
                                    <ul class="draggable draggable-inner-height">
                                    </ul>
                                    <label class="count-wrapper mb-0 mt-3 d-block pt-2 border-top">
                                        {{ trans('team-assignment.filter.count') }} -
                                        <span class="count" id="toCount">
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    {{ Form::hidden('fromteammembers', null, ['id' => 'fromteammembers']) }}
	                {{ Form::hidden('toteammembers', null, ['id' => 'toteammembers']) }}
	                {{ Form::hidden('limit', 0, ['id' => 'limit']) }}
                    <a class="btn btn-outline-primary" href="{{ route('admin.team-assignment.index') }}">
                        {{trans('buttons.general.reset')}}
                    </a>
                    <button class="btn btn-primary" type="submit">
                        {{ trans('buttons.general.update') }}
                    </button>
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</section>
@endsection

@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\UpdateTeamAssignmentRequest', '#teamAssignmentFrm') !!}
<script src="{{ asset('assets/plugins/sortable/jquery-sortable-min.js') }}" type="text/javascript">
</script>
<script src="{{ asset('js/external/jquery.form.min.js?var='.rand()) }}">
</script>
<script type="text/javascript">
var Qsfromdepartment = {{ request()->query('fromdepartment', 0) }},
Qstodepartment = {{ request()->query('todepartment', 0) }},
Qsfromteam = {{ request()->query('fromteam', 0) }},
Qstoteam = {{ request()->query('toteam', 0) }},
teamAssignment,
teamAssignmentSettings = {
    dataArray: [],
    itemName: "user",
    valueName: "value"
},
urls = {
    getTeams: '{{ route("admin.ajax.departmentTeams", ":id") }}',
    getTeamMembers: '{{ route("admin.team-assignment.getAssignmentTeamMembers", ":ids") }}',
},
message = {
	something_wrong_try_again: `{{ trans('team-assignment.message.something_wrong_try_again') }}`,
	select_atleast_one_member: `{{ trans('team-assignment.message.select_atleast_one_member') }}`,
	loading_team_members: `{{ trans('team-assignment.message.loading_team_members') }}`,
	failed_to_load_team_members: `{{ trans('team-assignment.message.failed_to_load_team_members') }}`,
	failed_to_load_team: `{{ trans('team-assignment.message.failed_to_load_team') }}`,
	team_reach_team_limit: `{{ trans('team-assignment.message.team_reach_team_limit') }}`,
};
</script>
<script src="{{ asset('js/team/team-assignment.js') }}" type="text/javascript">
</script>
@endsection
