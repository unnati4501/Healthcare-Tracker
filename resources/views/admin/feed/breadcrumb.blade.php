<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="d-md-flex justify-content-between">
            <div class="align-self-center">
                <h1>{{ $appPageTitle }}</h1>
                @if(!empty($breadcrumb))
                    {{ Breadcrumbs::render($breadcrumb) }}
                @endif
            </div>
            <div class="align-self-center">
                @if($create)
                @permission('create-story')
                  <a class="btn btn-primary" href="{{ route('admin.feeds.create') }}">
                      <i class="fal fa-plus me-2">
                      </i>
                      {{trans('feed.buttons.add_feed')}}
                  </a>
                  @endauth
                @endif
                @if($edit)
                <a class="btn btn-primary" href="{!! route('admin.feeds.edit',$feedData->id) !!}">
                    <i class="far fa-edit me-3 align-middle">
                    </i>
                    <span class="align-middle">
                        {{ trans('buttons.general.edit') }}
                    </span>
                </a>
                @endif
                @if($back)
                <a href="{{ route('admin.feeds.index') }}" class="btn btn-outline-primary"><i class="far fa-arrow-left me-3 align-middle"></i>  <span class="align-middle">{{trans('feed.buttons.back')}}</span></a>
                @endif
            </div>
        </div>
    </div>
</div>