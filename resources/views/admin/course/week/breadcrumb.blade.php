
<div class="content-header">
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-6 order-last order-sm-first">            
        <h1 class="m-0 text-dark">{{ $mainTitle }}</h1>
      </div><!-- /.col -->
      <div class="col-sm-6 d-flex justify-content-sm-end align-items-center order-first order-sm-last">
        @if(!empty($back) && $back == 'course')
          <div class="text-end">
              <a class="btn btn-primary btn-sm btn-effect" href="{!! route('admin.courses.index') !!}"><i class="fal fa-back"></i>Back</a>
          </div>
        @elseif(!empty($back) && $back == 'course_week' && empty($edit))
          <div class="text-end">
              <a class="btn btn-primary btn-sm btn-effect" href="{!! route('admin.courses.manageModules', $course->getKey()) !!}"><i class="fal fa-back"></i>Back</a>
          </div>
        @elseif(!empty($back) && $back == 'course_week' && !empty($edit))
          <div class="text-end">
              <a class="btn btn-primary btn-sm btn-effect" href="{!! route('admin.courses.manageModules', $record->course_id) !!}"><i class="fal fa-back"></i>Back</a>
          </div>
        @endif
        <!-- <div>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="JavaScript: void(0);">Manage User</a></li>
              <li class="breadcrumb-item active">Role Management</li>
            </ol>
        </div>     -->                    
      </div>
    </div>
  </div>
</div>