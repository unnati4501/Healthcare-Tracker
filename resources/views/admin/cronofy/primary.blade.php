@if($record->primary)
    <label>Primary</label>
@else
<a class="btn btn-primary badge-btn primary-calendar" data-action="primary" data-id="{{$record->id}}" href="javascript:;" title="Make Primary">
    Make Primary
</a>
@endif