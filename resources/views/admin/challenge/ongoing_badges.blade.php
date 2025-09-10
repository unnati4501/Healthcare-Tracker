<tr>
    <td class="th-btn-4">
        <div class="form-group mb-0 target_completed">
            @if(!empty($challengeData) && $challengeData->challengeRules[0]->challenge_target_id == 2)
            {{ Form::label('distance_completed', trans('challenges.form.labels.distance_completed')) }}
            @else
            {{ Form::label('steps_completed', trans('challenges.form.labels.steps_completed')) }}
            @endif
            {{ Form::text("target[]", $target, ['class' => 'form-control target_required on_type_required', 'placeholder' => trans('challenges.form.placeholders.target'), 'onkeypress' => "return isNumber(event)", 'disabled' => $allowTargetUnitEdit]) }}
        </div>
    </td>
    <td class="th-btn-4">
        <div class="form-group mb-0">
            {{ Form::label('In days', trans('challenges.form.labels.in_days')) }}
            {{ Form::text("in_days[]", $inDays, ['class' => 'form-control indays_required on_type_required', 'placeholder' => trans('challenges.form.placeholders.in_days'), 'onkeypress' => "return isNumber(event)", 'disabled' => $allowTargetUnitEdit]) }}
        </div>
    </td>
    <td class="th-btn-6">
        <div class="form-group mb-0">
            {{ Form::label('Badges', trans('challenges.form.labels.badge')) }}
            {{ Form::select('badge[]', $ongoingBadges, $badge, ['class' => 'select2 form-control ongoing-badge badges_required',"style"=>"width: 100%;", 'placeholder' => '', 'data-placeholder' => trans('challenges.form.placeholders.select_badge'), 'autocomplete' => 'off', 'disabled' => $allowTargetUnitEdit] ) }}
        </div>
    </td>
    @if(!$allowTargetUnitEdit)
    <td class="th-btn-sm">
        <a class="action-icon text-danger ongoing-badge-remove mt-4" href="javascript:void(0);" title="{{ trans('challenges.buttons.tooltips.remove_ongoing_badge') }}">
            <i class="far fa-trash">
            </i>
        </a>
        <a class="action-icon text-success mt-4" href="javascript:void(0);" id="ongoingBadgeAdd" title="{{ trans('challenges.buttons.tooltips.add_ongoing_badge') }}">
            <i class="far fa-plus">
            </i>
        </a>
    </td>
    @endif
</tr>