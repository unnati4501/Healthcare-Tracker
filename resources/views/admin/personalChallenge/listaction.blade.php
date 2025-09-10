@permission('update-personal-challenge')
<a class="action-icon" href="{{route('admin.personalChallenges.edit', $record->id)}}" title="{{ trans('personalChallenge.buttons.tooltips.edit') }}">
    <i class="far fa-edit">
    </i>
</a>
@endauth
@if($activeUserCount <= 0)
@permission('delete-personal-challenge')
<a class="action-icon danger" data-id="{{$record->id}}" href="javaScript:void(0)" id="deleteModal" title="{{ trans('personalChallenge.buttons.tooltips.delete') }}">
    <i class="far fa-trash-alt">
    </i>
</a>
@endauth
@endif
