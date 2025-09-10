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
            <div class="align-self-center">
                @if(isset($create) && $create == true)
                @permission('create-role')
                <a class="btn btn-primary" href="{{ route('admin.roles.create') }}">
                    <i class="far fa-plus me-3 align-middle">
                    </i>
                    <span class="align-middle">
                        {{ trans('roles.buttons.add') }}
                    </span>
                </a>
                @endauth
                @endif
                @if(isset($back) && $back == true)
                <a class="btn btn-primary" href="{{ route('admin.roles.index') }}">
                    <i class="far fa-arrow-left me-3 align-middle">
                    </i>
                    <span class="align-middle">
                        {{ trans('buttons.general.back') }}
                    </span>
                </a>
                @endif
            </div>
        </div>
    </div>
</div>