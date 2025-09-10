@extends('layouts.app')

@section('after-styles')
<!-- DataTables -->
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.challenge.breadcrumb', [
  'mainTitle' => $pageTitle,
  'breadcrumb' => $route . '.details',
  'back' => true
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card form-card">
            <div class="card-body">
                <div class="card-inner">
                    <div class="row">
                        <div class="col-xl-5 border-xl-right mb-5 mb-xl-0">
                            <h5 class="text-primary mb-3">
                                {{ trans('challenges.details.title.basic') }}
                            </h5>
                            <table class="table custom-table no-hover">
                                <tbody>
                                    <tr>
                                        <td class="border-top-0 gray-900">
                                            {{ trans('challenges.details.labels.name') }}
                                        </td>
                                        <td class="border-top-0">
                                            {{$challengeData->title}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="gray-900">
                                            {{ trans('challenges.details.labels.participants') }}
                                        </td>
                                        <td>
                                            {{ $totalMembers }}
                                        </td>
                                    </tr>
                                    @if($route != 'challenges')
                                    <tr>
                                        <td class="gray-900">
                                            {{ trans('challenges.details.labels.teams') }}
                                        </td>
                                        <td>
                                            {{ $totalTeams }}
                                        </td>
                                    </tr>
                                    @endif
                                    @if($route == 'interCompanyChallenges')
                                    <tr>
                                        <td class="gray-900">
                                            {{ trans('challenges.details.labels.companies') }}
                                        </td>
                                        <td>
                                            {{ $totalCompanies }}
                                        </td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td class="gray-900">
                                            {{ trans('challenges.details.labels.category') }}
                                        </td>
                                        <td>
                                            {{$challengeData->challengecategory->name}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="gray-900">
                                            {{ trans('challenges.details.labels.type') }}
                                        </td>
                                        <td>
                                            {{ ($challengeData->close == true)? "Closed" : "Open" }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="gray-900">
                                            {{ trans('challenges.details.labels.start_date') }}
                                        </td>
                                        <td>
                                            <span class="text-nowrap d-block">
                                                <i class="far fa-calendar me-2">
                                                </i>
                                                {{ Illuminate\Support\Carbon::parse($challengeData->start_date)->setTimezone($timezone)->format(config('zevolifesettings.date_format.default_date')) }}
                                            </span>
                                            <span class="text-nowrap d-block">
                                                <i class="far fa-clock me-2">
                                                </i>
                                                {{ Illuminate\Support\Carbon::parse($challengeData->start_date)->setTimezone($timezone)->format(config('zevolifesettings.date_format.default_time')) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="gray-900">
                                            {{ trans('challenges.details.labels.end_date') }}
                                        </td>
                                        <td>
                                            <span class="text-nowrap d-block">
                                                <i class="far fa-calendar me-2">
                                                </i>
                                                {{ Illuminate\Support\Carbon::parse($challengeData->end_date)->setTimezone($timezone)->format(config('zevolifesettings.date_format.default_date')) }}
                                            </span>
                                            <span class="text-nowrap d-block">
                                                <i class="far fa-clock me-2">
                                                </i>
                                                {{ Illuminate\Support\Carbon::parse($challengeData->end_date)->setTimezone($timezone)->format(config('zevolifesettings.date_format.default_time')) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="gray-900">
                                            {{ trans('challenges.details.labels.status') }}
                                        </td>
                                        <td>
                                            {{ $status }}
                                        </td>
                                    </tr>
                                    @if($route == 'challenges')
                                    <tr>
                                        <td class="gray-900">
                                            {{ trans('challenges.details.labels.recursive') }}
                                        </td>
                                        <td>
                                            {{ ($challengeData->recurring)? "Yes" : "No" }}
                                        </td>
                                    </tr>
                                    @if($challengeData->recurring)
                                    <tr>
                                        <td class="gray-900">
                                            {{ trans('challenges.details.labels.recursive_count') }}
                                        </td>
                                        <td>
                                            {{ $challengeData->recurring_count }}
                                        </td>
                                    </tr>
                                    @endif
                                    @endif
                                </tbody>
                            </table>
                            <div>
                                <table class="table table-bordered mb-3">
                                    <thead>
                                        <tr>
                                            <th>
                                                {{ trans('challenges.details.labels.target_name') }}
                                            </th>
                                            <th>
                                                {{ trans('challenges.details.labels.target_value') }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($target as $key => $value)
                                        <?php $exerciseName = ""; 
                                        if($value['challenge_target_id'] == 4 && array_key_exists($value['model_id'], $exercises)) {  
                                            $exerciseName = $exercises[$value['model_id']]; 
                                        }
                                        if($value['challenge_target_id'] == 6 && !empty($value['content_challenge_ids'])) {  
                                            $contentValue = explode(',',$value['content_challenge_ids']);
                                            $contentCategory = "";
                                            foreach($contentValue as $key=>$val){
                                                $contentCategory.=  $contentCategories[$val].", ";
                                            }
                                        }  ?>
                                        <tr>
                                            <td>
                                                {{ $value['targetName'] }} 
                                                {{ (!empty($exerciseName))? "( ".$exerciseName." )" : "" }}
                                                {{ (!empty($contentCategory))? "( ".trim($contentCategory, ', ')." )" : "" }}
                                            </td>
                                            <td>
                                                {{ ($challengeData->challenge_category_id != 2) ? $value['target']." ".ucfirst($value['uom']) : "NA" }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-xl-7">
                            <h5 class="text-primary mb-3 d-flex align-items-center">
                                {{ trans('challenges.details.title.participants') }}
                                    @if($status == 'Ongoing' || $status == 'Finished')
                                    <button class="btn btn-primary ms-auto" id="exportChallengeDetailHistory" data-id="{{$challengeData->id}}" data-totalcompanies="{{$totalCompanies}}" data-totalteams="{{$totalTeams}}" data-totalmembers="{{$totalMembers}}"  type="button">
                                        <span>
                                            <i class="far fa-envelope me-3 align-middle">
                                            </i>
                                            {{trans('buttons.general.export')}}
                                        </span>
                                    </button>
                                    @endif
                            </h5>
                            <div class="card-table-outer">
                                <div class="nav-tabs-wrap">
                                    <ul class="nav nav-tabs tabs-line-style" id="challengeParticipantTab" role="tablist">
                                        @if($route != 'teamChallenges' && $route != 'companyGoalChallenges' && $route != 'challenges')
                                        <li class="nav-item">
                                            <a aria-controls="Company" aria-selected="true" class="nav-link active" data-bs-toggle="tab" href="#company" id="company-tab" role="tab">
                                                Company
                                            </a>
                                        </li>
                                        @endif
                                        @if($route != 'challenges')
                                        <li class="nav-item">
                                            <a aria-controls="Team" aria-selected="false" class="nav-link {{ ($route ==  'teamChallenges' || $route ==  'companyGoalChallenges') ? 'active' : ''  }}" data-bs-toggle="tab" href="#team" id="team-tab" role="tab">
                                                Team
                                            </a>
                                        </li>
                                        @endif
                                        <li class="nav-item">
                                            <a aria-controls="Individual" aria-selected="false" class="nav-link {{ ($route ==  'challenges') ? 'active' : ''  }}" data-bs-toggle="tab" href="#individual" id="individual-tab" role="tab">
                                                Individual
                                            </a>
                                        </li>
                                    </ul>
                                    <div class="tab-content" id="challengeParticipantTabContent">
                                        @if($route != 'teamChallenges' && $route != 'companyGoalChallenges' && $route != 'challenges')
                                        <div aria-labelledby="company-tab" class="tab-pane fade show active" id="company" role="tabpanel">
                                            <div class="table-responsive">
                                                <table class="table custom-table" id="companylist">
                                                    <thead>
                                                        <tr>
                                                            <th class="no-sort th-btn-4">
                                                                {{ trans('challenges.details.table.logo') }}
                                                            </th>
                                                            <th>
                                                                {{ trans('challenges.details.table.company') }}
                                                            </th>
                                                            <th>
                                                                {{ trans('challenges.details.table.total_teams') }}
                                                            </th>
                                                            <th>
                                                                {{ trans('challenges.details.table.total_users') }}
                                                            </th>
                                                            <th width="200">
                                                                {{ trans('challenges.details.table.points') }}
                                                            </th>
                                                            <th width="100">
                                                                {{ trans('challenges.details.table.rank') }}
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                </table>
                                            </div>
                                        </div>
                                        @endif
                                        @if($route != 'challenges')
                                        <div aria-labelledby="team-tab" class="tab-pane fade {{ ($route ==  'teamChallenges' || $route ==  'companyGoalChallenges') ? 'show active' : ''  }}" id="team" role="tabpanel">
                                            <div class="table-responsive">
                                                <table class="table custom-table" id="teamlist">
                                                    <thead>
                                                        <tr>
                                                            <th class="no-sort th-btn-4">
                                                                {{ trans('challenges.details.table.logo') }}
                                                            </th>
                                                            <th>
                                                                {{ trans('challenges.details.table.team') }}
                                                            </th>
                                                            <th>
                                                                {{ trans('challenges.details.table.total_users') }}
                                                            </th>
                                                            <th width="200">
                                                                {{ trans('challenges.details.table.points') }}
                                                            </th>
                                                            <th width="100">
                                                                {{ trans('challenges.details.table.rank') }}
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                </table>
                                            </div>
                                        </div>
                                        @endif
                                        <div aria-labelledby="individual-tab" class="tab-pane fade {{ ($route ==  'challenges') ? 'show active' : ''  }}" id="individual" role="tabpanel">
                                            <div class="table-responsive">
                                                <table class="table custom-table" id="userlist">
                                                    <thead>
                                                        <tr>
                                                            <th class="no-sort th-btn-4">
                                                                {{ trans('challenges.details.table.logo') }}
                                                            </th>
                                                            <th>
                                                                {{ trans('challenges.details.table.name') }}
                                                            </th>
                                                            <th width="200">
                                                                {{ trans('challenges.details.table.points') }}
                                                            </th>
                                                            <th width="100">
                                                                {{ trans('challenges.details.table.rank') }}
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {{-- <div class="table-responsive">
                                    @if($route == 'challenges')
                                    <table class="table custom-table" id="userlist">
                                        <thead>
                                            <tr>
                                                <th class="no-sort th-btn-4">
                                                    {{ trans('challenges.details.table.logo') }}
                                                </th>
                                                <th>
                                                    {{ trans('challenges.details.table.name') }}
                                                </th>
                                                <th width="200">
                                                    {{ trans('challenges.details.table.points') }}
                                                </th>
                                                <th width="100">
                                                    {{ trans('challenges.details.table.rank') }}
                                                </th>
                                            </tr>
                                        </thead>
                                    </table>
                                    @elseif($route == 'teamChallenges' || ($route == 'companyGoalChallenges'))
                                    <table class="table custom-table" id="teamlist">
                                        <thead>
                                            <tr>
                                                <th class="no-sort th-btn-4">
                                                    {{ trans('challenges.details.table.logo') }}
                                                </th>
                                                <th>
                                                    {{ trans('challenges.details.table.team') }}
                                                </th>
                                                <th>
                                                    {{ trans('challenges.details.table.total_users') }}
                                                </th>
                                                <th width="200">
                                                    {{ trans('challenges.details.table.points') }}
                                                </th>
                                                <th width="100">
                                                    {{ trans('challenges.details.table.rank') }}
                                                </th>
                                            </tr>
                                        </thead>
                                    </table>
                                    @elseif($route == 'interCompanyChallenges')
                                    <table class="table custom-table" id="companylist">
                                        <thead>
                                            <tr>
                                                <th class="no-sort th-btn-4">
                                                    {{ trans('challenges.details.table.logo') }}
                                                </th>
                                                <th>
                                                    {{ trans('challenges.details.table.company') }}
                                                </th>
                                                <th>
                                                    {{ trans('challenges.details.table.total_teams') }}
                                                </th>
                                                <th>
                                                    {{ trans('challenges.details.table.total_users') }}
                                                </th>
                                                <th width="200">
                                                    {{ trans('challenges.details.table.points') }}
                                                </th>
                                                <th width="100">
                                                    {{ trans('challenges.details.table.rank') }}
                                                </th>
                                            </tr>
                                        </thead>
                                    </table>
                                    @endif
                                </div> --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@include('admin.challenge.export_modal')
<!-- /.container-fluid -->
@endsection
<!-- include datatable css -->
@section('after-scripts')
<!-- DataTables -->
<script src="{{ asset('js/external/jquery.form.min.js?var='.rand()) }}"></script>
<script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand()) }}">
</script>
<script type="text/javascript">
    var roleGroup = '{{ Auth::user()->roles()->first()->group }}',
    loginemail = '{{ $loginemail }}',
    url = {
        getMembersList: `{{ ($route == 'challenges') ? route('admin.challenges.getMembersList',$challengeData->id) : route('admin.challenges.getMembersListOther',$challengeData->id) }}`,
        getTeamMembersList: `{{ route('admin.teamChallenges.getTeamMembersList',$challengeData->id) }}`,
        getCompanyMembersList: `{{ route('admin.interCompanyChallenges.getCompanyMembersList',$challengeData->id) }}`,
        exportChallengeDetails:`{{ route('admin.'.$route.'.exportChallengeDetails') }}`,
        routeUrl: `{{ $route }}`,
    },
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    },
    message = {
        unauthorized: `{{ trans('challenges.messages.unauthorized') }}`,
        somethingWentWrong: `{{ trans('challenges.messages.something_wrong_try_again') }}`,
    };
</script>
<script src="{{ mix('js/challenges/details.js') }}">
</script>
@endsection
