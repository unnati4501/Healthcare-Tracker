@if(!empty($record->id))
@if($record->company_count == 0 && $record->default_count == 0)
@permission('update-app-theme')
<a class="action-icon" href="{{ route('admin.app-themes.edit', $record->id) }}" title="{{ trans('buttons.general.tooltip.edit') }}">
    <i aria-hidden="true" class="far fa-edit">
    </i>
</a>
@endauth

@permission('delete-app-theme')
<a class="action-icon danger deleteTheme" data-id="{{ $record->id }}" href="javascript:void(0);" title="{{ trans('buttons.general.tooltip.delete') }}">
    <i aria-hidden="true" class="far fa-trash-alt">
    </i>
</a>
@endauth
@endif
@endif
