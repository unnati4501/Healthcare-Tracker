@permission('add-dt-banners')
<a class="action-icon"  href="{{ route('admin.companies.editBanner', [$companyType, $record->company_id, $record->id]) }}" title="{{trans('buttons.general.tooltip.edit')}}">
    <i aria-hidden="true" class="far fa-edit">
    </i>
</a>
@endauth
@permission('edit-dt-banners')
<a class="action-icon danger bannerDelete" data-id="{{ $record->id }}" href="javascript:void(0);" id="bannerDelete" title="{{trans('buttons.general.tooltip.delete')}}">
    <i aria-hidden="true" class="far fa-trash-alt">
    </i>
</a>
@endauth