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
            @permission('create-app-theme')
            <div class="align-self-center">
            <a class="btn btn-primary" href="{{ route('admin.app-themes.create') }}">
                <i class="fal fa-plus me-2">
                </i>
                {{ trans('appthemes.buttons.add_record') }}
            </a>
            </div>
            @endauth
            @endif
        </div>
    </div>
</div>