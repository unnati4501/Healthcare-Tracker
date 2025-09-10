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
            @if($changeappsetting)
            <div class="align-self-center">
              <a class="btn btn-primary" href="{!! route('admin.appsettings.changeSettings') !!}">
                  <i class="far fa-cog me-3 align-middle"></i> {{trans('appsettings.buttons.change_settings_btn')}}
              </a>
            </div>
            @endif
        </div>
    </div>
</div>