@extends('layouts.app')

@section('after-styles')
<!-- DataTables -->
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.group.breadcrumb',[
    'appPageTitle' => trans('group.title.details'),
    'breadcrumb' => 'group.view',
    'create'     => false,
    'back'       => true,
    'edit'       => true,
    'string'     => $string
])
<!-- /.content-header -->
@endsection
@section('content')
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card form-card">
            <div class="card-body">
                <div class="card-inner">
                    <div class="row">
                        <div class="col-xl-5 border-xl-right">
                            <div class="row event-row-list">
                                <div class="col-lg-6 gray-900">
                                    {{trans('group.table.logo')}}
                                </div>
                                <div class="col-lg-6 gray-600">
                                    <div class="logo-preview">
                                        <img alt="" src="{{$groupData->logo ?? asset('assets/dist/img/boxed-bg.png')}}">
                                        </img>
                                    </div>
                                </div>
                                <div class="col-lg-6 gray-900">
                                    {{trans('group.table.grouptitle')}}
                                </div>
                                <div class="col-lg-6 gray-600">
                                    {{$groupData->title}}
                                </div>
                                <div class="col-lg-6 gray-900">
                                    {{trans('group.table.subcategoryname')}}
                                </div>
                                <div class="col-lg-6 gray-600">
                                    {{ $groupData->subcategory->name }}
                                </div>
                                <div class="col-lg-6 gray-900">
                                    {{trans('group.table.grouptype')}}
                                </div>
                                <div class="col-lg-6 gray-600">
                                    {{ ucfirst($groupData->type) }}
                                </div>
                                <div class="col-lg-6 gray-900">
                                    {{trans('group.table.groupmembers')}}
                                </div>
                                <div class="col-lg-6 gray-600">
                                    {{ $members }}
                                </div>
                                <div class="col-lg-6 gray-900">
                                    {{ Form::label('description', trans('labels.group.introduction'), ['class' => 'fw-medium']) }}
                                </div>
                                <div class="col-lg-12 gray-600 form-group mb-0 mt-2">
                                    {!! Form::textarea('introduction', @$description , ['id' => 'introduction', 'rows' => 5, 'class' => 'form-control','placeholder'=>'','spellcheck'=>'false','disabled' =>'true']) !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-7 mb-4 order-xl-2">
                            <div class="table-responsive">
                                <table class="table custom-table" id="userlist">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="display: none">
                                                {{trans('group.table.updated_at')}}
                                            </th>
                                            <th>
                                                {{trans('group.table.name')}}
                                            </th>
                                            <th>
                                                {{trans('group.table.email')}}
                                            </th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{!! route('admin.groups.index').$string !!}">
                        {{ trans('buttons.general.cancel') }}
                    </a>
                    <a class="btn btn-primary" href="{!! route('admin.groups.edit',$groupData->id) !!}">
                        {{ trans('buttons.general.edit') }}
                    </a>
                </div>
            </div> --}}
        </div>
    </div>
    <!-- /.container-fluid -->
</section>
<!-- /.content -->
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
        datatable: `{{ route('admin.groups.getMembersList',$groupData->id) }}`,
    },
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    };
</script>
<script src="{{ asset('js/group/details.js') }}" type="text/javascript">
</script>
@endsection
