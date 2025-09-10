@extends('layouts.app')

@section('after-styles')
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="d-md-flex justify-content-between">
            <div class="align-self-center">
                <h1>
                    {{ trans('company.limit.title.index', ['company_name' => $company->name]) }}
                </h1>
                {{ Breadcrumbs::render('companies.limits.index', $companyType) }}
            </div>
            <div class="align-self-center">
                <a class="btn btn-outline-primary" href="{{ route('admin.companies.index',$companyType) }}">
                    <i class="far fa-arrow-left me-3 align-middle">
                    </i>
                    <span class="align-middle">
                        {{ trans('labels.buttons.back') }}
                    </span>
                </a>
            </div>
        </div>
    </div>
</div>
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <!-- .nav-tabs -->
        <div class="nav-tabs-wrap">
            <ul class="nav nav-tabs tabs-line-style" id="limitList" role="tablist">
                @if($company->allow_app == true)
                <li class="nav-item">
                    <a aria-controls="{{ trans('labels.company.challenge_activity') }}" aria-selected="false" class="nav-link" data-bs-toggle="tab" href="#challengepoints" role="tab">
                        {!! trans('company.limit.tabs.challenge_activity', ['split' => '
                        <br/>
                        ']) !!}
                    </a>
                </li>
                @endif
                @if($company->is_reseller == true || !is_null($company->parent_id))
                <li class="nav-item">
                    <a aria-controls="{{ trans('labels.company.reward_activity') }}" aria-selected="false" class="nav-link" data-bs-toggle="tab" href="#rewardspoints" role="tab">
                        {!! trans('company.limit.tabs.reward_activity', ['split' => '
                        <br/>
                        ']) !!}
                    </a>
                </li>
                <li class="nav-item">
                    <a aria-controls="{{ trans('labels.company.reward_point_limit') }}" aria-selected="false" class="nav-link" data-bs-toggle="tab" href="#rewardspointslimit" role="tab">
                        {!! trans('company.limit.tabs.reward_point_limit', ['split' => '
                        <br/>
                        ']) !!}
                    </a>
                </li>
                @endif
            </ul>
            <div class="tab-content" id="limitListContent">
                @if($company->allow_app == true)
                <div aria-labelledby="challengepoints" class="tab-pane fade" id="challengepoints" role="tabpanel">
                    <div class="text-end mb-4">
                        <a class="btn btn-primary me-3 btn-set-default" data-type="challenge" href="javascript:void(0);">
                            <i class="far fa-undo me-3 align-middle">
                            </i>
                            <span class="align-middle">
                                {{ trans('company.limit.buttons.default') }}
                            </span>
                        </a>
                        <a class="btn btn-primary" href="{{ route('admin.companies.editLimits', [$companyType, $company->id, 'type' => 'challenge']) }}">
                            <i class="far fa-edit me-3 align-middle">
                            </i>
                            <span class="align-middle">
                                {{ trans('buttons.general.edit') }}
                            </span>
                        </a>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="card-table-outer" id="challengePointManagment-wrap">
                                <div class="table-responsive">
                                    <table class="table custom-table" id="challengePointManagment">
                                        <thead>
                                            <tr>
                                                <th>
                                                    {{ trans('company.limit.table.target_type') }}
                                                </th>
                                                <th>
                                                    {{ trans('company.limit.table.target_values') }}
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
                @endif
                @if($company->is_reseller == true || !is_null($company->parent_id))
                <div aria-labelledby="rewardspoints" class="tab-pane fade" id="rewardspoints" role="tabpanel">
                    <div class="text-end mb-4">
                        <a class="btn btn-primary me-3 btn-set-default" data-type="reward-point" href="javascript:void(0);">
                            <i class="far fa-undo me-3 align-middle">
                            </i>
                            <span class="align-middle">
                                {{ trans('company.limit.buttons.default') }}
                            </span>
                        </a>
                        <a class="btn btn-primary" href="{{ route('admin.companies.editLimits', [$companyType, $company->id, 'type' => 'reward']) }}">
                            <i class="far fa-edit me-3 align-middle">
                            </i>
                            <span class="align-middle">
                                {{ trans('buttons.general.edit') }}
                            </span>
                        </a>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="card-table-outer" id="rewardPointManagment-wrap">
                                <div class="table-responsive">
                                    <table class="table custom-table" id="rewardPointManagment">
                                        <thead>
                                            <tr>
                                                <th>
                                                    {{ trans('company.limit.table.target_type') }}
                                                </th>
                                                <th>
                                                    {{ trans('company.limit.table.target_values') }}
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
                <div aria-labelledby="rewardspointslimit" class="tab-pane fade" id="rewardspointslimit" role="tabpanel">
                    <div class="text-end mb-4">
                        <a class="btn btn-primary me-3 btn-set-default" data-type="reward-daily-limit" href="javascript:void(0);">
                            <i class="far fa-undo me-3 align-middle">
                            </i>
                            <span class="align-middle">
                                {{ trans('company.limit.buttons.default') }}
                            </span>
                        </a>
                        <a class="btn btn-primary" href="{{ route('admin.companies.editLimits', [$companyType, $company->id, 'type' => 'reward-daily-limit']) }}">
                            <i class="far fa-edit me-3 align-middle">
                            </i>
                            <span class="align-middle">
                                {{ trans('buttons.general.edit') }}
                            </span>
                        </a>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="card-table-outer" id="rewardPointDailyLimitManagment-wrap">
                                <div class="table-responsive">
                                    <table class="table custom-table" id="rewardPointDailyLimitManagment">
                                        <thead>
                                            <tr>
                                                <th>
                                                    {{ trans('company.limit.table.target_type') }}
                                                </th>
                                                <th>
                                                    {{ trans('company.limit.table.target_values') }}
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
                @endif
            </div>
        </div>
        <!-- /.nav-tabs -->
    </div>
</section>
<div class="modal fade" id="set-default-model-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ trans('company.limit.modal.default.title') }}
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    {{ trans('company.limit.modal.default.message') }}
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">
                    {{ trans('buttons.general.no') }}
                </button>
                <button class="btn btn-primary" id="set-default-confirm" type="button">
                    {{ trans('buttons.general.yes') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('after-scripts')
<script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand()) }}">
</script>
<script type="text/javascript">
    var url = {
        datatable: `{{ route("admin.companies.getLimitsList", $company->id) }}`,
        default: `{{ route("admin.companies.setDefaultLimits", $company->id) }}`
    },
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    },
    message = {
        something_went_wrong: `{{ trans('labels.common_title.something_wrong_try_again') }}`,
    },
    dtS = [];
</script>
<script src="{{ asset('js/company/limit/index.js') }}">
</script>
<script type="text/javascript">
    $(document).ready(function() {
        var hash = window.location.hash;
        if (hash && $(`#limitList a[href="${hash}"]`).length > 0) {
            $(`#limitList li a[href="${hash}"]`).addClass('active show').attr("aria-selected", "true");
            $(`#limitListContent div${hash}`).addClass('active show');
            if (hash == "#challengepoints") {
                loadDT('#challengePointManagment', 'challenge');
            } else if (hash == "#rewardspoints") {
                loadDT('#rewardPointManagment', 'reward-point');
            } else if (hash == "#rewardspointslimit") {
                loadDT('#rewardPointDailyLimitManagment', 'reward-daily-limit');
            }
        } else {
            $('#limitList li:first a').addClass('active show').attr("aria-selected", "true");
            $('#limitListContent div:first').addClass('active show');
            @if($company->allow_app == true && ($company->is_reseller == true || !is_null($company->parent_id)))
            loadDT('#challengePointManagment', 'challenge');
            @elseif($company->allow_app == true)
            loadDT('#challengePointManagment', 'challenge');
            @elseif($company->is_reseller == true || !is_null($company->parent_id))
            loadDT('#rewardPointManagment', 'reward-point');
            @endif
        }
    });
</script>
@endsection
