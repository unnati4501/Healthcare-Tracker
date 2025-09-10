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
    $(document).on('click', '.add-location-slot', function(e) {
        var wellbeingSp = $('#dt_wellbeing_sp_ids').val();
        if(wellbeingSp.length <= 0) {
            toastr.error("Staff field is required.");
            return true;
        }
        if ($(this).parents('.slots-wrapper-location').find('.add-new-slot-block-location').length == 0) {
            var template = $('#add-new-slot-location-template').text().trim();
            $(this).parents('.slots-wrapper-location').append(template);
            var wrapper = $(this).parents('.slots-wrapper-location').find('.slot-control-block-location'),
                timeselector = wrapper.find('.locationtime');
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
    $(document).on('click', '.save-new-slot-location', function(e) {
        var wrapper = $(this).parents('.add-new-slot-block-location').find('.slot-control-block-location');
        $('.toast').remove();
        var template = $('#preview-slot-location-template').text().trim(),
            wsSlotTemplate = $('#preview-ws-slot-template-location').text().trim(),
            wsHiddenTemplate = $('#preview-ws-location-hidden-template').text().trim(),
            dayWrapper = $(this).parents('.set-availability-box'),
            dayKey = (dayWrapper.data('dayKey') || ""),
            slotsWrapper = $(this).parents('.slots-wrapper-location'),
            slotActionBlock = $(this).parents('.slot-action-block-location'),
            controls = wrapper.find('.locationtime'),
            startTime = moment($(controls[0]).timepicker('getTime')),
            endTime = moment($(controls[1]).timepicker('getTime')),
            addNewSlot = true,
            otherSlots = slotsWrapper.find('.preview-slot-block-location input[name*="location_slots"]').serializeArray();
        if (startTime >= endTime) {
            addNewSlot = false;
            toastr.error('Please select appropriate time slots');
            return false;
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
            template = template.replace(/\#key#/g, dayKey).replace(/\#start_time#/g, startTime.format('HH:mm')).replace(/\#end_time#/g, endTime.format('HH:mm')).replace('#time#', `${startTime.format('hh:mm A')} - ${endTime.format('hh:mm A')}`).replace(/\#id#/g, id).replace(/\#ws_selected#/g, slotTemplate).replace(/\#ws_hidden_fields#/g, hiddenTemplate).replace(/\#from#/g, 'tempTable');
            slotActionBlock.find('.close-add-new-slot-location').trigger('click');
            slotsWrapper.append(template);
            $('#slots_exist_for_location').val('1').valid();
            $.ajax({
                url: saveLocationSlotsTemp,
                type: 'POST',
                dataType: 'json',
                data: {
                    'locationId': $('#add-location-slot-model-box').attr('data-id'),
                    'companyId' : $("#company_id").val(),
                    'startTime': startTime.format('HH:mm'),
                    'endTime'  : endTime.format('HH:mm'),
                    'wsId'     : selectedWs,
                    'day'      : dayKey
                }
            }).done(function(response) {
                slotsWrapper.find('.preview-slot-block-location').attr('data-id', response.last_insert_id);
            }).fail(function(error) {
            })
        }
    });
    // to close/disard new slot block
    $(document).on('click', '.close-add-new-slot-location', function(e) {
        $(this).parents('.add-new-slot-block-location').remove();
    });
    // remove slot button click show remove confirmation modal
    $(document).on('click', '.remove-slot-location', function(e) {
        // $("#add-location-slot-model-box").css({"z-index": "1040"});
        $('#remove-location-slot-model-box').data('selector', $(this)).modal('show');
        // $('#remove-location-slot-model-box').css({"z-index": "1050"});
    });
    $(document).on('hidden.bs.modal', '#remove-location-slot-model-box', function(e) {
        $('#remove-location-slot-model-box').data('selector', undefined);
        // $("#add-location-slot-model-box").css({"z-index": "1050"});
        //$("#add-location-slot-model-box").modal('show');
    });
    // remove button click of confirmation modal
    $('#remove-location-slot-confirm').on('click', function(e) {
        var _this = ($('#remove-location-slot-model-box').data('selector') || undefined);
        if (_this) {
            var currentSlotWrapper = $(_this).parents('.preview-slot-block-location'),
                slotsWrapper = currentSlotWrapper.parents('.slots-wrapper-location'),
                // id = (currentSlotWrapper.data('id') || 0),
                previewSlotLength = slotsWrapper.find('.preview-slot-block-location').length;
            if ((previewSlotLength - 1) == 0) {
                slotsWrapper.find('.no-data-block').removeClass('d-none');
                if ($('.set-availability-block').find('.preview-slot-block-location').length == 1) {
                    $('#slots_exist_for_location').val('').valid();
                }
            }
            
           var checkMainTableRemoveSlotIdExists = $("#mainTableRemovedSlotIds").val();
           if(currentSlotWrapper.attr('data-from') == 'mainTable'){
                if(checkMainTableRemoveSlotIdExists != ''){
                    checkMainTableRemoveSlotIdExists+= ","+currentSlotWrapper.attr('data-id');
                    $("#mainTableRemovedSlotIds").val(checkMainTableRemoveSlotIdExists);    
                }else{
                    $("#mainTableRemovedSlotIds").val(currentSlotWrapper.attr('data-id'));
                }
           }

           var checkTempTableRemoveSlotIdExists = $("#tempTableRemovedSlotIds").val();
           if(currentSlotWrapper.attr('data-from') == 'tempTable'){
                if(checkTempTableRemoveSlotIdExists != ''){
                    checkTempTableRemoveSlotIdExists+= ","+currentSlotWrapper.attr('data-id');
                    $("#tempTableRemovedSlotIds").val(checkTempTableRemoveSlotIdExists);    
                }else{
                    $("#tempTableRemovedSlotIds").val(currentSlotWrapper.attr('data-id'));
                }

                $.ajax({
                    type: 'DELETE',
                    url: deleteTempSlots + '/' + currentSlotWrapper.attr('data-id'),
                    data: null,
                    crossDomain: true,
                    cache: false,
                    contentType: 'json',
                    success: function(data) {
                    },
                    error: function(data) {
                    }
                });
           }


            currentSlotWrapper.remove();
            $('#remove-location-slot-model-box').modal('hide');
        }
    });
    // edit slot
    $(document).on('click', '.edit-slot-location', function(e) {
        var wellbeingSp = $('#dt_wellbeing_sp_ids').val();
        if(wellbeingSp.length <= 0) {
            toastr.error("Staff field is required.");
            return true;
        }
        var currentSlotWrapper = $(this).parents('.preview-slot-block-location'),
            slotsWrapper = currentSlotWrapper.parents('.slots-wrapper-location'),
            
            addNewSlotBlock = slotsWrapper.find('.add-new-slot-block-location'),
            editSlotBlock = slotsWrapper.find('.edit-slot-block-location'),
            // id = (currentSlotWrapper.data('id') || 0),
            startTime = (currentSlotWrapper.find('.start-time-data-location').val() || 0),
            endTime = (currentSlotWrapper.find('.end-time-data-location').val() || 0),
            slotId = $(this).parents('.preview-slot-block-location').data('id');
            from = $(this).parents('.preview-slot-block-location').data('from');
        if (startTime && endTime) {
            startTime = new Date(`${today.getFullYear()}-${(today.getMonth() + 1)}-${today.getDate()} ${startTime}`);
            endTime = new Date(`${today.getFullYear()}-${(today.getMonth() + 1)}-${today.getDate()} ${endTime}`);
            
            // close if edit-slot-block-location is exist in current particular slots block
            if (editSlotBlock.length > 0) {
                editSlotBlock.find('.cancel-edit-slot-location').trigger('click');
            }
            // close if add-new-slot-block-location is exist in current particular slots block
            if (addNewSlotBlock.length > 0) {
                addNewSlotBlock.find('.close-add-new-slot-location').trigger('click');
            }
            // generate preview for edit block along with details
            var template = $('#edit-slot-location-template').text().trim();
            template = template.replace("#id#", 0).replace('#start_time#', startTime).replace('#end_time#', endTime).replace('#from#', from);
            $(template).insertAfter(currentSlotWrapper);
            currentSlotWrapper.addClass('d-none');
            var slotControlBlock = $(this).parents('.slots-wrapper-location').find('.slot-control-block-location'),
                timeselector = slotControlBlock.find('.locationtime');
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
            slotsWrapper.find('.edit-slot-block-location').attr('data-id', slotId);
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
    $(document).on('click', '.save-edit-slot-location', function(e) {
        var editSlotBlock = $(this).parents('.edit-slot-block-location'),
            slotsWrapper = $(this).parents('.slots-wrapper-location'),
            wsSlotTemplate = $('#preview-ws-slot-template-location').text().trim(),
            wsHiddenTemplate = $('#preview-ws-location-hidden-template').text().trim(),
            dayWrapper = $(this).parents('.set-availability-box'),
            dayKey = (dayWrapper.data('dayKey') || ""),
            slotControlBlock = editSlotBlock.find('.slot-control-block-location');
        $('.toast').remove();
        var previewSlotBlock = editSlotBlock.prev(),
            controls = editSlotBlock.find('.locationtime'),
            startTime = moment($(controls[0]).timepicker('getTime')),
            endTime = moment($(controls[1]).timepicker('getTime')),
            allowEditSlot = true,
            otherSlots = slotsWrapper.find('.preview-slot-block-location').not(previewSlotBlock).find('input[name*="location_slots"]').serializeArray(),
            slotId = $(this).parents('.edit-slot-block-location').data('id');
        if (startTime >= endTime) {
            addNewSlot = false;
            toastr.error('Please select appropriate time slots');
            return false;
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
            previewSlotBlock.find('.slot-timmings-location').html(`${startTime.format('hh:mm A')} - ${endTime.format('hh:mm A')}`);
            previewSlotBlock.find('.start-time-data-location').val(startTime.format('HH:mm'));
            previewSlotBlock.find('.end-time-data-location').val(endTime.format('HH:mm'));
            previewSlotBlock.find('.ws_selected_users').html(slotTemplate);
            previewSlotBlock.find('.ws_hidden_fields').html(hiddenTemplate);
            editSlotBlock.remove();

            // let mainTableUpdatedSlotIds = [];
            // if($('#edit-slot-block-location').attr('data-from') == 'mainTable'){
            //     mainTableUpdatedSlotIds.push(slotId);
            //     $("#mainTableUpdatedSlotIds").val(slotId);
            // }
            var checkSlotIdExists = $("#mainTableUpdatedSlotIds").val();
           if(previewSlotBlock.attr('data-from') == 'mainTable'){
                if(checkSlotIdExists != ''){
                    checkSlotIdExists+= ","+slotId;
                    $("#mainTableUpdatedSlotIds").val(checkSlotIdExists);    
                }else{
                    $("#mainTableUpdatedSlotIds").val(slotId);
                }
                var mainSlot = slotId;
           }
            
            
            $.ajax({
                url: saveLocationSlotsTemp,
                type: 'POST',
                dataType: 'json',
                data: {
                    'id'        : slotId,
                    'locationId': $('#add-location-slot-model-box').attr('data-id'),
                    'from'      :  previewSlotBlock.attr('data-from'),
                    'companyId' : $("#company_id").val(),
                    'startTime' : startTime.format('HH:mm'),
                    'endTime'   : endTime.format('HH:mm'),
                    'wsId'      : selectedWs,
                    'day'       : dayKey,
                    'mainSlots' : mainSlot //slotId,
                }
            }).done(function(data) {
            }).fail(function(error) {
            })
        }
    });
    // close edit block
    $(document).on('click', '.cancel-edit-slot-location', function(e) {
        var wrapper = $(this).parents('.edit-slot-block-location');
        wrapper.prev().removeClass('d-none');
        wrapper.remove();
    });
});