@permission('update-challenge-map')
<a class="action-icon" href="{{ route('admin.challengeMapLibrary.edit', $record->id) }}" title="{{ trans('challengeMap.buttons.tooltips.edit') }}">
    <i aria-hidden="true" class="far fa-edit">
    </i>
</a>
@endauth
@permission('manage-challenge-map-library')
<a class="action-icon view-map" data-id="{{$record->id}}" href="javaScript:void(0)" id="view_map_{{$record->id}}" title="{{ trans('challengeMap.buttons.tooltips.view') }}">
    <i aria-hidden="true" class="far fa-eye">
    </i>
</a>
@endauth
@permission('delete-challenge-map')
@if($attechCount <= 0)
<a class="action-icon danger delete-map" data-id="{{ $record->id }}" href="javascript:void(0);" title="{{ trans('challengeMap.buttons.tooltips.delete') }}">
    <i aria-hidden="true" class="far fa-trash-alt">
    </i>
</a>
@endif
@if($activeCount <= 0 && $attechCount > 0)
<a class="action-icon danger archive-map" data-id="{{ $record->id }}" href="javascript:void(0);" title="{{ trans('challengeMap.buttons.tooltips.archive') }}">
    <i aria-hidden="true" class="far fa-archive">
    </i>
</a>
@endif
@endauth