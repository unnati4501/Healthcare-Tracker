@permission('draft-published-event')
@if($record->status == 2)
<span class="text-success">
    {{ $status['text'] }}
</span>
@else
<a class="btn btn-primary badge-btn" data-action="publish" data-id="{{ $record->id }}" href="javascript:void(0);" id="publishEvent">
    {{ trans('event.buttons.publish') }}
</a>
@endif
@endauth
