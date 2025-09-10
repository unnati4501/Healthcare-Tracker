@extends('layouts.app')

@section('after-styles')
<!-- DataTables -->
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.personalChallenge.breadcrumb', [
  'mainTitle' => $mailTitle,
  'breadcrumb' => 'personalChallenges.index',
  'create' => true
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card search-card">
            <div class="card-body pb-0">
                {{ Form::open(['route' => 'admin.personalChallenges.index', 'class' => 'form-horizontal', 'method'=>'get','role' => 'form']) }}
                <div class="search-outer d-md-flex justify-content-between">
                    <div>
                        <div class="form-group">
                            {{ Form::text('challengeName', request()->get('challengeName'), ['class' => 'form-control', 'placeholder' => trans('personalChallenge.filter.name'), 'id' => 'challengeName', 'autocomplete' => 'off']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::select('challengeType', $challengeTypeData, request()->get('challengeType'), ['class' => 'form-control select2','id'=>'challengeType',"style"=>"width: 100%;", 'placeholder' => '', 'data-placeholder'=> $challenge_type, 'autocomplete' => 'off'] ) }}
                        </div>
                        <div class="form-group" id="subtypeRecords">
                            {{ Form::select('subtype', $challengeSubType, request()->get('subtype'), ['class' => 'form-control select2','id'=>'subtype',"style"=>"width: 100%;", 'placeholder' => '', 'data-placeholder'=> trans('personalChallenge.filter.type'), 'autocomplete' => 'off'] ) }}
                        </div>
                        @if(request()->get('challengeType') != 'habit')
                        <div class="form-group">
                            {{ Form::select('recursive', $recursive, request()->get('recursive'), ['class' => 'form-control select2','id'=>'recursive',"style"=>"width: 100%;", 'placeholder' => '', 'data-placeholder'=> trans('personalChallenge.filter.recursive'), 'autocomplete' => 'off'] ) }}
                        </div>
                        @endif
                    </div>
                    <div class="search-actions align-self-start">
                        <button class="me-md-4 filter-apply-btn" type="submit">
                            {{ trans('buttons.general.apply') }}
                        </button>
                        <a class="filter-cancel-icon" href="{{ route('admin.personalChallenges.index') }}">
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
                <div class="card-table-outer" id="challengeManagment-wrap">
                    <div class="table-responsive">
                        <table class="table custom-table" id="challengeManagment">
                            <thead>
                                <tr>
                                    <th>
                                        {{ trans('personalChallenge.table.updated_at') }}
                                    </th>
                                    <th class="no-sort th-btn-4">
                                        {{ trans('personalChallenge.table.logo') }}
                                    </th>
                                    <th>
                                        @if($planAccess)
                                        {{ trans('personalChallenge.table.name') }}
                                        @else
                                        {{ trans('personalChallenge.table.name_goal') }}
                                        @endif
                                    </th>
                                    <th>
                                        {{ trans('personalChallenge.table.duration') }}
                                    </th>
                                    <th>
                                        {{ trans('personalChallenge.table.created_by') }}
                                    </th>
                                    <th>
                                        @if($planAccess)
                                        {{ trans('personalChallenge.table.challenge_type') }}
                                        @else
                                        {{ trans('personalChallenge.table.goal_type') }}
                                        @endif
                                    </th>
                                    <th>
                                        {{ trans('personalChallenge.table.type') }}
                                    </th>
                                    <th>
                                        {{ trans('personalChallenge.table.joined') }}
                                    </th>
                                    <th class="no-sort th-btn-4">
                                        {{ trans('personalChallenge.table.action') }}
                                    </th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.row (main row) -->
    </div>
</section>
@include('admin.personalChallenge.delete_modal')
@endsection
<!-- include datatable css -->
@section('after-scripts')
<!-- DataTables -->
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}">
</script>
<script type="text/javascript">
    var url = {
        datatable: `{{ route('admin.personalChallenges.getChallenges') }}`,
        delete: `{{ route('admin.personalChallenges.delete','/') }}`,
    },
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    },
    datarecords = {
        personalFitnessChallengeSubType: `<?php echo json_encode($personalFitnessChallengeSubType); ?>`,
        personalRoutineChallengeSubType: `<?php echo json_encode($personalRoutineChallengeSubType); ?>`,
        personalHabitChallengeSubType: `<?php echo json_encode($personalHabitChallengeSubType); ?>`,
    },
    message = {
        deleted: `{{ ($planAccess) ? trans('personalChallenge.messages.deleted') : trans('personalChallenge.messages.deleted_goal') }}`,
        unauthorized: `{{ trans('personalChallenge.messages.unauthorized') }}`,
        somethingWentWrong: `{{ trans('personalChallenge.messages.something_wrong_try_again') }}`,
    };
</script>
<script src="{{ mix('js/personalChallenge/index.js') }}">
</script>
@endsection
