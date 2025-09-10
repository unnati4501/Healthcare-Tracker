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
            <div class="align-self-center">
                <a class="btn btn-primary" href="{!! route('admin.domains.create') !!}"><i class="fal fa-plus me-2"></i>{{trans('domain.buttons.add_domain')}}</a>
            </div>
            @endif
            <!-- /.col -->
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->
</div>