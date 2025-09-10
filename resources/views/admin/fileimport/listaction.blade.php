@if($record->is_processed == false && $record->in_process == false)
<a class="action-icon danger" data-id="{{$record->id}}" href="javaScript:void(0)" id="importDelete" title="{{trans('buttons.general.tooltip.delete')}}">
    <i aria-hidden="true" class="far fa-trash-alt">
    </i>
</a>
@endif
