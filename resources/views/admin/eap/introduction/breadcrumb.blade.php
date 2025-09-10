<div class="content-header">
    <div class="container-fluid">
        <div class="d-md-flex justify-content-between">
        	<div class="align-self-center">
	            <h1 class="align-self-center">
	                {{ $mainTitle }}
	            </h1>
	            @if(!empty($breadcrumb))
	                {{ Breadcrumbs::render($breadcrumb) }}
	            @endif
        	</div>
        	<div class="align-self-center">
        		@if($back)
                <a href="{{ route('admin.support.list') }}" class="btn btn-outline-primary"><i class="far fa-arrow-left me-3 align-middle"></i>  <span class="align-middle">{{trans('buttons.general.back')}}</span></a>
                @endif
        	</div>
        </div>
    </div>
</div>