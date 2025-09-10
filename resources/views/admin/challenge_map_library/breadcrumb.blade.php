<div class="content-header">
    <div class="container-fluid">
        <div class="d-md-flex justify-content-between">
            <div class="align-self-center">
                <h1>
                    {{ $mainTitle }}
                </h1>
                {{ Breadcrumbs::render($breadcrumb) }}
            </div>
            <div class="align-self-center">
                @if(isset($create) && $create == true)
                @permission('add-challenge-map')
                <a class="btn btn-primary" href="{!! route('admin.challengeMapLibrary.create') !!}">
                    <i class="far fa-plus me-3 align-middle">
                    </i>
                    <span class="align-middle">
                        {{ trans('challengeMap.buttons.add') }}
                    </span>
                </a>
                @endauth
                @endif
            </div>
        </div>
    </div>
</div>