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
                @if($route != 'interCompanyChallenges')
                @permission('create-challenge')
                <a class="btn btn-primary" href="{!! route('admin.' . $route . '.create') !!}">
                    <i class="far fa-plus me-3 align-middle">
                    </i>
                    {{ trans('challenges.buttons.add') }}
                </a>
                @endauth
                @endif
                @if($route == 'interCompanyChallenges')
                @permission('create-inter-company-challenge')
                <a class="btn btn-primary" href="{!! route('admin.interCompanyChallenges.create') !!}">
                    <i class="far fa-plus me-3 align-middle">
                    </i>
                    {{ trans('challenges.buttons.add') }}
                </a>
                @endauth
                @endif
                @endif
                @if(isset($back) && $back == true)
                <a class="btn btn-outline-primary" href="{!! route('admin.' . $route . '.index') !!}">
                    <i class="far fa-arrow-left me-3 align-middle">
                    </i>
                    <span class="align-middle">
                        {{ trans('labels.buttons.back') }}
                    </span>
                </a>
                @endif
            </div>
        </div>
    </div>
</div>