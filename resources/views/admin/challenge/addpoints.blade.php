@extends('layouts.app')

@section('after-styles').
<link href="{{ asset('assets/plugins/tree-multiselect/tree-multiselect.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/datepicker/datepicker3.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.challenge.breadcrumb', [
  'mainTitle' => $ga_title,
  'breadcrumb' => $route . '.addPoints',
  'back' => true
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        @php
            $currentDate = \now()->setTimezone($timezone)->toDateString();
            $startDate = old('start_date', $challengeData->start_date->setTimezone($timezone)->toDateString());
        @endphp
        {{ Form::open(['route' => ['admin.'.$route.'.managePoints',$challengeData->id], 'class' => 'form-horizontal zevo_form_submit', 'method'=>'PATCH','role' => 'form', 'id'=>'managePoints','files' => true]) }}
        <div class="card form-card">
            <div class="card-body">
                <div class="card-inner">
                    <div class="row">
                        <div class="col-lg-6 col-xl-4">
                            <div class="form-group">
                                {{ Form::label('date', trans('challenges.points.form.labels.date')) }}
                                {{ Form::text('log_date', old('log_date'), ['class' => 'form-control datepicker ', 'id' => 'start_date', 'placeholder' => trans('challenges.points.form.placeholders.date'),  'autocomplete'=>'off']) }}
                            </div>
                        </div>
                        @foreach($challengeData->challengeRules as $key => $value)
                        <div class="col-lg-6 col-xl-4">
                            <div class="form-group">
                                <input name="points_target[]" type="hidden" value="{{$value->challenge_target_id}}"/>
                                {{ Form::label('points', (array_key_exists($value->challenge_target_id , $challenge_targets))? $challenge_targets[$value->challenge_target_id] . ' Points' : "") }}
                                <input class="form-control" id="points" max="100" name="points[]" onkeypress="return isNumberKey(event,this)" placeholder="{{ trans('challenges.points.form.placeholders.points') }}" type="text"/>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <h3 class="card-inner-title">
                        {{ Form::label('participating_user', trans('challenges.points.form.labels.participants')) }}
                    </h3>
                    <div class="row">
                        <div class="col-lg-6 col-xl-12">
                            <div id="setPermissionList" class="tree-multiselect-box mb-4">
                                <select class="form-control" id="group_member" multiple="multiple" name="group_member">
                                    @foreach($participantData as $compGroup => $compData)
                                        @foreach($compData['departments'] as $deptGroup => $deptData)
                                            @foreach($deptData['teams'] as $teamGroup => $teamData)
                                                @foreach($teamData['members'] as $memberGroup => $memberData)
                                                <option 
                                                    value="{{ $memberData['id'] }}" 
                                                    data-section="{{ $compData['name'] }}/{{ $deptData['name'] }}/{{ $teamData['name'] }}" 
                                                    {{ (!empty(old('members_selected')) && in_array($memberData['id'], old('members_selected'))) ? 'selected' : ''   }} >
                                                    {{ $memberData['name'] }}
                                                </option>
                                                @endforeach 
                                            @endforeach    
                                        @endforeach
                                    @endforeach
                                </select>
                            </div>
                            <span id="group_member-error" style="display: none; width: 100%; margin-top: 1rem; font-size: 80%; color: #f44436; margin-left: 1rem;">
                                {{trans('challenges.points.messages.users_required')}}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{!! route('admin.'.$route.'.index') !!}">
                        {{ trans('buttons.general.cancel') }}
                    </a>
                    <button class="btn btn-primary" type="submit">
                        {{ trans('buttons.general.save') }}
                    </button> 
                </div>
            </div>
        </div>
        {{ Form::close() }}
    </div>
</section>
@endsection

@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\ManagePointsRequest','#managePoints') !!}
<script src="{{ asset('assets/plugins/tree-multiselect/tree-multiselect.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/datepicker/bootstrap-datepicker.js?var='.rand()) }}">
</script>
<script type="text/javascript">
    var startDate = '<?php echo $startDate; ?>';
    var currentDate = '<?php echo $currentDate; ?>';
</script>
<script src="{{ mix('js/challenges/points.js') }}">
</script>
<script type="text/javascript">
    function isNumberKey(evt, element) {
        var charCode = (evt.which) ? evt.which : event.keyCode
        if (charCode > 31 && (charCode < 48 || charCode > 57) && !(charCode == 46 || charCode == 8))
        return false;
        else {
        var len = $(element).val().length;
        var index = $(element).val().indexOf('.');
        if (index > 0 && charCode == 46) {
            return false;
        }
        if (index > 0) {
            var CharAfterdot = (len + 1) - index;
            if (CharAfterdot > 3) {
            return false;
            }
        }
    
        }
    return true;
  }
</script>
@endsection
