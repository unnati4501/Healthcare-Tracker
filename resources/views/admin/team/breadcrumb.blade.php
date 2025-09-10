<div class="content-header">
    <div class="container-fluid">
        <div class="d-md-flex justify-content-between">
            <div class="align-self-center">
                <h1>
                    {{ $appPageTitle }}
                </h1>
                @if(!empty($breadcrumb))
                    {{ Breadcrumbs::render($breadcrumb) }}
                @endif
            </div>
            <div class="align-self-center">
                @if($setLimit)
                @permission('set-team-limit')
                @if($role->group == 'company')
                @if($hasOngoingChallenge)
                <a class="btn btn-primary" data-bs-target="#set-team-limit-model-box" data-bs-toggle="modal" href="javascript:void(0);">
                    <i class="fal fa-cog me-2">
                    </i>
                    {{ trans('labels.buttons.set_limit') }}
                </a>
                @else
                <a class="btn btn-primary" href="{{ route('admin.teams.setTeamLimit') }}">
                    <i class="fal fa-cog me-2">
                    </i>
                    {{ trans('labels.buttons.set_limit') }}
                </a>
                @endif
                @endif
                @endauth
                @endif
                @if($create)
                @permission('create-team')
                <a class="btn btn-primary" href="{{ route('admin.teams.create') }}">
                    <i class="fal fa-plus me-2">
                    </i>
                    {{ trans('team.buttons.add_team') }}
                </a>
                @endauth
                @endif
            </div>
        </div>
    </div>
</div>