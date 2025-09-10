function makeTimePicker(timeselector, wrapper) {
    $(timeselector).timepicker({
        'showDuration': true,
        'timeFormat': 'h:i A',
        'step': 30,
        'useSelect': true,
    });
    // $(wrapper).datepair({
    //     'defaultTimeDelta': 900000 // 60000 milliseconds => 1 Minute
    // });
}
$(document).ready(function() {
    // for add new slot
    $(document).on('click', '.add-slot', function(e) {
        var wellbeingSp = $('#dt_wellbeing_sp_ids').val();
        if(wellbeingSp.length <= 0) {
            toastr.error("Staff field is required.");
            return true;
        }
        if ($(this).parents('.slots-wrapper').find('.add-new-slot-block').length == 0) {
            var template = $('#add-new-slot-template').text().trim();
            $(this).parents('.slots-wrapper').append(template);
            var wrapper = $(this).parents('.slots-wrapper').find('.slot-control-block'),
                timeselector = wrapper.find('.time');
            makeTimePicker(timeselector, wrapper);
            $('.dt_wb_ids').empty();
            $.each(JSON.parse(wellbeingSpecialist), function(i, value) {
                if ($.inArray(i, wellbeingSp) >= 0) {
                    $('.dt_wb_ids').append('<option value="' + i + '">' + value + '</option>');
                }
            });
            setTimeout(function() {
                var select = $('.dt_wb_ids').select2({
                    closeOnSelect: false
                }).on("change", function(e){
                    select.select2("close");
                    select.select2("open");
                });
            }, 100);
        }
    });
    // to store new slot
    $(document).on('click', '.save-new-slot', function(e) {
        var wrapper = $(this).parents('.add-new-slot-block').find('.slot-control-block');
        $('.toast').remove();
        var template = $('#preview-slot-template').text().trim(),
            wsSlotTemplate = $('#preview-ws-slot-template').text().trim(),
            wsHiddenTemplate = $('#preview-ws-hidden-template').text().trim(),
            dayWrapper = $(this).parents('.set-availability-box'),
            dayKey = (dayWrapper.data('dayKey') || ""),
            slotsWrapper = $(this).parents('.slots-wrapper'),
            slotActionBlock = $(this).parents('.slot-action-block'),
            controls = wrapper.find('.time'),
            startTime = moment($(controls[0]).timepicker('getTime')),
            endTime = moment($(controls[1]).timepicker('getTime')),
            addNewSlot = true,
            otherSlots = slotsWrapper.find('.preview-slot-block input[name*="slots"]').serializeArray(),
            tempStartTime = moment(startTime).format('HH:mm'),
            tempEndTime = moment(endTime).format('HH:mm');
        if(tempStartTime != '00:00' && tempEndTime != '00:00') {
            if (startTime >= endTime) {
                addNewSlot = false;
                toastr.error('Please select appropriate time slots');
                return false;
            }
        }
        if (otherSlots.length > 0) {
            var otherSlotsData = otherSlots.map(function(slot) {
                return moment(new Date(`${today.getFullYear()}-${(today.getMonth() + 1)}-${today.getDate()} ${slot.value}`));
            });
            for (i = 0; i < otherSlotsData.length; i++) {
                if (i % 2 == 0) {
                    if (!(!otherSlotsData[i].isBetween(startTime, endTime, undefined, '[)') && !otherSlotsData[i + 1].isBetween(startTime, endTime, undefined, '(]') && !startTime.isBetween(otherSlotsData[i], otherSlotsData[i + 1], undefined, '[)') && !endTime.isBetween(otherSlotsData[i], otherSlotsData[i + 1], undefined, '(]'))) {
                        addNewSlot = false;
                        toastr.error('Selected duration is occupied.');
                        break;
                    }
                }
            }
        }
        if (addNewSlot) {
            var selectedWs = $('#dt_wb_ids').val();
            var slotTemplate = "";
            var hiddenTemplate = "";
            var id = `id${Date.now()}`;
            var count = 1;
            $.each(JSON.parse(wellbeingSpecialist), function(i, value) {
                var blankString = '';
                if ($.inArray(i, selectedWs) >= 0) {
                    if(count < selectedWs.length){
                        var blankString = ', ';
                    }
                    slotTemplate += wsSlotTemplate.replace(/\#value#/g, i).replace(/\#key#/g, dayKey).replace(/\#id#/g, id).replace(/\#ws_name#/g, value) + blankString;
                    hiddenTemplate += wsHiddenTemplate.replace(/\#value#/g, i).replace(/\#key#/g, dayKey).replace(/\#id#/g, id).replace(/\#ws_name#/g, value);
                    count++;
                }
            });
            slotsWrapper.find('.no-data-block').addClass('d-none'); // startTime.toDate() // endTime.toDate()
            let displayEndtime = endTime.format('HH:mm');
            template = template.replace(/\#key#/g, dayKey).replace(/\#start_time#/g, startTime.format('HH:mm')).replace(/\#end_time#/g, displayEndtime).replace('#time#', `${startTime.format('hh:mm A')} - ${endTime.format('hh:mm A')}`).replace(/\#id#/g, id).replace(/\#ws_selected#/g, slotTemplate).replace(/\#ws_hidden_fields#/g, hiddenTemplate);
            slotActionBlock.find('.close-add-new-slot').trigger('click');
            slotsWrapper.append(template);
            $('#slots_exist').val('1').valid();
        }
    });
    // to close/disard new slot block
    $(document).on('click', '.close-add-new-slot', function(e) {
        $(this).parents('.add-new-slot-block').remove();
    });
    // remove slot button click show remove confirmation modal
    $(document).on('click', '.remove-slot', function(e) {
        $('#remove-slot-model-box').data('selector', $(this)).modal('show');
    });
    $(document).on('hidden.bs.modal', '#remove-slot-model-box', function(e) {
        $('#remove-slot-model-box').data('selector', undefined);
    });
    // remove button click of confirmation modal
    $('#remove-slot-confirm').on('click', function(e) {
        var _this = ($('#remove-slot-model-box').data('selector') || undefined);
        if (_this) {
            var currentSlotWrapper = $(_this).parents('.preview-slot-block'),
                slotsWrapper = currentSlotWrapper.parents('.slots-wrapper'),
                // id = (currentSlotWrapper.data('id') || 0),
                previewSlotLength = slotsWrapper.find('.preview-slot-block').length;
            if ((previewSlotLength - 1) == 0) {
                slotsWrapper.find('.no-data-block').removeClass('d-none');
                if ($('.set-availability-block').find('.preview-slot-block').length == 1) {
                    $('#slots_exist').val('').valid();
                }
            }
            currentSlotWrapper.remove();
            $('#remove-slot-model-box').modal('hide');
        }
    });
    // edit slot
    $(document).on('click', '.edit-slot', function(e) {
        var wellbeingSp = $('#dt_wellbeing_sp_ids').val();
        if(wellbeingSp.length <= 0) {
            toastr.error("Staff field is required.");
            return true;
        }
        var currentSlotWrapper = $(this).parents('.preview-slot-block'),
            slotsWrapper = currentSlotWrapper.parents('.slots-wrapper'),
            addNewSlotBlock = slotsWrapper.find('.add-new-slot-block'),
            editSlotBlock = slotsWrapper.find('.edit-slot-block'),
            // id = (currentSlotWrapper.data('id') || 0),
            startTime = (currentSlotWrapper.find('.start-time-data').val() || 0),
            endTime = (currentSlotWrapper.find('.end-time-data').val() || 0),
            slotId = $(this).parents('.preview-slot-block').data('id');
        if (startTime && endTime) {
            startTime = new Date(`${today.getFullYear()}-${(today.getMonth() + 1)}-${today.getDate()} ${startTime}`);
            endTime = new Date(`${today.getFullYear()}-${(today.getMonth() + 1)}-${today.getDate()} ${endTime}`);
            // close if edit-slot-block is exist in current particular slots block
            if (editSlotBlock.length > 0) {
                editSlotBlock.find('.cancel-edit-slot').trigger('click');
            }
            // close if add-new-slot-block is exist in current particular slots block
            if (addNewSlotBlock.length > 0) {
                addNewSlotBlock.find('.close-add-new-slot').trigger('click');
            }
            // generate preview for edit block along with details
            var template = $('#edit-slot-template').text().trim();
            template = template.replace("#id#", 0).replace('#start_time#', startTime).replace('#end_time#', endTime);
            $(template).insertAfter(currentSlotWrapper);
            currentSlotWrapper.addClass('d-none');
            var slotControlBlock = $(this).parents('.slots-wrapper').find('.slot-control-block'),
                timeselector = slotControlBlock.find('.time');
            makeTimePicker(timeselector, slotControlBlock);
            $(timeselector[0]).timepicker('setTime', startTime);
            $(timeselector[1]).timepicker('setTime', endTime);
            // $(slotControlBlock).datepair('refresh');
            let selectedwsArray = [];
            if (currentSlotWrapper.find('.selected_ws').length) {
                currentSlotWrapper.find('.selected_ws').each(function(i, value) {
                    selectedwsArray.push(this.value);
                });
            }
            slotsWrapper.find('.edit-slot-block').attr('data-id', slotId);
            $('.dt_wb_ids').empty();
            $.each(JSON.parse(wellbeingSpecialist), function(i, value) {
                if ($.inArray(i, wellbeingSp) >= 0) {
                    let selectedOption = "";
                    if($.inArray(i, selectedwsArray) >= 0) {
                        selectedOption = "selected='selected'";
                    }
                    $('.dt_wb_ids').append('<option value="' + i + '" '+ selectedOption +'>' + value + '</option>');
                }
            });
            setTimeout(function() {
                var select = $('.dt_wb_ids').select2({
                    closeOnSelect: false
                }).on("change", function(e){
                    select.select2("close");
                    select.select2("open");
                });
            }, 200);
        }
    });
    // save edited bloc
    $(document).on('click', '.save-edit-slot', function(e) {
        var editSlotBlock = $(this).parents('.edit-slot-block'),
            slotsWrapper = $(this).parents('.slots-wrapper'),
            wsSlotTemplate = $('#preview-ws-slot-template').text().trim(),
            wsHiddenTemplate = $('#preview-ws-hidden-template').text().trim(),
            dayWrapper = $(this).parents('.set-availability-box'),
            dayKey = (dayWrapper.data('dayKey') || ""),
            slotControlBlock = editSlotBlock.find('.slot-control-block');
        $('.toast').remove();
        var previewSlotBlock = editSlotBlock.prev(),
            controls = editSlotBlock.find('.time'),
            startTime = moment($(controls[0]).timepicker('getTime')),
            endTime = moment($(controls[1]).timepicker('getTime')),
            allowEditSlot = true,
            otherSlots = slotsWrapper.find('.preview-slot-block').not(previewSlotBlock).find('input[name*="slots"]').serializeArray(),
            slotId = $(this).parents('.edit-slot-block').data('id'),
            tempStartTime = moment(startTime).format('HH:mm'),
            tempEndTime = moment(endTime).format('HH:mm');
        if(tempStartTime != '00:00' && tempEndTime != '00:00') {
            if (startTime >= endTime) {
                addNewSlot = false;
                toastr.error('Please select appropriate time slots');
                return false;
            }
        }
        if (otherSlots.length > 0) {
            var otherSlotsData = otherSlots.map(function(slot) {
                return moment(new Date(`${today.getFullYear()}-${(today.getMonth() + 1)}-${today.getDate()} ${slot.value}`));
            });
            for (i = 0; i < otherSlotsData.length; i++) {
                if (i % 2 == 0) {
                    if (!(!otherSlotsData[i].isBetween(startTime, endTime, undefined, '[)') && !otherSlotsData[i + 1].isBetween(startTime, endTime, undefined, '(]') && !startTime.isBetween(otherSlotsData[i], otherSlotsData[i + 1], undefined, '[)') && !endTime.isBetween(otherSlotsData[i], otherSlotsData[i + 1], undefined, '(]'))) {
                        allowEditSlot = false;
                        toastr.error('Selected duration is occupied.');
                        break;
                    }
                }
            }
        }
        if (allowEditSlot) {
            var selectedWs = $('#dt_wb_ids').val();
            var slotTemplate = "";
            var hiddenTemplate = "";
            var count = 1;
            $.each(JSON.parse(wellbeingSpecialist), function(i, value) {
                var blankString = '';
                if ($.inArray(i, selectedWs) >= 0) {
                    if(count < selectedWs.length){
                        var blankString = ', ';
                    }
                    slotTemplate += wsSlotTemplate.replace(/\#value#/g, i).replace(/\#key#/g, dayKey).replace(/\#id#/g, slotId).replace(/\#ws_name#/g, value) + blankString;
                    hiddenTemplate += wsHiddenTemplate.replace(/\#value#/g, i).replace(/\#key#/g, dayKey).replace(/\#id#/g, slotId).replace(/\#ws_name#/g, value);
                    count++;
                }
            });
            previewSlotBlock.removeClass('d-none');
            previewSlotBlock.find('.slot-timmings').html(`${startTime.format('hh:mm A')} - ${endTime.format('hh:mm A')}`);
            previewSlotBlock.find('.start-time-data').val(startTime.format('HH:mm'));
            previewSlotBlock.find('.end-time-data').val(endTime.format('HH:mm'));
            previewSlotBlock.find('.ws_selected_users').html(slotTemplate);
            previewSlotBlock.find('.ws_hidden_fields').html(hiddenTemplate);
            editSlotBlock.remove();
        }
    });
    // close edit block
    $(document).on('click', '.cancel-edit-slot', function(e) {
        var wrapper = $(this).parents('.edit-slot-block');
        wrapper.prev().removeClass('d-none');
        wrapper.remove();
    });

    // Set Hours By, Set Availability By
    $('.set_hours').hide();
    set_dt_functionality(hoursby, availabilityby);

    $('#set_hours_by, #set_availability_by').change(function() {
        $('.set_hours').hide();
        var _hoursby = $('#set_hours_by').val();
        var _availabilityby = $('#set_availability_by').val();
        // $('.hiddenfields').html(' ');
        set_dt_functionality(_hoursby, _availabilityby);
    });
    $(document).on('click', '.edit-location-specific', function() {
        var selectedWBS = $("#dt_wellbeing_sp_ids :selected").map(function(i, el) {
            return $(el).val() +'-'+ $(el).text();
        }).get();
        $('#location_specific_wellbeing_sp_ids').empty();
        $.each(selectedWBS, function(key, value) {
            var tempArray = value.split('-');
            $('#location_specific_wellbeing_sp_ids').append('<option value="' + tempArray[0] + '" selected>' + tempArray[1] + '</option>');
        });
        $('#location_specific_wellbeing_sp_ids').trigger('change select2');

        var location_name = $('.edit-location-specific').parent('tr').find('td:first').text();
        var locationId = $(this).attr('locationid');
        var item = $.parseJSON(locationList).find(function (e) {
            if (e.id == locationId) {
                return e;
            }
        });
        $('#location-specific-ws-slot').find('#modal_title').html(item.name + "'s Availability");
        $('#location-specific-ws-slot').attr('data-id', locationId).modal('show');
        $("#location_specific_wellbeing_sp_ids").attr('data-id', locationId);
        var all_ws_ids = [];
        var specificHtml = "";
        $('#dtspecificwshours').find('tbody').html('');
        $('#location_ws_id_'+locationId+' > span').map(function() {
            var _child_id = this.id;
            var ids = _child_id.split('_');
            var wsid = ids[ids.length - 1];
            var _title = this.title;
            if(_title != ''){
                specificHtml+='<tr id="ws_'+wsid+'" data-type="new"><td>'+_title+'</td>';
                specificHtml+="<td class='text-center'>";
                specificHtml+="<a class='action-icon bs-calendar-slidebar' id="+wsid+" href='javascript:;' data-toggle='canvas' data-target='#bs-canvas-right' aria-expanded='false' aria-controls='bs-canvas-right' title='Edit'>";
                specificHtml+="<i class='far fa-edit'></i></a></td></tr>";
                all_ws_ids.push(wsid);
            }
        });
        $('#dtspecificwshours').find('tbody').html(specificHtml);
        $('.location_specific_wellbeing_sp_ids').val(all_ws_ids).prop('selected', true).trigger('change.select2');

        if($('#location-specific-ws-slot').attr('data-id', locationId).find('table > tbody > tr').length == 0){
            $('#location-specific-ws-slot').attr('data-id', locationId).find('table > tbody').html('<tr class="text-center no-tr-'+locationId+'" ><td>No wellbeing specialist available</td></tr>')
        }
    });
    $(document).on('select2:unselect', '.location_specific_wellbeing_sp_ids', function(e) {
        var locationId = $(this).attr('data-id');
        var selectedRemovedId = e.params.data.id;
        $('#dtspecificwshours').find('#ws_'+selectedRemovedId).remove();
        $('.ls_slots_'+locationId+'_'+selectedRemovedId).remove();
        $('.location_ws_column').find('#ws_'+locationId+'_'+selectedRemovedId).remove();

        if($('#location-specific-ws-slot').attr('data-id', locationId).find('table > tbody > tr').length == 0){
            $('#location-specific-ws-slot').attr('data-id', locationId).find('table > tbody').html('<tr class="text-center no-tr-'+locationId+'"><td>No wellbeing specialist available</td></tr>')
        }
    });
    $(document).on('select2:select', '.location_specific_wellbeing_sp_ids', function(e) {
        var locationId = $(this).attr('data-id');
        var selectedIds = e.params.data.id;
        var _title = e.params.data.text;
        specificHtml='<tr id="ws_'+selectedIds+'" data-type="new"><td>'+_title+'</td>';
        specificHtml+="<td class='text-center'>";
        specificHtml+="<a class='action-icon bs-calendar-slidebar' id="+selectedIds+" href='javascript:;' data-toggle='canvas' data-target='#bs-canvas-right' aria-expanded='false' aria-controls='bs-canvas-right' title='Edit'>";
        specificHtml+="<i class='far fa-edit'></i></a></td></tr>";

        var tag_html = "<span id='ws_"+locationId+"_"+selectedIds+"' title='"+_title+"' class='ws_sl_"+locationId+" ws_"+selectedIds+" badge badge-secondary'>"+_title+"</span>";
        $('table#dtspecificwshours tr.no-tr-'+locationId+'').remove();
        $('#dtspecificwshours').find('tbody').append(specificHtml);
        $('#location_ws_id_'+locationId).append(tag_html);
    });
});
function set_dt_functionality(hoursby, availabilityby) {
    $('.location_ws_column').hide();
    $('.location_WS_specific_edit').addClass('d-none');
    $('.location_ws_column_test').addClass('d-none');
    var wellbeingSp = $('#dt_wellbeing_sp_ids').val();
    if (hoursby == 1 && availabilityby == 1) {
        $('#set_business_hours').show();
    } else if (hoursby == 1 && availabilityby == 2 && wellbeingSp.length > 0) {
        $('#set_wellbeing_hours').show();
    } else if (hoursby == 2 && availabilityby == 1) {
        $('.location_ws_column, .slot-specific, .slot-calendar-general, .slot-calendar-specific').hide();
        $('.location_WS_specific_edit').removeClass('d-none');
        $('#set_location_hours, .slot-general').show();
    } else if (hoursby == 2 && availabilityby == 2) {
        $('.location_ws_column, .slot-specific, .slot-calendar-specific, #set_location_hours').show();
        $('.location_ws_column_test').removeClass('d-none');
        $('.location_WS_specific_edit').addClass('d-none');
        $(".slot-general, .slot-calendar-general").hide();
    }
}
function removeValue(list, value) {
    list = list.split(',');
    list.splice(list.indexOf(value), 1);
    return list.join(',');
  }