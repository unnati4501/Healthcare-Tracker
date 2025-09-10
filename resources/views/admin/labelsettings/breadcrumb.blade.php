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
            @if(isset($defautlBtn) && $defautlBtn == true)
            <div class="align-self-center">
                <div class="text-end">
                    <a class="btn btn-primary" href="{{ route('admin.labelsettings.setdefault') }}">
                        {{ trans('labelsettings.buttons.set_default') }}
                    </a>
                </div>
            </div>
            @endif
            @if($changelabel)
            <div class="align-self-center">
                <a class="btn btn-primary" href="{{ route('admin.labelsettings.changelabel') }}">
                    <i class="fal fa-plus me-2">
                    </i>
                    {{trans('labelsettings.buttons.change_label')}}
                </a>
            </div>
            @endif
        </div>
    </div>
</div>