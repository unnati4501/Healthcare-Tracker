<div class="content-header">
    <div class="container-fluid">
        <div class="d-md-flex justify-content-between">
            <div class="align-self-center">
                <h1>{{ $mainTitle }}</h1>
                @if(!empty($breadcrumb))
                    {{ Breadcrumbs::render($breadcrumb) }}
                @endif
            </div>
            <div class="align-self-center">
            @if($create)
            @permission('add-webinar')
            <a class="btn btn-primary" href="{!! route('admin.webinar.create') !!}">
                <i class="fal fa-plus me-2">
                </i>
                {{trans('webinar.buttons.add_webinar')}}
            </a>
            @endauth
            @endif
            </div>
        </div>
    </div>
</div>