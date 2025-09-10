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
                    {{ __('Moderators of ' . $company->name) }}
                </h1>
                {{ Breadcrumbs::render('companies.moderator.index', $companyType) }}
            </div>
            <div class="align-self-center">
                @permission('add-moderator')
                <a class="btn btn-primary" href="{{ route('admin.companies.createModerator', [$companyType, $company->id]) }}">
                    <i class="far fa-plus me-3 align-middle">
                    </i>
                    <span class="align-middle">
                        Add Moderator
                    </span>
                </a>
                @endauth
                <a class="btn btn-outline-primary" href="{{ route('admin.companies.index', $companyType) }}">
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
        <!-- search-block -->
        <div class="card search-card">
            <div class="card-body pb-0">
                <h4 class="d-md-none">
                    {{ trans('buttons.general.filter') }}
                </h4>
                {{ Form::open(['route' => ['admin.companies.moderators', [$companyType, $company->id]], 'class' => 'form-horizontal', 'method'=>'get', 'role' => 'form', 'id' => 'userSearch']) }}
                {{Form::hidden('companyType', $companyType)}}
                <div class="search-outer d-md-flex justify-content-between">
                    <div>
                        <div class="form-group">
                            {{ Form::text('recordEmail', request()->get('recordEmail'), ['class' => 'form-control', 'placeholder' => 'Search By Email', 'id' => 'recordEmail', 'autocomplete' => 'off']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::text('recordName', request()->get('recordName'), ['class' => 'form-control', 'placeholder' => 'Search By Full Name', 'id' => 'recordName', 'autocomplete' => 'off']) }}
                        </div>
                    </div>
                    <div class="search-actions align-self-start">
                        <button class="me-md-4 filter-apply-btn" type="submit">
                            {{ trans('buttons.general.apply') }}
                        </button>
                        <a class="filter-cancel-icon" href="{{ route('admin.companies.moderators', [$companyType, $company->id]) }}">
                            <i class="far fa-times">
                            </i>
                            <span class="d-md-none ms-2 ms-md-0">
                                {{ trans('buttons.general.reset') }}
                            </span>
                        </a>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
        <a class="btn btn-primary filter-btn" href="javascript:void(0);">
            <i class="far fa-filter me-2 align-middle">
            </i>
            <span class="align-middle">
                {{ trans('buttons.general.filter') }}
            </span>
        </a>
        <!-- /.search-block -->
        <div class="card">
            <div class="card-body">
                <div class="card-table-outer">
                    <div class="table-responsive">
                        <table class="table custom-table" id="moderatorsManagment">
                            <thead>
                                <tr>
                                    <th>
                                        {{ __('Name') }}
                                    </th>
                                    <th>
                                        {{ __('Email') }}
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
@endsection

@section('after-scripts')
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}">
</script>
<script type="text/javascript">
    var url = {
        datatable: `{{ route("admin.companies.getCompanyModerators", $company->id) }}`,
    },
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    };
</script>
<script src="{{ asset('js/company/moderator/index.js') }}" type="text/javascript">
</script>
@endsection
