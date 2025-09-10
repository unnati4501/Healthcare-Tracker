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
                @permission('create-meditation-library')
                <a class="btn btn-primary" href="{{ route('admin.meditationtracks.create') }}">
                    <i class="far fa-plus me-3 align-middle">
                    </i>
                    <span class="align-middle">
                        {{ trans('meditationtrack.buttons.add') }}
                    </span>
                </a>
                @endauth
                @endif
            </div>
        </div>
    </div>
</div>