<div class="col-xl-10 offset-xl-1 mt-4" id="location_team_{{ $id }}">
    <div class="row">
        <div class="form-group col-md-5">
            {{ Form::label("employee_count_{$id}", trans('labels.department.no_of_employee') . " ({$locationName})") }}
            {{ Form::text("employee_count[{$id}]", $no_of_employee, ['class' => "form-control emp-count {$ecClass}", 'placeholder' => trans('labels.department.no_of_employee'), 'id' => "employee_count_{$id}", 'min' => 1, 'max' => 10000, 'disabled' => $disabled]) }}
        </div>
        <div class="form-group col-md-2">
            {{ Form::label('name', trans('labels.department.possible_teams')) }}
            <p class="text-center" id="possible_teams_{{ $id }}">
                {{ $possible_teams }}
            </p>
        </div>
        <div class="form-group col-md-5">
            {{ Form::label("naming_convention_{$id}", trans('labels.department.naming_convention')) }}
            {{ Form::text("naming_convention[{$id}]", $name_convention, ['class' => "form-control show-suggestions {$ncClass}", 'placeholder' => trans('labels.department.naming_convention'), 'id' => "naming_convention_{$id}", 'disabled' => $disabled, "data-id" => $id]) }}
            <p class="grey" id="suggestion_{{ $id }}">
            </p>
        </div>
    </div>
</div>