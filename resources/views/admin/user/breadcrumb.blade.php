<div class="content-header">
    <div class="container-fluid">
        <div class="d-md-flex justify-content-between">
            <div class="align-self-center">
                <h1>
                    {{ $mainTitle }}
                </h1>
                @if(!empty($breadcrumb))
                    {!! $breadcrumb !!}
                @endif
            </div>
            <div class="align-self-center">
                @if(isset($create) && $create == true)
                @if(request()->has('referrer') && request()->get('referrer') == 'teams')
                <a class="btn btn-outline-primary" href="{!! route('admin.teams.index') !!}">
                    <i class="far fa-arrow-left me-3 align-middle">
                    </i>
                    {{ trans('user.buttons.back_to_team') }}
                </a>
                @else
                @permission('create-user')
                <a class="btn btn-primary" href="{{ route('admin.users.create') }}">
                    <i class="far fa-plus me-3 align-middle">
                    </i>
                    <span class="align-middle">
                        {{ trans('user.buttons.add') }}
                    </span>
                </a>
                @endauth
                @endif
                @endif
                @if(isset($back) && $back == true)
                <a class="btn btn-outline-primary" href="{{ route('admin.users.index') }}">
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