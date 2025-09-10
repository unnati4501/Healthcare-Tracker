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
                    {{ __('App Settings of ' . $company->name) }}
                </h1>
                {{ Breadcrumbs::render('companiesold.app-settings.index') }}
            </div>
            <div class="align-self-center">

                <a class="btn btn-outline-primary" href="{{ route('admin.companiesold.index', $companyType) }}">
                    <i class="far fa-arrow-left me-3 align-middle">
                    </i>
                    <span class="align-middle">
                        Back
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
        <!-- .grid -->
        <div class="card">
            <div class="card-body">
                <div class="text-end mb-10">
                    @permission('change-company-app-settings')
                    @if($dsiplayDefaultSettings)
                    <a class="btn btn-primary" data-bs-target="#defaultSettings-model-box" data-bs-toggle="modal" href="javascript:void(0);">
                        <i class="far fa-cogs me-3 align-middle">
                        </i>
                        <span class="align-middle">
                            Default Settings
                        </span>
                    </a>
                    @endif
                    <a class="btn btn-primary" href="{{ route('admin.companiesold.changeAppSettingCreateEdit', [$companyType, $company->id]) }}">
                        <i class="far fa-cogs me-3 align-middle">
                        </i>
                        <span class="align-middle">
                            {{ trans('labels.app_settings.change_settings_btn') }}
                        </span>
                    </a>
                    @endauth
                </div>
                <div class="card-table-outer">
                    <div class="table-responsive">
                        <table class="table custom-table" id="AppSettingsManagment">
                            <thead>
                                <tr>
                                    <th class="text-center" style="display: none">
                                        Updated At
                                    </th>
                                    <th class="no-sorting-arrow" width="110">
                                        {{trans('labels.common_title.sr_no')}}
                                    </th>
                                    <th>
                                        {{trans('labels.app_settings.app_key')}}
                                    </th>
                                    <th>
                                        {{trans('labels.app_settings.app_value')}}
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
        <!-- .grid -->
    </div>
</section>
<div class="modal fade" data-id="0" id="defaultSettings-model-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Set default settings?
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    Are you sure you want to set the default setting?
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">
                    No
                </button>
                <a class="btn btn-primary" href="{{ route('admin.companiesold.changeToDefaultSettings', request()->company) }}" id="defaultSettings-model-box-confirm">
                    Yes
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('after-scripts')
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}">
</script>
<script type="text/javascript">
    var pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    };
    $(document).ready(function() {
        $('#AppSettingsManagment').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('admin.companiesold.getCompanyAppSettings','zevo') }}',
                data: {
                    company: {{ $company->id }},
                    getQueryString: window.location.search
                },
            },
            columns: [
                {data: 'updated_at', name: 'updated_at' , visible: false},
                {data: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'key', name: 'key'},
                {data: 'value', name: 'value'}
            ],
            paging: false,
            pageLength: pagination.value,
            dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
            lengthChange: false,
            searching: false,
            ordering: true,
            order: [[0, 'desc']],
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
            // dom: '<<tr><"pagination-wrap"ip>',
            // initComplete: (settings, json) => {
            //     $('.pagination-wrap').appendTo(".card-table-outer");
            // },
        });
    });
</script>
@endsection
