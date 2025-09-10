@extends('layouts.app')

@section('after-styles')
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/datatables/extensions/ColReorder/css/rowReorder.dataTables.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection
<!-- Content Header (Page header) -->
@section('content-header')
@include('admin.companies.dt-banners.breadcrumb', [
    'mainTitle' => trans('company.dt_banners.title.index', ['company' => $company->name]),
    'breadcrumb' => Breadcrumbs::render('companies.dt-banners.index', [$companyType]),
    'create' => true,
    'backToCompany' => true,
    'back' => false
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <!-- .grid -->
        <div class="card">
            <div class="card-body">
                <div class="card-table-outer">
                    <div class="table-responsive">
                        <table class="table custom-table" id="dtBannerManagement">
                        {{ Form::hidden('companyType', $companyType, ['id' => 'companyType']) }}
                            <thead>
                                <tr>
                                    <th class="text-center" style="display: none">
                                        {{trans('company.dt_banners.table.updated_at')}}
                                    </th>
                                    <th>
                                        {{trans('company.dt_banners.table.order')}}
                                    </th>
                                    <th>
                                        {{trans('company.dt_banners.table.description')}}
                                    </th>
                                    <th class="text-center no-sort th-btn-2">
                                        {{trans('company.dt_banners.table.banner')}}
                                    </th>
                                    <th>
                                        {{trans('company.dt_banners.table.action')}}
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
<div class="modal fade" data-id="0" id="delete-model-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Delete Banner?
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    Are you sure you want to delete this banner?
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">
                    {{ trans('buttons.general.cancel') }}
                </button>
                <button class="btn btn-primary" id="delete-model-box-confirm" type="button">
                    {{ trans('buttons.general.delete') }}
                </button>
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
<script src="{{asset('assets/plugins/datatables/extensions/ColReorder/js/dataTables.rowReorder.min.js?var='.rand())}}">
</script>
<script type="text/javascript">
    var pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    },
    companyId = `{{ $company->id }}`,
    url = {
        datatable   : `{{ route('admin.companies.getDigitalTherapyBanners', 'zevo') }}`,
        reorderingScreen: `{{ route('admin.companies.reorderingScreen', $company->id) }}`,
        deleteBanner: `{{ route('admin.companies.deleteBanner', ':id') }}`,
    },
    messages = {
        banner_deleted: `{{trans('company.dt_banners.messages.banner_deleted')}}`,
        required_one_banner: `{{trans('company.dt_banners.validation.banner_validation_min_limit')}}`,
        delete_error: `{{trans('company.dt_banners.validation.delete_error')}}`,
    };
</script>
<script src="{{ mix('js/company/dtBanner/index.js') }}"></script>
@endsection
