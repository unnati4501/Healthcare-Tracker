@extends('layouts.app')

@section('after-styles')
<!-- DataTables -->
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.categories.breadcrumb', [
  'mainTitle' => trans('categories.subcategories.title.manage', [
    'category' => request()->category->name
  ]),
  'breadcrumb' => Breadcrumbs::render('subcategories.index', request()->category),
  'back' => true
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div class="card-table-outer" id="subCategoryManagment-wrap">
                    <div class="table-responsive">
                        <table class="table custom-table" id="subCategoryManagment">
                            <thead>
                                <tr>
                                    <th>
                                        {{ trans('categories.subcategories.table.updated_at') }}
                                    </th>
                                    <th>
                                        {{ trans('categories.subcategories.table.name') }}
                                    </th>
                                    <th class="text-center th-btn-2 no-sort">
                                        {{ trans('categories.subcategories.table.action') }}
                                    </th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /.container-fluid -->
</section>
<!-- /.content -->
@include('admin.categories.subcategories.delete-modal')
@endsection

@section('after-scripts')
<!-- DataTables -->
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}">
</script>
<script type="text/javascript">
    var pagination = {{ $pagination }};
    var subCategoriesListUrl = "{{ route('admin.subcategories.getSubCategories') }}";
    var subCategoriesDeleteUrl = "{{ route('admin.subcategories.delete','/') }}";
    var categoryId = {{ request()->category->id }};
    var deletedMessage = "{{ trans('categories.subcategories.messages.deleted') }}";
    var inUseMessage = "{{ trans('categories.subcategories.messages.in_use') }}";
    var unauthorizedMessage = "{{ trans('categories.subcategories.messages.unauthorized') }}";
    var somethingWentWrongMessage = "{{ trans('categories.subcategories.messages.something_wrong_try_again') }}";
</script>
<script src="{{mix('js/categories/subcategories/index.js')}}">
</script>
@endsection
