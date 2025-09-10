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
            @if($create)
            @permission('create-badge')
            <div class="align-self-center">
              <a class="btn btn-primary" href="{!! route('admin.badges.create') !!}">
                  <i class="far fa-plus me-3 align-middle">
                  </i>
                  {{trans('badge.buttons.add_badge')}}
              </a>
            </div>
            @endauth
            @endif
            @if($back)
                <a href="{{ route('admin.badges.index') }}" class="btn btn-outline-primary"><i class="far fa-arrow-left me-3 align-middle"></i>  <span class="align-middle">{{trans('buttons.general.back')}}</span></a>
            @endif
        </div>
    </div>
</div>