@if(!is_null($record->is_favorite) && ($record->is_favorite == 1))
<a class="action-icon set-favorite" data-id="{{ $record->id }}" href="javascript:void(0);" title="{{ trans('survey.feedback.labels.unfavorite') }}">
    <i class="fas fa-star">
    </i>
</a>
@else
<a class="action-icon set-favorite" data-id="{{ $record->id }}" href="javascript:void(0);" title="{{ trans('survey.feedback.labels.favorite') }}">
    <i class="far fa-star">
    </i>
</a>
@endif
