<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="d-md-flex justify-content-between">
            <div class="align-self-center">
                <h1>{{ $appPageTitle . ' of ' . $department->name }}</h1>
                @if(!empty($breadcrumb))
                    {{ Breadcrumbs::render($breadcrumb) }}
                @endif
            </div>
            @permission('create-department')
            <div class="align-self-center">
                <a class="btn btn-outline-primary" href="{{ route('admin.departments.index') }}">
                    <i class="far fa-arrow-left me-3 align-middle">
                    </i>
                    Back
                </a>
            </div>
            @endauth
            <!-- /.col -->
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->
</div>