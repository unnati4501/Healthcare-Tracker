@extends('layouts.app')
@section('after-styles')
<!-- DataTables -->
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="d-md-flex justify-content-between">
            <div class="align-self-center">
                <h1>
                    {{ trans('labels.company.manage_credits') }}
                </h1>
                {{ Breadcrumbs::render('companies.manageCredits', $companyType, $company->id) }}
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
        {{ Form::open(['route' => ['admin.companies.storeCredits', $companyType, $company->id], 'class' => 'form-horizontal zevo_form_submit', 'method' => 'PATCH', 'role' => 'form', 'id' => 'storeCredits']) }}
        {{Form::hidden('company_id', $company->id, ['id' => 'company_id'])}}
        {{Form::hidden('companyType', $companyType)}}
        {{Form::hidden('available_credits', (isset($company->credits) ? $company->credits : 0), ['id' => 'available_credits'])}}
        <div class="card form-card">
            <div class="card-body">
                <div class="card-inner">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="session-detail-block bg-light mb-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="me-3">
                                        <span class="">{{trans('company.manage_credits.form.labels.available_credits')}}</span>
                                        <span class="d-block font-20">{{$company->credits}}</span>
                                    </div>
                                </div>
                            </div>
                        </div> 
                        <div class="col-lg-4">
                            <div class="session-detail-block bg-light mb-4">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="me-3">
                                        <span class="">{{trans('company.manage_credits.form.labels.onhold_credits')}}</span>
                                        <span class="d-block font-20">{{$company->on_hold_credits}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group">
                                {{ Form::label('company_name', trans('company.manage_credits.form.labels.company_name')) }}
                                    {{ Form::text('company_name', $company->name, ['class' => 'form-control', 'id' => 'company_name', 'disabled' => true]) }}
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                {{ Form::label('update_type', trans('company.manage_credits.form.labels.update_type')) }}
                                    {{ Form::select('type', ['Add' => 'Add', 'Remove' => 'Remove'], (isset($type) ? $type : 'Add'), ['class' => 'form-control select2', 'id' => 'update_type', 'data-allow-clear' => 'false']) }}
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                {{ Form::label('credits', trans('company.manage_credits.form.labels.credits')) }}
                                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{trans('company.manage_credits.form.tooltips.credits')}}">
                                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                                    </i>
                                </span>
                                {{ Form::number('credits', null, ['class' => 'form-control', 'placeholder' => trans('company.manage_credits.form.placeholder.credits'), 'min' => 1, 'max' => 100, "oninput"=>"validity.valid||(value='')", 'autocomplete' => 'off']) }}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="form-group">
                                {{ Form::label('notes', trans('company.manage_credits.form.labels.note')) }}
                                {{ Form::textarea('notes', null, ['class' => 'form-control', 'placeholder' => trans('company.manage_credits.form.placeholder.enter_note'), 'id' => 'notes', 'rows' => 4]) }}
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                {{ Form::label('updated_by', trans('company.manage_credits.form.labels.user_name')) }}
                                    {{ Form::text('user_name', null, ['class' => 'form-control', 'placeholder' => trans('company.manage_credits.form.placeholder.enter_user_name'), 'id' => 'user_name']) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{{ route('admin.companies.index', $companyType) }}">
                        {{ trans('labels.buttons.cancel') }}
                    </a>
                    <a class="btn btn-primary" href="javascript:void(0);" id="zevo_submit_btn" title="Save">
                        {{ trans('labels.buttons.save') }}
                    </a>
                </div>
            </div>
        </div>
        {{ Form::close() }}
    </div>

    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div class="card-table-outer">
                    <div class="d-md-flex justify-content-between">
                        <h4 class="mt-2">
                        {{trans('company.manage_credits.table.credit_history')}}
                        </h4>
                        <div class="dt-buttons">
                            <button class="btn btn-primary" id="exportCreditHistory" type="button">
                                <span>
                                    <i class="far fa-envelope me-3 align-middle">
                                    </i>
                                    {{trans('buttons.general.export')}}
                                </span>
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table custom-table" id="creditHistory">
                                <thead>
                                    <tr>
                                        <th>
                                            {{ trans('company.manage_credits.table.date_time') }}
                                        </th>
                                        <th>
                                            {{ trans('company.manage_credits.table.action') }}
                                        </th>
                                        <th>
                                            {{ trans('company.manage_credits.table.credit_count') }}
                                        </th>
                                        <th>
                                            {{ trans('company.manage_credits.table.updated_by') }}
                                        </th>
                                        <th>
                                            {{ trans('company.manage_credits.table.available_credits') }}
                                        </th>
                                        <th>
                                            {{ trans('company.manage_credits.table.notes') }}
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

    
</section>

<div class="modal fade" data-id="0" id="add-remove-credit-model-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-title">
                    
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body">
                <p class="m-0" id="modal-message"></p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">
                    {{ trans('buttons.general.cancel') }}
                </button>
                <button class="btn btn-primary" id="add-remove-credits-confirm" type="button">
                    {{ trans('buttons.general.okay') }}
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" data-id="0" id="export-model-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{trans('company.manage_credits.modal.title')}}
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div id="exportCreditHistoryReport">
                <div class="modal-body">
                    <div class="container">
                        <div class="form-group">
                            {{ Form::label('email', trans('company.manage_credits.modal.email')) }}
                            {{ Form::text('email', $loginemail, ['class' => 'form-control', 'placeholder' => trans('company.manage_credits.modal.enter_email_address'), 'id' => 'email', 'autocomplete' => 'off']) }}
                            <span id="emailError" class="invalid-feedback" style="display:none"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">
                        {{trans('buttons.general.cancel')}}
                    </button>
                    <button class="btn btn-primary" id="export-model-box-confirm" type="button">
                        {{trans('buttons.general.export')}}
                    </button>
                </div>
            </div>
            <div class="modal-body" id="exportContentMsg" style="display: none">
                {{trans('contentreport.modal.report_running_background')}}
            </div>
        </div>
    </div>
</div>
@endsection

@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\StoreCreditRequest', '#storeCredits') !!}
<script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand()) }}">
</script>
<script type="text/javascript">
    var url = {
        datatable: `{{ route('admin.companies.credit-history', [$companyType, $company->id]) }}`,
        exportReport: `{{ route('admin.companies.export-credit-history', [$companyType, $company->id]) }}`,
    },
    timezone = `{{ $timezone }}`,
    date_format = `{{ $date_format }}`,
    loginemail = '{{ $loginemail }}';
    pagination = {
        value: `{{ $pagination }}`,
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    },
    messages = {
        failedToLoad: `{!! trans('company.manage_credits.messages.failed_to_load') !!}`,
        emailRequired: `{!! trans('company.manage_credits.messages.email_required') !!}`,
        validEmail   : `{!! trans('company.manage_credits.messages.valid_email') !!}`,
        creditCountError:  `{!! trans('company.manage_credits.messages.error_credit_count') !!}`,
    };
</script>
<script src="{{ mix('js/company/manage-credit.js') }}" type="text/javascript">
</script>
@endsection
