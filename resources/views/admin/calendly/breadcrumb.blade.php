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
                @permission('manage-sessions')
                @if(isset($book) && $book == true)
                <a class="btn btn-primary" href="https://calendly.com/event_types/all_users_and_teams" target="_blank">
                    <i class="far fa-plus me-3 align-middle">
                    </i>
                    {{ trans('calendly.buttons.book') }}
                </a>
                @endif
                @if(isset($back) && $back == true)
                <a class="btn btn-outline-primary" href="{{ route('admin.sessions.index') }}">
                    <i class="far fa-arrow-left me-3 align-middle">
                    </i>
                    {{ trans('calendly.buttons.back') }}
                </a>
                @endif
                @endauth
            </div>
        </div>
    </div>
</div>