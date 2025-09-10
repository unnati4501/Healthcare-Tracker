@if(!empty($record->id))
@permission('update-meditation-category')
<a class="btn btn-sm btn-outline-primary animated bounceIn slow" href="{{route('admin.meditationcategorys.edit', $record->id)}}" title="{{trans('labels.buttons.edit_record')}}">
    <i aria-hidden="true" class="fal fa-pencil-alt"></i>
</a>
@endauth
@permission('delete-meditation-category')
@if($record->tracks->count() == 0)
	<a href="javaScript:void(0)" class="btn btn-sm btn-outline-danger animated bounceIn slow delete-toast" title="{{trans('labels.buttons.delete_record')}}" data-id="{{$record->id}}" id="meditationcategoryDelete">
	    <i class="fal fa-trash-alt" aria-hidden="true" ></i>
	</a>
@endif
@endauth
@endif