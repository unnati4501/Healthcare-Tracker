@extends('layouts.app')

@section('after-styles')
    <!-- DataTables -->
    <link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
@endsection

@section('content')

    @include('admin.department.breadcrumb',['appPageTitle' => trans('labels.department.department_location_title')])

    <section class="content">
      <div class="container-fluid">        
        <!-- /.row (main row) -->
        <div class="row">
            <div class="col-12">
              <div class="card">                
                <!-- /.card-header -->
                <div class="card-body">
                  <div class="text-end">
                      
                  </div>                  
                  <div class="table-responsive">
                    <table id="departmentLocation" class="table table-bordered table-hover">
                      <thead>
                      <tr>
                        <th class="text-center" style="display: none">Updated At</th>
                        <th>{{trans('labels.department.locationname')}}</th>
                        <th>{{trans('labels.department.country')}}</th>
                        <th>{{trans('labels.department.state')}}</th>
                        <th>{{trans('labels.department.time_zone')}}</th>
                        <th>{{trans('labels.department.address')}}</th>
                      </tr>
                      </thead>
                      <tbody>
                        
                      </tbody>
                    </table>
                  </div>
                </div>
                <!-- /.card-body -->
              </div>
              <!-- /.card -->
            </div>
            <!-- /.col -->
          </div>
          <!-- /.row -->
      </div><!-- /.container-fluid -->
    </section>
@endsection

<!-- include datatable css -->



@section('after-scripts')
    <!-- DataTables -->
    <style type="text/css">
      .hidden{
        display: none;
      }
    </style>
    <script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}">
    </script>
    <script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}">
    </script>
    <script type="text/javascript">
        $(document).ready(function() {
            $(function() {

                $('#departmentLocation').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '{{ route('admin.departments.getLocationList',$id) }}',
                        data: {
                            status: 1,
                            getQueryString: window.location.search
                        },
                    },
                    columns: [
                        {data: 'updated_at', name: 'updated_at' , className: 'hidden'},
                        {data: 'name', name: 'name'},
                        {data: 'teams', name: 'teams'},
                        {data: 'members', name: 'members'},
                        {data: 'actions', name: 'actions', searchable: false, sortable: false}
                    ],
                    paging: true,
                    dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
                    pageLength: {{ $pagination }},
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
                    stateSave: false

                });

                $('#roleGroup').select2({
                    placeholder: "Select Group"
                });
            });
        });

    </script>
@endsection