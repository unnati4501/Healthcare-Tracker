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
    var bsDefaults = {
            offset: false,
            overlay: true,
            width: '330px'
        },
        bsMain = $('.bs-offset-main'),
        bsOverlay = $('.bs-canvas-overlay'),
        calendarDate = null;
    SPECIFICDATE = new Date();
    $(document).on('click', '[data-toggle="canvas"][aria-expanded="false"]', function() {
        var canvas = $(this).data('target'),
            opts = $.extend({}, bsDefaults, $(canvas).data()),
            prop = $(canvas).hasClass('bs-canvas-right') ? 'margin-right' : 'margin-left';
        if (opts.width === '100%') opts.offset = false;
        $(canvas).css('width', opts.width);
        if (opts.offset && bsMain.length) bsMain.css(prop, opts.width);
        $(canvas + ' .bs-canvas-close').attr('aria-expanded', "true");
        $('[data-toggle="canvas"][data-target="' + canvas + '"]').attr('aria-expanded', "true");
        if (opts.overlay && bsOverlay.length) bsOverlay.addClass('show');
        return false;
    });
    $('.bs-canvas-close, .bs-canvas-overlay').on('click', function() {
        var canvas, aria;
        if ($(this).hasClass('bs-canvas-close')) {
            canvas = $(this).closest('.bs-canvas');
            aria = $(this).add($('[data-toggle="canvas"][data-target="#' + canvas.attr('id') + '"]'));
            if (bsMain.length) bsMain.css(($(canvas).hasClass('bs-canvas-right') ? 'margin-right' : 'margin-left'), '');
        } else {
            canvas = $('.bs-canvas');
            aria = $('.bs-canvas-close, [data-toggle="canvas"]');
            if (bsMain.length) bsMain.css({
                'margin-left': '',
                'margin-right': ''
            });
        }
        canvas.css('width', '');
        aria.attr('aria-expanded', "false");
        if (bsOverlay.length) bsOverlay.removeClass('show');
        return false;
    });

    $('.bs-calendar-slidebar').click(function() {
        var wsId = $(this).attr('id');
        $('#calendar_offcanvas').attr('location-id', $('#location-specific-ws-slot').attr('data-id'));
        $('#calendar_offcanvas').attr('ws-id', wsId);
        $('input[name=wsId]').val(wsId);
        $('#calendar_offcanvas').find('.js-events').find('.list-group-item:first').html(' ');
        $('#calendar_offcanvas').find('.js-today').trigger('click');
        $('#calendar_offcanvas').bsCalendar('refresh');
    });

    $(document).on('click', '.bs-calendar-slidebar', function() {
        var wsId = $(this).attr('id');
        $('#calendar_offcanvas').attr('location-id', $('#location-specific-ws-slot').attr('data-id'));
        $('#calendar_offcanvas').attr('ws-id', wsId);
        $('input[name=wsId]').val(wsId);
        $('#calendar_offcanvas').find('.js-events').find('.list-group-item:first').html(' ');
        $('#calendar_offcanvas').find('.js-today').trigger('click');
        $('#calendar_offcanvas').bsCalendar('refresh');
    });
    //   $.bsCalendar.setDefault('width', 5000);
    //   $('#calendar_offcanvas').bsCalendar({width: '80%'});
    $(document).on('click', '.add-specific-ws-hours', function() {
        $(this).parents('.js-collapse').find('.add-spefic-new-slot-block').remove();
        var template = $('#add-specific-new-slot-template').text().trim();
        $(this).parents('.js-collapse').find('.new-specific-time-slots').append(template);
        var wrapper = $(this).parents('.js-collapse').find('.slot-control-block'),
            timeselector = wrapper.find('.time');
        makeTimePicker(timeselector, wrapper);
    });
    // to store new slot
    $(document).on('click', '.save-specific-new-slot', function(e) {
        var wrapper = $(this).parents('.add-spefic-new-slot-block').find('.slot-control-block');
        $('.toast').remove();
        var template = $('#specific-preview-slot-template').text().trim(),
            wsHiddenTemplate = $('#preview-ws-hidden-template').text().trim(),
            dayWrapper = $(this).parents('.set-availability-box'),
            dayKey = (dayWrapper.data('dayKey') || ""),
            slotActionBlock = $(this).parents('.slot-action-block'),
            slotsHtmlWrapper = $(this).parents('.js-collapse').find('.js-events').find('.list-group-item:first'),
            controls = wrapper.find('.time'),
            startTime = moment($(controls[0]).timepicker('getTime')),
            endTime = moment($(controls[1]).timepicker('getTime')),
            addNewSlot = true,
            otherSlots = $(this).parents('.js-collapse').find('.specific-preview-slot-block input').serializeArray();
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
            var _hoursby = $('#set_hours_by').val();
            var _availabilityby = $('#set_availability_by').val();
            var wellbeingId = $('input[name=wsId]').val();
            var highlightdate = moment(SPECIFICDATE).format("YYYY-MM-DD");
            var parseDate = Date.parse(highlightdate);
            var id = `id${Date.now()}`;
            var count = 1;
            var template = $('#specific-preview-slot-template').text().trim();
            var location_id = $('#location-specific-ws-slot').attr('data-id');
            template = template.replace(/\#key#/g, wellbeingId).replace(/\#start_time#/g, startTime.format('HH:mm')).replace(/\#end_time#/g, endTime.format('HH:mm')).replace(/\#date_stamp#/g, parseDate).replace('#time#', `${startTime.format('hh:mm A')} - ${endTime.format('hh:mm A')}`).replace(/\#id#/g, id);
            slotsHtmlWrapper.append(template);
            if (_hoursby == 2 && _availabilityby == 2) {
                var templateInput = $('#location-specific-preview-slot-input-template').text().trim();
                templateInput = templateInput.replace(/\#key#/g, wellbeingId).replace(/\#location_id#/g, location_id).replace(/\#start_time#/g, startTime.format('HH:mm')).replace(/\#end_time#/g, endTime.format('HH:mm')).replace(/\#date_stamp#/g, parseDate).replace(/\#date_input#/g, highlightdate).replace(/\#id#/g, id);
            } else {
                var templateInput = $('#specific-preview-slot-input-template').text().trim();
                templateInput = templateInput.replace(/\#key#/g, wellbeingId).replace(/\#start_time#/g, startTime.format('HH:mm')).replace(/\#end_time#/g, endTime.format('HH:mm')).replace(/\#date_stamp#/g, parseDate).replace(/\#date_input#/g, highlightdate).replace(/\#id#/g, id);
            }
            $('#calendar_offcanvas').parent().find('.hiddenfields').append(templateInput);
            $(this).parents('.add-spefic-new-slot-block').remove();
            $.fn.bsCalendar();
        }
    });
    // remove slot button click show remove confirmation modal
    $(document).on('click', '.specific-remove-slot', function(e) {
        //$('#remove-specific-slot-model-box').attr('selector', $(this).data('id')).modal('show');
        $("#location-specific-ws-slot").css({"z-index": "1040"});
        $('#remove-specific-slot-model-box').attr('selector', $(this).data('id')).modal('show');
        $('#remove-specific-slot-model-box').css({"z-index": "1060"});
        //$("#remove-specific-slot-model-box").attr('data-backdrop', 'static'); commented as not working and outside delete popup closed that shouldn't
    });

    $(document).on('hidden.bs.modal', '#remove-specific-slot-model-box', function(e) {
        $('#remove-specific-slot-model-box').data('selector', undefined);
        $("#location-specific-ws-slot").css({"z-index": "1055"});
    });

    // remove button click of confirmation modal
    $('#remove-specific-slot-confirm').on('click', function(e) {
        var _this = $(this).parents('#remove-specific-slot-model-box').attr('selector');
        if (_this) {
            $('.specific_' + _this).remove();
            $('.hiddenfields').find('#' + _this).remove();
            $('#remove-specific-slot-model-box').modal('hide');
        }
    });
    $(document).on('click', '.close-specific-add-new-slot', function() {
        $(this).parents('.add-spefic-new-slot-block').remove();
    });
    $(document).on('click', '.edit-specific-slot', function() {
        var currentSlotWrapper = $(this).parents('.specific-preview-slot-block'),
            slotsWrapper = currentSlotWrapper.parents('.js-events'),
            addNewSlotBlock = slotsWrapper.find('.add-new-slot-block'),
            editSlotBlock = slotsWrapper.find('.edit-slot-block'),
            // id = (currentSlotWrapper.data('id') || 0),
            startTime = (currentSlotWrapper.find('.start-time-data').val() || 0),
            endTime = (currentSlotWrapper.find('.end-time-data').val() || 0),
            slotId = $(this).parents('.specific-preview-slot-block').data('id'),
            specificId = currentSlotWrapper.data('id');
        var template = $('#specific-edit-slot-template').text().trim();
        template = template.replace("#id#", specificId).replace('#start_time#', startTime).replace('#end_time#', endTime);
        $(template).insertAfter(currentSlotWrapper);
        currentSlotWrapper.addClass('d-none');
        var slotControlBlock = $(this).parents('.js-collapse').find('.slot-control-block'),
            timeselector = slotControlBlock.find('.time');
        makeTimePicker(timeselector, slotControlBlock);
        $(timeselector[0]).timepicker('setTime', startTime);
        $(timeselector[1]).timepicker('setTime', endTime);
    });
    // save edited bloc
    $(document).on('click', '.save-specific-edit-slot', function(e) {
        var editSlotBlock = $(this).parents('.specific-edit-slot-block'),
            slotsWrapper = $(this).parents('.slots-wrapper'),
            dayWrapper = $(this).parents('.set-availability-box'),
            dayKey = (dayWrapper.data('dayKey') || ""),
            slotControlBlock = editSlotBlock.find('.slot-control-block');
        $('.toast').remove();
        var previewSlotBlock = editSlotBlock.prev(),
            controls = editSlotBlock.find('.time'),
            startTime = moment($(controls[0]).timepicker('getTime')),
            endTime = moment($(controls[1]).timepicker('getTime')),
            allowEditSlot = true,
            otherSlots = $(this).parents('.js-collapse').find('.specific-preview-slot-block').not(previewSlotBlock).find('input[type=hidden]').serializeArray(),
            slotId = $(this).parents('.specific-edit-slot-block').data('id');
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
            var wellbeingId = $('input[name=wsId]').val();
            var highlightdate = moment(SPECIFICDATE).format("YYYY-MM-DD");
            var parseDate = Date.parse(highlightdate);
            var specificId = editSlotBlock.data('id');
            $('#' + specificId).val(startTime.format('HH:mm') + '-' + endTime.format('HH:mm'));
            previewSlotBlock.removeClass('d-none');
            previewSlotBlock.find('p:first').html(`${startTime.format('hh:mm A')} - ${endTime.format('hh:mm A')}`);
            previewSlotBlock.find('.start-time-data').val(startTime.format('HH:mm'));
            previewSlotBlock.find('.end-time-data').val(endTime.format('HH:mm'));
            editSlotBlock.remove();
        }
    });
    $(document).on('click', '.cancel-specific-edit-slot', function() {
        var wrapper = $(this).parents('.specific-edit-slot-block');
        wrapper.prev().removeClass('d-none');
        wrapper.remove();
    });
    $(document).on('click', '.slot-calendar', function() {
        $('#companyspecificview #ws_name').hide();
        var _wsId = $(this).attr('id');
        var _wsName = $(this).parents('tr').find('td:first').text();
        var _token = $('input[name="_token"]').val();
        var _hoursby = $('#set_hours_by').val();
        var _availabilityby = $('#set_availability_by').val();
        var location_id = $(this).attr('data-locationid');
        $.ajax({
            url: getSpecificSlots,
            method: 'POST',
            data: {
                _token: _token,
                company_id: companyId,
                ws_id: _wsId,
                location_id: (_hoursby == 2 && _availabilityby == 2) ? location_id : null,
            },
            success: function(result) {
                if (result.status) {
                    if(_hoursby == 2 && _availabilityby == 2){
                        $('#companyspecificview #ws_name').show();
                    }
                    $('#company-specific-slots-view').find('#modal_title').html(_wsName + "'s Availabilities");
                    $("#companyspecificview").dataTable().fnDestroy();
                    $('#companyspecificview').find('tbody').html(result.data);
                    $('#companyspecificview').DataTable({
                        lengthChange: false,
                        pageLength: 10,
                        autoWidth: false,
                        columns: [{
                            data: 'date',
                            name: 'date'
                        }, {
                            data: 'wellbeing_specialist',
                            name: 'wellbeing_specialist',
                            visible: (_hoursby == 2 && _availabilityby == 2) ? true : false
                        }, {
                            data: 'time',
                            name: 'time'
                        }],
                        order: [[0, 'asc']],
                        paging: true,
                        searching: true,
                        ordering: true,
                        info: true,
                        columnDefs: [{
                            "targets": 'no-sort',
                            "orderable": false,
                        }],
                        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
                        language: {
                            "paginate": {
                                "previous": pagination.previous,
                                "next": pagination.next
                            },
                             "sInfo": "Entries _START_ to _END_",
                             "infoFiltered": ""
                        }
                    });
                    $('#company-specific-slots-view').attr('selector', _wsId).modal('show');
                } else {
                    toastr.error(result.data);
                }
            }
        });
        
    })
    $('#calendar_offcanvas').bsCalendar({
        locale: 'en',
        url: null, // save as data-bs-target
        width: '330px',
        icons: {
            prev: 'fas fa-arrow-left fa-fw',
            next: 'fas fa-arrow-right fa-fw',
            eventEdit: 'fas fa-edit fa-fw',
            eventRemove: 'fas fa-trash fa-fw'
        },
        showEventEditButton: false,
        showEventRemoveButton: false,
        formatEvent: function(event) {
            
            return drawEvent(event);
        },
        formatNoEvent: function(date) {
            var wellbeingId = $('input[name=wsId]').val();
            var highlightdate = moment(SPECIFICDATE).format("YYYY-MM-DD");
            var parseDate = Date.parse(highlightdate);
            var customtemplate = "";
            var _hoursby = $('#set_hours_by').val();
            var _availabilityby = $('#set_availability_by').val();
            if (_hoursby == 2 && _availabilityby == 2) {
                var location_id = $('#location-specific-ws-slot').attr('data-id');
                if ($(".location_slots_" + location_id + "_" + wellbeingId + "_" + parseDate).length > 0) {
                    $(".location_slots_" + location_id + "_" + wellbeingId + "_" + parseDate).each(function(key, value) {
                        var template = $('#specific-preview-slot-template').text().trim();
                        var value = $(this).val().split('-');
                        var id = $(this).attr('id');
                        var startTime = Date.parse(highlightdate + ' ' + value[0]);
                        var endTime = Date.parse(highlightdate + ' ' + value[1]);
                        var flag = 'block';
                        if(Date.parse(moment(SPECIFICDATE).format("YYYY-MM-DD")) < Date.parse(moment(Date()).format("YYYY-MM-DD"))) {
                            flag = 'none';
                        }
                        customtemplate += template.replace(/\#key#/g, wellbeingId).replace(/\#start_time#/g, moment(startTime).format('HH:mm')).replace(/\#end_time#/g, moment(endTime).format('HH:mm')).replace('#time#', `${moment(startTime).format('hh:mm A')} - ${moment(endTime).format('hh:mm A')}`).replace(/\#date_stamp#/g, parseDate).replace(/\#id#/g, id).replace(/\#previous_slot_block#/g, flag);
                    });
                    return customtemplate;
                }
            } else {
                if ($(".slots_" + wellbeingId + "_" + parseDate).length > 0) {
                    $(".slots_" + wellbeingId + "_" + parseDate).each(function(key, value) {
                        var template = $('#specific-preview-slot-template').text().trim();
                        var value = $(this).val().split('-');
                        var id = $(this).attr('id');
                        var startTime = Date.parse(highlightdate + ' ' + value[0]);
                        var endTime = Date.parse(highlightdate + ' ' + value[1]);
                        var flag = 'block';
                        if(Date.parse(moment(SPECIFICDATE).format("YYYY-MM-DD")) < Date.parse(moment(Date()).format("YYYY-MM-DD"))) {
                            flag = 'none';
                        }
                        customtemplate += template.replace(/\#key#/g, wellbeingId).replace(/\#start_time#/g, moment(startTime).format('HH:mm')).replace(/\#end_time#/g, moment(endTime).format('HH:mm')).replace('#time#', `${moment(startTime).format('hh:mm A')} - ${moment(endTime).format('hh:mm A')}`).replace(/\#date_stamp#/g, parseDate).replace(/\#id#/g, id).replace(/\#previous_slot_block#/g, flag);
                    });
                    return customtemplate;
                }
            }
        },
        queryParams: function(params) {
            return params;
        },
        onClickEditEvent: function(e, event) {},
        onClickDeleteEvent: function(e, event) {},
    });

    
});