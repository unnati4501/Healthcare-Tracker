<div class="content-header">
    <div class="container-fluid">
        <div class="d-md-flex justify-content-between">
            <div class="align-self-center">
                <h1>
                    {{ $mainTitle }}
                </h1>
                @if(!empty($breadcrumb))
                    {{ Breadcrumbs::render($breadcrumb) }}
                @endif
            </div>
            @if($create)
            @permission('create-location')
            <a class="btn btn-primary" href="{!! route('admin.locations.create') !!}">
                <i class="fal fa-plus me-2">
                </i>
                {{ trans('location.buttons.add_location') }}
            </a>
            @endauth
            @endif
        </div>
    </div>
</div>