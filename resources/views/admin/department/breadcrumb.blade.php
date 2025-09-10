<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="d-md-flex justify-content-between">
            <div class="align-self-center">
                <h1>{{ $appPageTitle }}</h1>
                @if(!empty($breadcrumb))
                    {{ Breadcrumbs::render($breadcrumb) }}
                @endif
            </div>
            @if($create)
            @permission('create-department')
            <div class="align-self-center">
                <a href="{!! route('admin.departments.create') !!}" class="btn btn-primary" title="{{trans('department.buttons.add_department')}}"> <i class="far fa-plus me-3 align-middle"></i> <span class="align-middle">{{trans('department.buttons.add_department')}}</span></a>
            </div>
            @endauth
            @endif
            <!-- /.col -->
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->
</div>