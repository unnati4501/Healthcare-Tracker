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
            	@if($introduction)
                @permission('support-introduction')
	                <a class="btn btn-primary" href="{!! route('admin.support.introduction') !!}">
	                    <i class="fas fa-info-circle me-2">
	                    </i>
	                    <span class="align-middle">{{trans('eap.buttons.eap_introduction')}}</span>
	                </a>
                @endauth
               	@endif
                @if($create)
                @permission('create-support')
	                <a class="btn btn-primary" href="{!! route('admin.support.create') !!}">
	                    <i class="far fa-plus me-3 align-middle">
	                    </i>
	                    <span class="align-middle">
	                    {{trans('eap.buttons.add_eap')}}
	                	</span>
	                </a>
                @endauth
                @endif
            </div>
        </div>
    </div>
</div>