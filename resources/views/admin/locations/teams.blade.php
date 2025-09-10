@extends('layouts.app')

@section('after-styles')
<!-- DataTables -->
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
@endsection

@section('content')

    @include('admin.companies.breadcrumb',['mainTitle' => 'Teams of '.$company->name])
<section class="content">
    <div class="container-fluid">
        <!-- /.row (main row) -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <!-- /.card-header -->
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="teamManagment">
                                <thead>
                                    <tr>
                                        <th>
                                            Team Code
                                        </th>
                                        <th>
                                            Team Name
                                        </th>
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
    </div>
    <!-- /.container-fluid -->
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
    var tzUrl = '{{ route("admin.companies.getCompanyTeams", ":id") }}';
    var companyId = '{{ $company->id }}';
    $(document).ready(function() {
      $('#teamManagment').DataTable({
          processing: true,
          serverSide: true,
          ajax: {
              url: tzUrl.replace(':id', companyId),
              data: {
                  status: 1,
                  getQueryString: window.location.search
              },
          },
          columns: [
              {data: 'code', name: 'code'},
              {data: 'name', name: 'name'},
          ],
          paging: true,
          pageLength: {{ $pagination }},
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
          stateSave: false

      });
  });
</script>
@endsection
