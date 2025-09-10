@extends('layouts.app')

@section('after-styles')
<!-- DataTables -->
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
@endsection

@section('content')

    @include('admin.badge.breadcrumb',['appPageTitle' => trans('labels.badge.details')." For - ".$badge->title])
<section class="content">
    <div class="container-fluid">
        <!-- /.row (main row) -->
        <div class="row">
          <!-- Left col -->
          <section class="col-lg-12">

            <!-- DIRECT CHAT -->
            <div class="card collapsed-card">
              <div class="card-header" data-widget="collapse">
                <h3 class="card-title">Search</h3>

                <div class="card-tools">                  
                  <button type="button" class="btn btn-tool" data-widget="collapse">
                      <i class="fa fa-chevron-up"></i>
                    </button>
                  </button>
                </div>
              </div>
              <!-- /.card-header -->
              <div class="card-body">                
                {{ Form::open(['route' => ['admin.badges.details',$badge->id], 'class' => 'form-horizontal', 'method'=>'get','role' => 'form', 'id'=>'userSearch']) }}
                  <div class="row">
                      <div class="form-group col-md-4 offset-md-2">
                        {{ Form::text('recordName', request()->get('recordName'), ['class' => 'form-control', 'placeholder' => 'Search By Full Name', 'id' => 'recordName', 'autocomplete' => 'off']) }}
                      </div>
                  </div>
                  <div class="text-center">
                      <button type="submit" class="btn btn-primary btn-effect me-2"><i class="fal fa-search"></i> {{trans('labels.buttons.submit')}}</button>
                      
                      <a href="{{ route('admin.badges.details',$badge->id) }}" class="btn btn-primary btn-effect"><i class="fal fa-undo"></i>{{trans('labels.buttons.reset')}}</a>
                  </div>
                  
                {{ Form::close() }}               
              </div>
              <!-- /.card-body -->
              
              <!-- /.card-footer-->
            </div>
            <!--/.direct-chat -->
          </section>
          <!-- /.Left col -->
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <!-- /.card-header -->
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="membersManagment">
                                <thead>
                                    <tr>
                                        <th>
                                            Name
                                        </th>
                                        <th>
                                            Awarded On
                                        </th>
                                        <th>
                                            Status
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
    var tzUrl = '{{ route("admin.badges.getMembersList", ":id") }}';
    var companyId = '{{ $badge->id }}';
    $(document).ready(function() {
      $('#membersManagment').DataTable({
          processing: true,
          serverSide: true,
          ajax: {
              url: tzUrl.replace(':id', companyId),
              data: {
                  status: 1,
                  recordName: $('#recordName').val(),
                  getQueryString: window.location.search
              },
          },
          columns: [
              {data: 'name', name: 'name'},
              {data: 'awardedon', name: 'awardedon'},
              {data: 'status', name: 'status'},
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
  });
</script>
@endsection
