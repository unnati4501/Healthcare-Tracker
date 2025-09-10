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
        if ($(this).parents('.slots-wrapper').find('.add-new-slot-block').length == 0) {
            var template = $('#add-new-slot-template').text().trim();
            $(this).parents('.slots-wrapper').append(template);
            var wrapper = $(this).parents('.slots-wrapper').find('.slot-control-block'),
                timeselector = wrapper.find('.time');
            makeTimePicker(timeselector, wrapper);
        }
    });
    // to store new slot
    $(document).on('click', '.save-new-slot', function(e) {
        var wrapper = $(this).parents('.add-new-slot-block').find('.slot-control-block'),
            diffInMilliseconds = (parseInt($(wrapper).datepair('getTimeDiff')) || 0);
        $('.toast').remove();
        if (diffInMilliseconds > 0) {
            var template = $('#preview-slot-template').text().trim(),
                dayWrapper = $(this).parents('.set-availability-box'),
                dayKey = (dayWrapper.data('dayKey') || ""),
                slotsWrapper = $(this).parents('.slots-wrapper'),
                slotActionBlock = $(this).parents('.slot-action-block'),
                controls = wrapper.find('.time'),
                startTime = moment($(controls[0]).timepicker('getTime')),
                endTime = moment($(controls[1]).timepicker('getTime')),
                addNewSlot = true,
                otherSlots = slotsWrapper.find('.preview-slot-block input').serializeArray();
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
                slotsWrapper.find('.no-data-block').addClass('d-none'); // startTime.toDate() // endTime.toDate()
                template = template.replace(/\#key#/g, dayKey).replace(/\#start_time#/g, startTime.format('HH:mm')).replace(/\#end_time#/g, endTime.format('HH:mm')).replace('#time#', `${startTime.format('hh:mm A')} - ${endTime.format('hh:mm A')}`).replace(/\#id#/g, `id${Date.now()}`);
                slotActionBlock.find('.close-add-new-slot').trigger('click');
                slotsWrapper.append(template);
                $('#slots_exist').val('1').valid();
            }
        } else {
            toastr.error('Please select valid start and end time.');
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
        var currentSlotWrapper = $(this).parents('.preview-slot-block'),
            slotsWrapper = currentSlotWrapper.parents('.slots-wrapper'),
            addNewSlotBlock = slotsWrapper.find('.add-new-slot-block'),
            editSlotBlock = slotsWrapper.find('.edit-slot-block'),
            // id = (currentSlotWrapper.data('id') || 0),
            startTime = (currentSlotWrapper.find('.start-time-data').val() || 0),
            endTime = (currentSlotWrapper.find('.end-time-data').val() || 0);
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
            $(slotControlBlock).datepair('refresh');
        }
    });
    // save edited bloc
    $(document).on('click', '.save-edit-slot', function(e) {
        var editSlotBlock = $(this).parents('.edit-slot-block'),
            slotsWrapper = $(this).parents('.slots-wrapper'),
            slotControlBlock = editSlotBlock.find('.slot-control-block'),
            diffInMilliseconds = (parseInt($(slotControlBlock).datepair('getTimeDiff')) || 0);
        $('.toast').remove();
        if (diffInMilliseconds > 0) {
            var previewSlotBlock = editSlotBlock.prev(),
                controls = editSlotBlock.find('.time'),
                startTime = moment($(controls[0]).timepicker('getTime')),
                endTime = moment($(controls[1]).timepicker('getTime')),
                allowEditSlot = true,
                otherSlots = slotsWrapper.find('.preview-slot-block').not(previewSlotBlock).find('input').serializeArray();
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
                previewSlotBlock.removeClass('d-none');
                previewSlotBlock.find('.slot-timmings').html(`${startTime.format('hh:mm A')} - ${endTime.format('hh:mm A')}`);
                previewSlotBlock.find('.start-time-data').val(startTime.format('HH:mm'));
                previewSlotBlock.find('.end-time-data').val(endTime.format('HH:mm'));
                editSlotBlock.remove();
            }
        } else {
            toastr.error('Please select valid start and end time.');
        }
    });
    // close edit block
    $(document).on('click', '.cancel-edit-slot', function(e) {
        var wrapper = $(this).parents('.edit-slot-block');
        wrapper.prev().removeClass('d-none');
        wrapper.remove();
    });
});