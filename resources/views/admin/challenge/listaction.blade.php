@php
    $role = getUserRole();
@endphp
@if($record->company_id == $companyId)
@if(!empty($record->id))
@permission('add-points')
@if($start_date <= $now && $endDate >= $now && !$record->cancelled)
<a class="action-icon" href="{{route('admin.'.$route.'.addPoints', $record->id)}}" title="{{ trans('challenges.buttons.tooltips.points') }}">
    <i aria-hidden="true" class="far fa-plus">
    </i>
</a>
@endif
@endauth
@permission('add-points-for-inter-company-challenge')
@if($start_date <= $now && $endDate >= $now && !$record->cancelled)
<a class="action-icon" href="{{route('admin.interCompanyChallenges.addPoints', $record->id)}}" title="{{ trans('challenges.buttons.tooltips.points') }}">
    <i aria-hidden="true" class="far fa-plus">
    </i>
</a>
@endif
@endauth
@permission('update-challenge')
@if($endDate >= $now && $record->creator_id == \Auth::user()->id && !$record->cancelled)
<a class="action-icon" href="{{route('admin.'.$route.'.edit', $record->id)}}" title="{{ trans('challenges.buttons.tooltips.edit') }}">
    <i aria-hidden="true" class="far fa-edit">
    </i>
</a>
@endif
@if($endDate >= $now && !$record->cancelled)
<a class="action-icon danger font-16" href="javaScript:void(0)" id="challengeCancel" data-id="{{ $record->id }}" title="{{ trans('challenges.buttons.tooltips.cancel_challenge') }}">
    <i aria-hidden="true" class="far fa-times-square">
    </i>
</a>
@endif
@endauth
@permission('update-inter-company-challenge')
@if($endDate >= $now && $record->creator_id == \Auth::user()->id && !$record->cancelled)
<a class="action-icon" href="{{route('admin.interCompanyChallenges.edit', $record->id)}}" title="{{ trans('challenges.buttons.tooltips.edit') }}">
    <i aria-hidden="true" class="far fa-edit">
    </i>
</a>
@endif
@if($endDate >= $now && !$record->cancelled)
<a class="action-icon danger font-16" href="javaScript:void(0)" id="challengeCancel" data-id="{{ $record->id }}" title="{{ trans('challenges.buttons.tooltips.cancel_challenge') }}">
    <i aria-hidden="true" class="far fa-times-square">
    </i>
</a>
@endif
@endauth
@permission('delete-challenge')
@if($record->creator_id == \Auth::user()->id && !$record->cancelled)

@if(!($start_date <= $now && $endDate >= $now))
<a class="action-icon danger" data-id="{{$record->id}}" href="javaScript:void(0)" id="challengeDelete" title="{{ trans('challenges.buttons.tooltips.delete') }}">
    <i aria-hidden="true" class="far fa-trash-alt">
    </i>
</a>
@elseif($record->challenge_type == 'individual')
<a class="action-icon danger" data-id="{{$record->id}}" href="javaScript:void(0)" id="challengeDelete" title="{{ trans('challenges.buttons.tooltips.delete') }}">
    <i aria-hidden="true" class="far fa-trash-alt">
    </i>
</a>
@endif

@endif
@endauth
@if($role->group == 'zevo')
@permission('delete-inter-company-challenge')
@if($record->creator_id == \Auth::user()->id && !$record->cancelled && !($start_date <= $now && $endDate >= $now))
<a class="action-icon danger" data-id="{{$record->id}}" href="javaScript:void(0)" id="challengeDelete" title="{{ trans('challenges.buttons.tooltips.delete') }}">
    <i aria-hidden="true" class="far fa-trash-alt">
    </i>
</a>
@endif
@endauth
@permission('export-inter-company-challenge')
@if($record->creator_id == \Auth::user()->id && !$record->cancelled && ($start_date <= $now))
<a class="action-icon" data-end="{{$endDate}}" data-id="{{$record->id}}" data-start="{{$start_date}}" href="javaScript:void(0)" id="challengeExport" title="{{ trans('challenges.buttons.tooltips.export') }}">
    <i aria-hidden="true" class="far fa-download">
    </i>
</a>
@endif
@endauth
@endif
@endif
@endif
