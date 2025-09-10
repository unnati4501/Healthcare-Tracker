@extends('layouts.app')

@section('after-styles')
    <!-- DataTables -->
    <link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
@endsection

@section('content')

    @include('admin.meditationcategory.breadcrumb',['appPageTitle' => trans('labels.meditationcategory.index_title')])

    <section class="content">
      <div class="container-fluid">        
        <!-- Main row -->
        <div class="row">
          <!-- Left col -->
          <section class="col-lg-12">

            <!-- DIRECT CHAT -->
            <div class="card collapsed-card">
              <div class="card-header" data-widget="collapse">
                <h3 class="card-title">{{trans('labels.common_title.search')}}</h3>

                <div class="card-tools">                  
                  <button type="button" class="btn btn-tool" data-widget="collapse">
                      <i class="fa fa-chevron-up"></i>
                    </button>
                  </button>
                </div>
              </div>
              <!-- /.card-header -->
              <div class="card-body">                
                {{ Form::open(['route' => 'admin.meditationcategorys.index', 'class' => 'form-horizontal', 'method'=>'get','role' => 'form', 'id'=>'categorySearch']) }}
                  <div class="row">
                    <div class="col-md-6 offset-md-3">
                      <div class="input-group">
                        {{ Form::text('categoryName', request()->get('categoryName'), ['class' => 'form-control', 'placeholder' => 'Search By Name', 'id' => 'categoryName', 'autocomplete' => 'off']) }}
                        <button type="submit" class="btn btn-primary btn-effect"><i class="fal fa-search me-2"></i>{{trans('labels.buttons.submit')}}</button>
                        <a href="{{ route('admin.meditationcategorys.index') }}" class="btn btn-secondary btn-effect ms-2"><i class="fal fa-undo me-2"></i>{{trans('labels.buttons.reset')}}</a>
                      </div>
                    </div>
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
        <!-- /.row (main row) -->
        <div class="row">
            <div class="col-12">
              <div class="card">                
                <!-- /.card-header -->
                <div class="card-body">
                  <div class="text-end">
                      @permission('create-meditation-category')
                      <a class="btn btn-success btn-sm btn-effect" href="{!! route('admin.meditationcategorys.create') !!}"><i class="fal fa-plus me-2"></i>{{trans('labels.buttons.add_record')}}</a>
                      @endauth
                  </div>                  
                  <div class="table-responsive">
                    <table id="meditationcatManagment" class="table table-bordered table-hover">
                      <thead>
                      <tr>
                        <th class="text-center" style="display: none">Updated At</th>
                        <th>Logo</th>
                        <th>{{trans('labels.meditationcategory.category_name')}}</th>
                        <th>{{trans('labels.meditationcategory.total_track')}}</th>
                        <th class="text-center th-btn-2 no-sort">{{trans('labels.buttons.action')}}</th>
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
<div class="modal fade" data-id="0" id="delete-model-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Delete Meditation Category?
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                <span aria-hidden="true">
                    ×
                </span>
                </button>
            </div>
            <div class="modal-body">
                <p class="m-0">
                    Are you sure you want to delete this meditation category?
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger btn-effect m-w-100" id="delete-model-box-confirm" title="Delete"
                        type="button">
                    {{trans('labels.buttons.delete')}}
                </button>
                <button class="btn btn-effect btn-default m-w-100" data-bs-dismiss="modal" title="Cancel"
                        type="button">
                    {{trans('labels.buttons.cancel')}}
                </button>
            </div>
        </div>
    </div>
</div>
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

                $('#meditationcatManagment').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '{{ route('admin.meditationcategorys.getMeditationCategorys') }}',
                        data: {
                            status: 1,
                            categoryName: $('#categoryName').val(),
                            getQueryString: window.location.search
                        },
                    },
                    columns: [
                        {data: 'updated_at', name: 'updated_at' , className: 'hidden'},
                        {data: 'logo', name: 'logo', searchable: false, sortable: false},
                        {data: 'title', name: 'title'},
                        {data: 'total_track', name: 'total_track'},
                        {data: 'actions', name: 'actions', searchable: false, sortable: false}
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
                    },{
                        targets: 1,
                        className: 'text-center',
                    }, {
                        targets: 4,
                        className: 'text-center',
                    }],
                    stateSave: false

                });
            });
        });


        $(document).on('click', '#meditationcategoryDelete', function (t) {
            var deleteConfirmModalBox = '#delete-model-box';
            $(deleteConfirmModalBox).attr("data-id", $(this).data('id'));
            $(deleteConfirmModalBox).modal('show');
        });
        $(document).on('click', '#delete-model-box-confirm', function (e) {
            var deleteConfirmModalBox = '#delete-model-box';
            var objectId = $(deleteConfirmModalBox).attr("data-id");

            $.ajaxSetup({
              headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              }
            });

            $.ajax({
                type: 'DELETE',
                url: "{{route('admin.meditationcategorys.delete','/')}}"+ '/' + objectId,
                data: null,
                crossDomain: true,
                cache: false,
                contentType: 'json',
                success: function (data) {
                    $('#meditationcatManagment').DataTable().ajax.reload(null, false);
                    if (data['deleted'] == 'true') {
                        toastr.success("Meditation Category deleted");
                    } else if(data['deleted'] == 'use') {
                        toastr.error("The Meditation Category is in use!");
                    } else {
                        toastr.error("Unable to delete meditation category data.");
                    }
                    var deleteConfirmModalBox = '#delete-model-box';
                    $(deleteConfirmModalBox).modal('hide');
                },
                error: function (data) {
                    $('#meditationcatManagment').DataTable().ajax.reload(null, false);
                    toastr.error("Unable to delete meditation category data.");
                    var deleteConfirmModalBox = '#delete-model-box';
                    $(deleteConfirmModalBox).modal('hide');
                }
            });
        });

    </script>
@endsection