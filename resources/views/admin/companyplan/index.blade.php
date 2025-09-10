@extends('layouts.app')

@section('after-styles')
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.companyplan.breadcrumb',[
    'mainTitle'  => trans('companyplans.title.index_title'),
    'breadcrumb' => 'company-plan.index',
    'create'     => true,
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card search-card">
            <div class="card-body pb-0">
                <h4 class="d-md-none">
                    {{trans('companyplans.title.search')}}
                </h4>
                {{ Form::open(['route' => 'admin.company-plan.index', 'class' => 'form-horizontal', 'method'=>'get','role' => 'form', 'id'=>'companyplanSearch']) }}
                <div class="search-outer d-md-flex justify-content-between">
                    <div>
                        <div class="form-group">
                            {{ Form::select('grouptype', $groupType, request()->get('grouptype'), ['class' => 'form-control select2', 'id'=>'grouptype', 'style'=>'width: 100%;', 'autocomplete' => 'off', 'placeholder' => trans('companyplans.filter.select_group_type'), 'data-placeholder' => trans('companyplans.filter.select_group_type'), 'data-allow-clear' => 'true']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::text('companyplan', request()->get('companyplan'), ['class' => 'form-control', 'placeholder' => trans('companyplans.filter.search_by_company_name'), 'id' => 'companyplan', 'autocomplete' => 'off']) }}
                        </div>
                    </div>
                    <div class="search-actions align-self-start">
                        <button class="me-md-4 filter-apply-btn" type="submit">
                            {{trans('buttons.general.apply')}}
                        </button>
                        <a class="filter-cancel-icon" href="{{ route('admin.company-plan.index') }}">
                            <i class="far fa-times">
                            </i>
                            <span class="d-md-none ms-2 ms-md-0">
                                {{trans('buttons.general.reset')}}
                            </span>
                        </a>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="card-table-outer">
                    <div class="table-responsive">
                        <table class="table custom-table" id="companyPlan">
                            <thead>
                                <tr>
                                    <th class="text-center" style="display: none">
                                        {{trans('companyplans.table.updated_at')}}
                                    </th>
                                    <th>
                                        {{trans('companyplans.table.company_plan')}}
                                    </th>
                                    <th>
                                        {{trans('companyplans.table.mapped_companies')}}
                                    </th>
                                    <th>
                                        {{trans('companyplans.table.action')}}
                                    </th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Delete Model Popup -->
@include('admin.companyplan.delete-model')
@endsection
@section('after-scripts')
<script src="{{asset('assets/plugins/moment/moment.min.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/moment/moment-timezone-with-data-10-year-range.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}">
</script>
<script type="text/javascript">
    var url = {
        datatable: `{{ route('admin.company-plan.getcompanyplan') }}`,
        delete: `{{ route('admin.company-plan.delete', ':id') }}`,
    },
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    }
    message = {
        companyplan_deleted: `{{ trans('companyplans.modal.companyplan_deleted') }}`,
        unable_to_delete_companyplan: `{{ trans('companyplans.modal.failed_delete_company_plan') }}`,
        already_in_use: `{{ trans('companyplans.message.already_in_use') }}`,
    };
</script>
<script src="{{ asset('js/companyplan/index.js') }}" type="text/javascript">
</script>
@endsection
