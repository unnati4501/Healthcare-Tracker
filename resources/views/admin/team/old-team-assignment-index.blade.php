@extends('layouts.app')

@section('after-styles')
<link href="{{asset('assets/plugins/listbox-transfer/css/jquery.transfer.css?var='.rand())}}" rel="stylesheet"/>
@endsection

@section('content')
	@include('admin.team.breadcrumb',['appPageTitle' => trans('labels.team-assignment.index_title')])
<section class="content">
    <div class="container-fluid">
        <div class="card">
            {{ Form::open(['route' => 'admin.old-team-assignment.update', 'class' => 'form-horizontal', 'method'=>'post','role' => 'form', 'id'=>'teamAssignmentFrm']) }}
            <div class="card-body">
                <div class="row">
                    <div class="col-md-10 offset-md-1">
                        <div class="row">
                            <div class="col-sm-6 tranform-custom-border-right">
                                <div class="department-box department-box-left">
                                    {{--
                                    <i class="fal fa-long-arrow-right mobile-to-from-arrow">
                                        --}}
                                    </i>
                                    <div class="department-box-select department-box-select-left">
                                        {{ trans('labels.team-assignment.select_from') }}
                                        {{--
                                        <i class="fal fa-long-arrow-right">
                                        </i>
                                        --}}
                                    </div>
                                    <div class="department-team-select-area">
                                        <div class="form-group">
                                            <label for="">
                                                {{ trans('labels.team-assignment.select_department') }}
                                            </label>
                                            {{ Form::select('fromdepartment', $department, request()->query('fromdepartment'), ['class' => 'form-control select2', 'id'=>'fromdepartment', 'style'=>'width: 100%;', 'autocomplete' => 'off', 'placeholder' => trans('labels.team-assignment.select_department'), 'data-placeholder' => trans('labels.team-assignment.select_department'), 'data-allow-clear' => 'true']) }}
                                        </div>
                                        <div class="form-group">
                                            <label for="">
                                                {{ trans('labels.team-assignment.select_team') }}
                                            </label>
                                            {{ Form::select('fromteam', [], null, ['class' => 'form-control select2', 'id'=>'fromteam', 'style'=>'width: 100%;', 'autocomplete' => 'off', 'placeholder' => trans('labels.team-assignment.select_team'), 'data-placeholder' => trans('labels.team-assignment.select_team'), 'data-allow-clear' => 'true']) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="department-box department-box-right">
                                    <div class="department-box-select department-box-select-right">
                                        {{ trans('labels.team-assignment.select_to') }}
                                        {{--
                                        <i class="fal fa-long-arrow-right">
                                        </i>
                                        --}}
                                    </div>
                                    <div class="department-team-select-area">
                                        <div class="form-group">
                                            <label for="">
                                                {{ trans('labels.team-assignment.select_department') }}
                                            </label>
                                            {{ Form::select('todepartment', $department, request()->query('todepartment'), ['class' => 'form-control select2', 'id'=>'todepartment', 'style'=>'width: 100%;', 'autocomplete' => 'off', 'placeholder' => trans('labels.team-assignment.select_department'), 'data-placeholder' => trans('labels.team-assignment.select_department'), 'data-allow-clear' => 'true']) }}
                                        </div>
                                        <div class="form-group">
                                            <label for="">
                                                {{ trans('labels.team-assignment.select_team') }}
                                            </label>
                                            {{ Form::select('toteam', [], null, ['class' => 'form-control select2', 'id'=>'toteam', 'style'=>'width: 100%;', 'autocomplete' => 'off', 'placeholder' => trans('labels.team-assignment.select_team'), 'data-placeholder' => trans('labels.team-assignment.select_team'), 'data-allow-clear' => 'true']) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-10 offset-md-1">
                        <div class="transfer-department-item d-none" id="teamAssignment">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer border-top text-center">
                <input id="fromteammembers" name="fromteammembers" type="hidden" value=""/>
                <input id="toteammembers" name="toteammembers" type="hidden" value=""/>
                <a class="btn btn-effect btn-outline-secondary me-2 mm-w-100" href="{!! route('admin.old-team-assignment.index') !!}">
                    {{trans('labels.buttons.reset')}}
                </a>
                <button class="btn btn-primary btn-effect mm-w-100" type="submit">
                    {{ trans('labels.team-assignment.update_btn') }}
                </button>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</section>
@endsection

@section('after-scripts')
<script src="{{ asset('assets/plugins/listbox-transfer/js/jquery.transfer.js') }}" type="text/javascript">
</script>
{!! JsValidator::formRequest('App\Http\Requests\Admin\UpdateTeamAssignmentRequest','#teamAssignmentFrm') !!}
<script type="text/javascript">
    var _token = $('input[name="_token"]').val(),
    	Qsfromdepartment = {{ request()->query('fromdepartment', 0) }},
    	Qstodepartment = {{ request()->query('todepartment', 0) }},
    	Qsfromteam = {{ request()->query('fromteam', 0) }},
    	Qstoteam = {{ request()->query('toteam', 0) }},
	    teamAssignmentSettings = {
	        dataArray: [],
	        itemName: "user",
	        valueName: "value"
	    },
	    urls = {
	        getTeams: '{{ route("admin.ajax.departmentTeams", ":id") }}',
	        getTeamMembers: '{{ route("admin.old-team-assignment.getAssignmentTeamMembers", ":ids") }}',
	    };

	function loadAssignmentBlock() {
	    var fromdepartment = $('#fromdepartment').val(),
	        todepartment = $('#todepartment').val(),
	        fromteam = $('#fromteam').val(),
	        toteam = $('#toteam').val();
	    if (fromdepartment != "" && fromdepartment != null && todepartment != "" && todepartment != null && fromteam != "" && fromteam != null && toteam != "" && toteam != null) {
	    	showPageLoaderWithMessage('Loading team members....');
	    	var url = urls.getTeamMembers.replace(":ids", [fromteam, toteam].toString());

	    	$.ajax({
		        url: url,
		        type: 'GET',
		        dataType: 'json',
		        data: { _token: _token }
		    }).done(function(data) {
		    	if(data.status && data.status == 1) {
        			teamAssignmentSettings.dataArray = data.data;
        			teamAssignmentSettings.tabNameText = $('#fromteam option:selected').text();
			        teamAssignmentSettings.rightTabNameText = $('#toteam option:selected').text();
			        $("#teamAssignment").empty().transfer(teamAssignmentSettings);
			        $('#teamAssignment').removeClass('d-none');
			        $(document).on('keydown', '#teamAssignment input[type="text"]', function(e) {
			            if (e.keyCode == 13) {
			                e.preventDefault();
			                return false;
			            }
			        });
        		} else {
        			alert("Failed to load team members, Please try again");
        		}
		    }).fail(function(error) {
		    	alert("Failed to load team members, Please try again");
		    }).always(function() {
		    	hidesPageLoader();
		    });
	    } else {
	        $('#teamAssignment').addClass('d-none');
	    }
	}

	$(document).ready(function() {
	    $.ajaxSetup({
	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
	        }
	    });

	    $('#fromteam').attr('disabled', true);
	    $('#toteam').attr('disabled', true);

	    setTimeout(function() {
		    if($('#fromdepartment').val() != null && $('#fromdepartment') != "") {
		    	$('#fromdepartment').trigger('change');
		    }
		    if($('#todepartment').val() != null && $('#todepartment') != "") {
		    	$('#todepartment').trigger('change');
		    }
	    }, 2000);

	    $(document).on('change', '#fromdepartment, #todepartment', function(e) {
	        var departmentId = $(this).val(),
	            element = ((e.target.name == "fromdepartment") ? "#fromteam" : "#toteam"),
	            qsvalue = ((e.target.name == "fromdepartment") ? Qsfromteam : Qstoteam),
	            teamsData = '';
	        if (departmentId != "" && departmentId != null) {
	            var url = urls.getTeams.replace(":id", departmentId);
	            $.get(url, {
	                _token: _token
	            }, function(data) {
	                if (data.code && data.code == 200) {
	                    $.each(data.result, function(index, team) {
	                        teamsData += `<option value="${team.id}">${team.name}</option>`;
	                    });
	                    $(element).html(teamsData).val(qsvalue).trigger('change');
	                    $(element).attr('disabled', false);

	                    var fromTeamvalue = $('#fromteam').val(),
	            			toTeamvalue = $('#toteam').val();

	                    if (element == "#toteam" && fromTeamvalue != null && fromTeamvalue != "" && $('#toteam').find(`option`).length > 0) {
	                        $('#toteam').find(`option`).removeAttr('disabled');
	                        $('#toteam').find(`option[value='${fromTeamvalue}']`).attr('disabled', 'disabled');
	                        $('#toteam').select2('destroy').select2();
	                    } else if (element == "#fromteam" && toTeamvalue != null && toTeamvalue != "" && $('#fromteam').find(`option`).length > 0) {
	                        $('#fromteam').find(`option`).removeAttr('disabled');
	                        $('#fromteam').find(`option[value='${toTeamvalue}']`).attr('disabled', 'disabled');
	                        $('#fromteam').select2('destroy').select2();
	                    }

	                    if(element == "#toteam" && (Qsfromdepartment == Qstodepartment && Qsfromteam == Qstoteam)) {
	                    	$('#toteam').val('');
	                    	$('#toteam').find(`option[value='${Qsfromteam}']`).attr('disabled', 'disabled');
	                    	$('#toteam').select2('destroy').select2();
	                    }

	                    loadAssignmentBlock();
	                } else {
	                    alert("Failed to load teams, Please try again");
	                }
	            }, 'json');
	        } else {
	            loadAssignmentBlock();
	            $(element).empty().val('').trigger('change');
	        }
	    });
	    $(document).on('change', '#toteam', function(e) {
	        var toTeamId = $(this).val();
	        $('#fromteam').find(`option`).removeAttr('disabled');
	        if (toTeamId != null && toTeamId != "") {
	            $('#fromteam').find(`option[value='${toTeamId}']`).attr('disabled', 'disabled');
	        }
	        if($('#fromteam').data('select2')) {
	        	$('#fromteam').select2('destroy').select2();
		 	}
	        loadAssignmentBlock();
	    });
	    $(document).on('change', '#fromteam', function(e) {
	        var teamId = $(this).val();
	        if (teamId != null && teamId != "") {
	            $('#toteam').find(`option`).removeAttr('disabled');
	            $('#toteam').find(`option[value='${teamId}']`).attr('disabled', 'disabled');
	            if($('#toteam').data('select2')) {
		        	$('#toteam').select2('destroy').select2();
			 	}
	        } else {
	            $('#toteam').find(`option`).removeAttr('disabled');
	            if($('#toteam').data('select2')) {
		        	$('#toteam').select2('destroy').select2();
			 	}
	        }
            loadAssignmentBlock();
	    });
	    $(document).on("submit", "#teamAssignmentFrm", function(e) {
			if($(this).valid() == true) {
				var fromteam = $('#fromteam').val(),
					toteam = $('#toteam').val(),
					fromteammembersobj = [],
					toteammembersobj = [],
					fromteammembers = [],
					toteammembers = [];

				fromteammembersobj = $(`.transfer-double-content-left ul li:not(.selected-hidden) input[type="checkbox"][data-currteam="${toteam}"][data-newteam="${fromteam}"]`);
				toteammembersobj = $(`.transfer-double-content-right ul li:not(.selected-hidden) input[type="checkbox"][data-currteam="${fromteam}"][data-newteam="${toteam}"]`);

				$('#fromteammembers').val('');
				$('#toteammembers').val('');

				if(fromteammembersobj.length > 0 || toteammembersobj.length > 0) {
					if(fromteammembersobj.length > 0) {
						$.each(fromteammembersobj, function(key, member) {
							fromteammembers.push(member.value);
						});
						fromteammembers = fromteammembers.toString();
						if(fromteammembers != '') {
							$('#fromteammembers').val(fromteammembers);
						}

					}

					if(toteammembersobj.length > 0) {
						$.each(toteammembersobj, function(key, member) {
							toteammembers.push(member.value);
						});
						toteammembers = toteammembers.toString();
						if(toteammembers != '') {
							$('#toteammembers').val(toteammembers);
						}
					}
				} else {
					toastr.error("Please select atleast one member and move to another team");
					e.preventDefault();
					return false;
				}
			};
		});
	});
</script>
@endsection
