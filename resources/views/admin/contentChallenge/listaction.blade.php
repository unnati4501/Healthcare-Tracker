@permission('manage-content-challenge')
<a class="action-icon" href="{{route('admin.contentChallengeActivity.index', $contentChallengeCategory->id)}}" title="{{ trans('contentChallgent.buttons.tooltips.view') }}">
    <i class="far fa-edit">
    </i>
</a>
@endauth
