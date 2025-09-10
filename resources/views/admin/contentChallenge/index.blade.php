@extends('layouts.app')

@section('after-styles')
<!-- DataTables -->
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.contentChallenge.breadcrumb', [
  'mainTitle' => trans('contentChallenge.title.manage'),
  'breadcrumb' => Breadcrumbs::render('contentChallenge.index'),
  'create' => true
])
<!-- /.content-header -->
@endsection

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="card-table-outer" id="contentChallengeManagment-wrap">
                <div class="table-responsive">
                    <table class="table custom-table" id="contentChallengeManagment">
                        <thead>
                            <tr>
                                <th>
                                    {{ trans('contentChallenge.table.updated_at') }}
                                </th>
                                <th>
                                    {{ trans('contentChallenge.table.category_name') }}
                                </th>
                                <th>
                                    {{ trans('contentChallenge.table.activities') }}
                                </th>
                                <th class="th-btn-4 no-sort">
                                    {{ trans('contentChallenge.table.action') }}
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
    var pagination              = {{$pagination}};
    var contentChallengeListUrl = '{{ route('admin.contentChallenge.getCategories') }}';

</script>
<script src="{{mix('js/contentchallenge/index.js')}}">
</script>
@endsection
