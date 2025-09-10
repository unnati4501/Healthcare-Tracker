@if(!empty($record->id))
@permission('view-group')
<a class="action-icon" href="{{route('admin.groups.details', $record->id)}}" title="{{trans('buttons.general.tooltip.view')}}">
    <i aria-hidden="true" class="far fa-eye"></i>
</a>
@endauth
@if(is_null(Auth::user()->company->first()) || (!is_null(Auth::user()->company->first()) && $record->company_id == Auth::user()->company->first()->id) || ($record->company_id == null))
@if($record->created_by == 'Admin')
@permission('update-group')
<a class="action-icon" href="{{route('admin.groups.edit', $record->id)}}" title="{{trans('buttons.general.tooltip.edit')}}">
    <i aria-hidden="true" class="far fa-edit"></i>
</a>
@endauth
@endif
@if(!in_array($record->subcategory->short_name, ['challenge','masterclass']) || (in_array($record->subcategory->short_name, ['challenge']) && $record->is_archived))
@permission('delete-group')
<a href="javaScript:void(0)" class="action-icon delete-toast danger" title="{{trans('buttons.general.tooltip.delete')}}" data-id="{{$record->id}}" id="groupDelete">
    <i class="far fa-trash-alt" aria-hidden="true" ></i>
</a>
@endauth
@endif
@if($record->groupReports->count() > 0)
<a class="action-icon" href="{{route('admin.groups.reportAbuse', $record->id)}}" title="{{trans('buttons.general.tooltip.reportabuse')}}">
    <i aria-hidden="true" class="far fa-signal"></i>
</a>
@endif
@endif
@endif