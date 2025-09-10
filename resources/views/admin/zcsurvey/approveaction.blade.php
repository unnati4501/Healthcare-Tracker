@if($record->status == 1)
	<span class='badge badge-pill badge-success'>{{ trans('labels.recipe.approved') }}</span>
@else
	@if(\Auth::user()->roles->first()->group == "company" && $record->company_id == \Auth::user()->company->first()->id)
		<a class="btn btn-icon btn-sm btn-outline-primary animated bounceIn slow delete-toast" data-id="{{$record->id}}" href="javaScript:void(0)" id="recipeApprove" title="Approve Recipe">Approve</a>
	@else
		<span class='badge badge-pill badge-warning'>{{ trans('labels.recipe.pending') }}</span>
	@endif
@endif