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
                @if(isset($book) && $book == true)
                @permission('create-sessions')
                <a class="btn btn-primary" href="{{ route('admin.cronofy.sessions.create') }}">
                    <i class="far fa-plus me-3 align-middle">
                    </i>
                    {{ trans('calendly.buttons.book') }}
                </a>
                @endauth
                @endif
                @if(isset($back) && $back == true)
                <a class="btn btn-outline-primary" href="{{ route('admin.cronofy.sessions.index') }}">
                    <i class="far fa-arrow-left me-3 align-middle">
                    </i>
                    {{ trans('Cronofy.session_list.buttons.back') }}
                </a>
                @endif
            </div>
        </div>
    </div>
</div>