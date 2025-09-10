@extends('layouts.app')

@section('after-styles')
<!-- DataTables -->
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
@endsection

@section('content')

@include('admin.course.week.breadcrumb',['mainTitle' => trans('labels.course.manage_module'), 'back' => 'course'])
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
                {{ Form::open(['route' => ['admin.courses.manageModules', $course->getKey()], 'class' => 'form-horizontal', 'method'=>'get','role' => 'form', 'id'=>'courseSearch']) }}
                <div class="row">
                    <div class="col-md-6 offset-md-3">
                        <div class="input-group">
                        {{ Form::text('recordName', request()->get('recordName'), ['class' => 'form-control', 'placeholder' => 'Search By Course Module Title', 'id' => 'recordName', 'autocomplete' => 'off']) }}
                        <button type="submit" class="btn btn-primary btn-effect"><i class="fal fa-search me-2"></i>{{trans('labels.buttons.submit')}}</button>
                        <a href="{{ route('admin.courses.manageModules', $course->getKey()) }}" class="btn btn-secondary btn-effect ms-2"><i class="fal fa-undo me-2"></i>{{trans('labels.buttons.reset')}}</a>
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
                  <a class="btn btn-success btn-sm btn-effect" href="{!! route('admin.courses.createModule', $course->getKey()) !!}"><i class="fal fa-plus me-2"></i>{{trans('labels.buttons.add_record')}}</a>
              </div>                  
              <div class="table-responsive">
                <table class="table table-bordered table-hover" id="courseModuleManagment">
                    <thead>
                        <tr>
                            <th class="text-center" style="display: none">
                                Id
                            </th>
                            <th class="text-center no-sort">
                                {{trans('labels.course.module_name')}}
                            </th>
                            <th class="text-center no-sort">
                                {{trans('labels.course.lessions')}}
                            </th>
                            <th>{{trans('labels.course.status')}}</th>
                            <th class="text-center th-btn-4 no-sort">
                                {{trans('labels.buttons.action')}}
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
    </div><!-- /.container-fluid -->
</section>
<!-- /.row -->
<!-- /.container-fluid -->
<div class="modal fade" data-id="0" id="delete-model-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Delete record?
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">
                        ×
                    </span>
                </button>
            </div>
            <div class="modal-body">
                <p class="m-0">
                    Are you sure you want to delete this record?
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger btn-effect m-w-100" id="delete-model-box-confirm" title="Delete" type="button">
                    Delete
                </button>
                <button class="btn btn-effect btn-default m-w-100" data-bs-dismiss="modal" title="Cancel" type="button">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" data-id="0" id="publish-course-module-model-box" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Publish Course Module?
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <span aria-hidden="true">
                        ×
                    </span>
                </button>
            </div>
            <div class="modal-body">
                <p class="m-0">
                    Are you sure, you want to publish the course module?
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success btn-effect m-w-100" id="course-module-model-box-confirm" title="Delete" type="button">
                    Yes
                </button>
                <button class="btn btn-effect btn-default m-w-100" data-bs-dismiss="modal" title="Cancel" type="button">
                    No
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
        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
        });

        $('#courseModuleManagment').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{url("/admin/courses/{$course->id}/getCourseModules")}}",
                data: {
                    status: 1,
                    recordName: $('#recordName').val(),
                    getQueryString: window.location.search
                },
            },
            columns: [
              {data: 'id', name: 'id' , className: 'hidden'},
              {data: 'title', name: 'title'},
              {data: 'totalLessions', name: 'totalLessions', searchable: false},
              {data: 'status', name: 'status', searchable: false, sortable: false},
              {data: 'actions', name: 'actions', sortable:false},
            ],
            paging: true,
            dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
            pageLength: {{ $pagination }},
            lengthChange: false,
            searching: false,
            ordering: true,
            order: [[0, 'ASC']],
            info: true,
            autoWidth: false,
            columnDefs: [{
                targets: 'no-sort',
                orderable: false,
            }, {
                targets: [3,4],
                className: 'text-center',
            }],
            stateSave: false

        });
    });

    $(document).on('click', '#courseDelete', function (t) {
        var deleteConfirmModalBox = '#delete-model-box';
        $(deleteConfirmModalBox).attr("data-id", $(this).data('id'));
        $(deleteConfirmModalBox).modal('show');
    });

    $(document).on('click', '#delete-model-box-confirm', function (e) {
        $('.page-loader-wrapper').show();
        var deleteConfirmModalBox = '#delete-model-box';
        var objectId = $(deleteConfirmModalBox).attr("data-id");

        $.ajax({
            type: 'DELETE',
            url: "{{route('admin.courses.deleteModule','/')}}"+ '/' + objectId,
            data: null,
            crossDomain: true,
            cache: false,
            contentType: 'json',
            success: function (data) {
                $('#courseModuleManagment').DataTable().ajax.reload(null, false);
                if (data['deleted'] == 'true') {
                    toastr.success("File deleted");
                } else {
                    toastr.error("delete error.");
                }
                var deleteConfirmModalBox = '#delete-model-box';
                $(deleteConfirmModalBox).modal('hide');
                $('.page-loader-wrapper').hide();
            },
            error: function (data) {
                if (data == 'Forbidden') {
                    toastr.error("delete error.");
                }
                var deleteConfirmModalBox = '#delete-model-box';
                $(deleteConfirmModalBox).modal('hide');
                $('.page-loader-wrapper').hide();
            }
        });
    });

    $(document).on('click', '#publishCourseModule', function(t) {
        $('#publish-course-module-model-box').data("id", $(this).data('id'));
        $('#publish-course-module-model-box').modal('show');
    });


    $(document).on('click', '#course-module-model-box-confirm', function(e) {
          var _this = $(this);
          _this.prop('disabled', 'disabled');
          var objectId = $('#publish-course-module-model-box').data("id");
          $.ajax({
              type: 'POST',
              url: "{{ route('admin.courses.publishModule', '/') }}" + `/${objectId}`,
              crossDomain: true,
              cache: false,
              contentType: 'json'
          })
          .done(function(data) {
              $('#courseModuleManagment').DataTable().ajax.reload(null, false);
              if (data.published == true) {
                  toastr.success(data.message);
              } else {
                  toastr.error(data.message);
              }
          })
          .fail(function(data) {
              if (data == 'Forbidden') {
                  toastr.error("Failed to publish course module.");
              }
          })
          .always(function() {
              _this.removeAttr('disabled');
              $('#publish-course-module-model-box').modal('hide');
          });
      });
</script>
@endsection
