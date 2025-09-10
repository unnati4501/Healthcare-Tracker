@extends('layouts.app')

@section('after-styles')
<!-- DataTables -->
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/datatables/extensions/ColReorder/css/rowReorder.dataTables.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.slides.breadcrumb', [
    'appPageTitle' => trans('appslides.title.index_title'),
    'breadcrumb' => 'appslides.index'
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        <!-- Nav tabs -->
        <div class="nav-tabs-wrap">
            <ul class="nav nav-tabs tabs-line-style" id="myTab" role="tablist">
                <li class="nav-item">
                    <a app-count="{{$onBoardingappCount}}" aria-controls="app" aria-selected="true" class="nav-link active" href="javascript:void(0)" id="app-tab" role="tab">
                        {{trans('appslides.title.app')}}
                    </a>
                </li>
                <li class="nav-item">
                    <a aria-controls="portal" aria-selected="false" class="nav-link" href="javascript:void(0)" id="portal-tab" portal-count="{{$onBoardingportalCount}}" role="tab">
                        {{trans('appslides.title.portal')}}
                    </a>
                </li>
                {{-- <li class="nav-item">
                    <a aria-controls="eap" aria-selected="false" class="nav-link" href="javascript:void(0)" id="eap-tab" eap-count="{{$onBoardingeapCount}}" role="tab">
                        {{trans('appslides.title.eap')}}
                    </a>
                </li> --}}
            </ul>
            <div class="tab-content" id="myTabContent">
                <div aria-labelledby="app-tab" class="tab-pane fade show active" id="app" role="tabpanel">
                    <div class="text-end mb-4 tab-button">
                        @permission('create-onboarding')
                        <a class="btn btn-primary {{ ($onBoardingappCount >= 3)? 'hidden':'' }}" href="{!! route('admin.appslides.create',['type' => 'app']) !!}" id="addOnBoardingBtn">
                            <i class="far fa-plus me-3 align-middle">
                            </i>
                            {{trans('appslides.buttons.add_record')}}
                        </a>
                        @endauth
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="card-table-outer" id="slideManagment-wrap">
                                <div class="table-responsive">
                                    <table class="table custom-table" id="slideManagment">
                                        <thead>
                                            <tr>
                                                <th>
                                                    {{trans('appslides.table.order')}}
                                                </th>
                                                <th style="display: none">
                                                    {{trans('appslides.table.id')}}
                                                </th>
                                                <th>
                                                    {{trans('appslides.table.description')}}
                                                </th>
                                                <th>
                                                    {{trans('appslides.table.banner')}}
                                                </th>
                                                <th class="th-btn-2 no-sort">
                                                    {{trans('appslides.table.action')}}
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
            </div>
        </div>
    </div>
</section>
<!-- /.modals -->
@include('admin.slides.delete-modal')
@endsection
@section('after-scripts')
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datatables/extensions/ColReorder/js/dataTables.rowReorder.min.js?var='.rand())}}">
</script>
<script type="text/javascript">
var url = {
    datatable: `{{ route('admin.appslides.getSlides') }}`,
    appSlidesApp: `{!! route('admin.appslides.create',['type' => 'app']) !!}`,
    appSlidesPortal: `{!! route('admin.appslides.create',['type' => 'portal']) !!}`,
    appSlidesEAP: `{!! route('admin.appslides.create',['type' => 'eap']) !!}`,
    reorderingScreen: `{{ route('admin.appslides.reorderingScreen') }}`,
    appSlidesDelete: `{{route('admin.appslides.delete','/')}}`,
},
pagination = {
    value: {{ $pagination }},
    previous: `{!! trans('buttons.pagination.previous') !!}`,
    next: `{!! trans('buttons.pagination.next') !!}`,
},
message = {
    something_wrong: `{{ trans('appslides.message.something_wrong') }}`,
    onboarding_side_deleted: `{{ trans('appslides.message.onboarding_side_deleted') }}`,
    delete_error: `{{ trans('appslides.message.delete_error') }}`,
};
</script>
<script src="{{ asset('js/appslides/index.js') }}" type="text/javascript">
</script>
@endsection
