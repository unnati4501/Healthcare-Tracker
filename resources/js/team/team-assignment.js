function loadAssignmentBlock() {
    var fromdepartment = $('#fromdepartment').val(),
        todepartment = $('#todepartment').val(),
        fromteam = $('#fromteam').val(),
        toteam = $('#toteam').val();
    if (fromdepartment != "" && fromdepartment != null && todepartment != "" && todepartment != null && fromteam != "" && fromteam != null && toteam != "" && toteam != null) {
    	showPageLoaderWithMessage(message.loading_team_members);
    	$.ajax({
	        url: urls.getTeamMembers.replace(":ids", [fromteam, toteam].toString()),
	        type: 'GET',
	        dataType: 'json',
	    }).done(function(data) {
	    	if(data.status && data.status == 1) {
	    		// remove error class
	    		$('#fromTeamMembersList .draggable-outer, #toTeamMembersList .draggable-outer').removeClass('limit-error');

	    		// set data ids of both ul
	    		$('#fromTeamMembersList').data('id', fromteam).data('default', data.data.default[fromteam]);
	    		$('#toTeamMembersList').data('id', toteam).data('default', data.data.default[toteam]);

	    		// set headers of ul
	    		$(`#fromTeamMembersList .name`).html($('#fromteam option:selected').text());
	    		$(`#toTeamMembersList .name`).html($('#toteam option:selected').text());

	    		// assign team members
	    		$('#fromTeamMembersList .draggable').html(data.data[fromteam]);
	    		$('#toTeamMembersList .draggable').html(data.data[toteam]);

	    		// set limit
	    		$('#limit').val(data.data.limit);
	    		var fromCount = $('#fromTeamMembersList .draggable li').length,
	    			toCount = $('#toTeamMembersList .draggable li').length;

				// set members count
    			if(data.data.default[fromteam]) {
    				$('#fromCount').html(`${fromCount}`);
    			} else {
    				$('#fromCount').html(((data.data.limit > 0) ? `${fromCount} out of ${data.data.limit}` : `${fromCount}`));
    			}

    			// set members count
    			if(data.data.default[toteam]) {
    				$('#toCount').html(`${toCount}`);
    			} else {
    				$('#toCount').html(((data.data.limit > 0) ? `${toCount} out of ${data.data.limit}` : `${toCount}`));
    			}

	    		// remove hidden class
	    		$('#teamAssignment').removeClass('d-none');

	    		// refresh widget
	    		teamAssignment.sortable("refresh");
    		} else {
    			alert(message.failed_to_load_team_members);
    		}
	    }).fail(function(error) {
	    	alert(message.failed_to_load_team_members);
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

    teamAssignment = $(".draggable").sortable({
	    group: 'draggable',
	    pullPlaceholder: false,
	    isValidTarget: function  ($item, container) {
      		return true;
	  	},
	  	onDrop: function ($item, container, _super) {
	    	_super($item, container);
	    	$('#fromTeamMembersList .draggable-outer, #toTeamMembersList .draggable-outer').removeClass('limit-error');
	    	var limit = parseInt($('#limit').val() || 0),
	    		fromDefault = ($('#fromTeamMembersList').data('default') || false),
	    		toDefault = ($('#toTeamMembersList').data('default') || false),
	    		fromCount = $('#fromTeamMembersList .draggable li').length,
	    		toCount = $('#toTeamMembersList .draggable li').length;

    		// add error class if members count are greather than limit
	    	if(!fromDefault && limit > 0 && fromCount > limit) {
	    		$('#fromTeamMembersList .draggable-outer').addClass('limit-error');
	    	}

	    	// add error class if members count are greather than limit
	    	if(!toDefault && limit > 0 && toCount > limit) {
	    		$('#toTeamMembersList .draggable-outer').addClass('limit-error');
	    	}

	    	// update members count
	    	if($('#fromTeamMembersList').data('default') == true) {
				$('#fromCount').html(`${fromCount}`);
			} else {
				$('#fromCount').html(((limit > 0) ? `${fromCount} out of ${limit}` : `${fromCount}`));
			}

			// update members count
			if($('#toTeamMembersList').data('default') == true) {
				$('#toCount').html(`${toCount}`);
			} else {
				$('#toCount').html(((limit > 0) ? `${toCount} out of ${limit}` : `${toCount}`));
			}
	  	},
		serialize: function (parent, children, isContainer) {
			if(isContainer) {
				return parent.find('li').map(function(index, element) { return $(element).data('id'); });
			} else {
				return parent.data('id');
			}
		},
	});

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
            $.get(url, function(data) {
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
                    alert(message.failed_to_load_team);
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

    $(document).on("keyup", '.search-member', function(e) {
    	var value = $.trim($(this).val()).replace(/ +/g, ' ').toLowerCase(),
    		control = $(this).data('control'),
    		$rows = $(`#${control}`).find('li');

		$rows.show().filter(function() {
	        var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
	        return !~text.indexOf(value);
	    }).hide();
    });

    $(document).on('keydown', '.search-member', function(e) {
        if (e.keyCode == 13) {
            e.preventDefault();
            return false;
        }
    });

    $(document).on("submit", "#teamAssignmentFrm", function(e) {
    	$('.toast').remove();
		if($(this).valid() == true) {
			var fromteam = $('#fromteam').val(),
				toteam = $('#toteam').val(),
				limit = parseInt($('#limit').val() || 0),
				fromteammembersobj = $('#fromTeamMembersList .draggable').find(`li[data-team="${toteam}"]`),
				toteammembersobj = $('#toTeamMembersList .draggable').find(`li[data-team="${fromteam}"]`),
				fromteamdefault = ($('#fromTeamMembersList').data('default') || false),
				toteamdefault = ($('#toTeamMembersList').data('default') || false),
				fromteammembers = [],
				toteammembers = [];

			$('#fromteammembers, #toteammembers').val('');
			$('#fromTeamMembersList .draggable-outer, #toTeamMembersList .draggable-outer').removeClass('limit-error');

			if(fromteammembersobj.length > 0 || toteammembersobj.length > 0) {
				if(fromteammembersobj.length > 0) {
					// validate team limit
					if(!fromteamdefault && limit > 0 && $('#fromTeamMembersList .draggable li').length > limit)  {
						$('#fromTeamMembersList .draggable-outer').addClass('limit-error');
						$('.toast').remove();
						toastr.error(`${$('#fromteam option:selected').text()} `+message.team_reach_team_limit);
						e.preventDefault();
						return false;
					}

					// preapre array of members
					$.each(fromteammembersobj, function(key, member) {
						fromteammembers.push(member.dataset.id);
					});
					fromteammembers = fromteammembers.toString();

					// set members
					if(fromteammembers != '') {
						$('#fromteammembers').val(fromteammembers);
					}

				}

				if(toteammembersobj.length > 0) {
					// validate team limit
					if(!toteamdefault && limit > 0 && $('#toTeamMembersList .draggable li').length > limit)  {
						$('#toTeamMembersList .draggable-outer').addClass('limit-error');
						$('.toast').remove();
						toastr.error(`${$('#toteam option:selected').text()} `+message.team_reach_team_limit);
						e.preventDefault();
						return false;
					}

					// preapre array of members
					$.each(toteammembersobj, function(key, member) {
						toteammembers.push(member.dataset.id);
					});
					toteammembers = toteammembers.toString();

					// set members
					if(toteammembers != '') {
						$('#toteammembers').val(toteammembers);
					}
				}

			    $('#teamAssignmentFrm').ajaxSubmit({
			    	beforeSend: function(data) {
		                $(".page-loader-wrapper").show();
			    	},
		            success: function(data) {
		            	if(data.status && data.status == true) {
		                	window.location.replace(data.url);
		            	}
		            },
		            error: function(error) {
		                toastr.error(error?.responseJSON?.data || message.something_wrong_try_again);
		            },
		            complete: function() {
		                $(".page-loader-wrapper").hide();
		            }
		        });
		        e.preventDefault();
				return false;
			} else {
				toastr.error(message.select_atleast_one_member);
				e.preventDefault();
				return false;
			}
		};
	});
});