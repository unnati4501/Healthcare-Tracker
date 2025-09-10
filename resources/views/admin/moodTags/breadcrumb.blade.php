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
                @permission('create-mood-tags')
                <a class="btn btn-primary" href="{!! route('admin.moodTags.create') !!}">
                    <i class="far fa-plus me-3 align-middle">
                    </i>
                    {{ trans('moods.tags.buttons.add') }}
                </a>
                @endauth
                @endif
            </div>
        </div>
    </div>
</div>