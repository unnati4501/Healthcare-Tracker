@if(!$record->status)
<a class="action-icon" href="{{ route('admin.masterclass.edit', $record->id) }}" title="{{ trans('Cronofy.tooltips.reconnect') }}">
    <i class="far fa-link">
    </i>
</a>
@elseif(!$record->primary && $record->status)
<a class="action-icon unlink-calendar" profileId="{{$record->profile_id}}" href="javascript:;" title="{{ trans('Cronofy.tooltips.disconnect') }}">
    <i class="far fa-unlink">
    </i>
</a>
@else
-
@endif