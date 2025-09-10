@permission('update-content-challenge')
<a class="action-icon edit_activity" href="javascript:void(0)" id="edit_activity_{{$activity->id}}" data-id="{{$activity->id}}" title="{{ trans('contentChallenge.buttons.tooltips.edit') }}">
    <i class="far fa-edit">
    </i>
</a>


<a class="action-icon save_activity" style="display:none;" id="save_activity_{{$activity->id}}" data-id={{$activity->id}} href="javascript:void(0);" title="{{ trans('contentChallenge.buttons.tooltips.save') }}">
    <i class="far fa-save">
    </i>
</a>
@endauth