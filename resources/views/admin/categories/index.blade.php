@extends('layouts.app')

@section('after-styles')
<!-- DataTables -->
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.categories.breadcrumb', [
  'mainTitle' => trans('categories.title.manage'),
  'breadcrumb' => Breadcrumbs::render('categories.index'),
  'create' => true
])
<!-- /.content-header -->
@endsection

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="card-table-outer" id="categoryManagment-wrap">
                <div class="table-responsive">
                    <table class="table custom-table" id="categoryManagment">
                        <thead>
                            <tr>
                                <th>
                                    {{ trans('categories.table.updated_at') }}
                                </th>
                                <th>
                                    {{ trans('categories.table.name') }}
                                </th>
                                <th>
                                    {{ trans('categories.table.total') }}
                                </th>
                                <th class="th-btn-4 no-sort">
                                    {{ trans('categories.table.action') }}
                                </th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
<!-- include datatable css -->
@section('after-scripts')
<!-- DataTables -->
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}">
</script>
<script type="text/javascript">
    var pagination = {{$pagination}};
    var categoriesListUrl = '{{ route('admin.categories.getCategories') }}';
</script>
<script src="{{mix('js/categories/index.js')}}">
</script>
@endsection
