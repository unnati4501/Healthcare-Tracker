function readURL(input, previewElement) {
    if (input && input.files.length > 0) {
        var reader = new FileReader();
        reader.onload = function(e) {
            // Validation for image max height / width and Aspected Ratio
            var image = new Image();
            image.src = e.target.result;
            image.onload = function () {
                var imageWidth = $(input).data('width');
                var imageHeight = $(input).data('height');
                var ratio = $(input).data('ratio');
                var aspectedRatio = ratio;
                var ratioSplit = ratio.split(':');
                var newWidth = ratioSplit[0];
                var newHeight = ratioSplit[1];
                var ratioGcd = gcd(this.width, this.height, newHeight, newWidth);
                if((this.width < imageWidth && this.height < imageHeight) || ratioGcd != aspectedRatio){
                    $(input).empty().val('');
                    $(input).parent('div').find('.invalid-feedback').hide();
                    $(previewElement).removeAttr('src');
                    toastr.error(message.upload_image_dimension);
                    readURL(null, previewElement);
                }
            }
            $(previewElement).attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        $(previewElement).attr('src', defaultCourseImg);
    }
}
$(document).ready(function() {
    $('#availability').change(function() {
        var value = $(this).val();
        if(value == '2') {
            $('[data-availability-dates-wrapper]').show();
        } else {
            $('[data-availability-dates-wrapper]').hide();
            $('.custom-leave-from-date, .custom-leave-to-date').val('').removeClass('is-valid is-invalid');
        }
    });
    $('#from_date_1').datepicker({
        startDate: today,
        endDate: endDate,
        autoclose: true,
        todayHighlight: true,
        format: 'yyyy-mm-dd',
    }).on('changeDate', function () {
        $('#to_date_1').datepicker('setStartDate', new Date($(this).val()));
        $('#from_date_1').valid();
    });

    $('#to_date_1').datepicker({
        startDate: today,
        endDate: endDate,
        autoclose: true,
        todayHighlight: true,
        format: 'yyyy-mm-dd',
    }).on('changeDate', function () {
        $('#from_date_1').datepicker('setEndDate', new Date($(this).val()));
        $('#to_date_1').valid();
    });
    $('#date_of_birth').datepicker({
        endDate: minDOBDate,
        autoclose: true,
        todayHighlight: false,
        format: 'yyyy-mm-dd',
    });
    $("#date_of_birth").keypress(function(event) {
        event.preventDefault();
    });
    //Add multiple custom leave boxes
    $(document).on('click', '.addCustomLeaveDates', function() {
        var totalCustomLeavesInForm = $(".zevo_form_submit").find('.custom-leave-wrap');
        if (totalCustomLeavesInForm.length >= 5) {
            $('.toast').remove();
            toastr.warning('Five custom leaves have been added, not allowed to add more.');
            // Prevent from adding more leaves.
            return;
        }

        // Get previous form value
        var currentFormId = $('#total-form-custom-leaves').val();
        // Increase form value for next iteration.
        currentFormId++;
        // var previousFormId = currentFormId - 1;
        // Get last custom leave html source
        var $lastItem = $('.custom-leave-wrap').last();
        console.log($lastItem);
        var previousFormId = $lastItem.attr('data-order');
        // Create new clone from lastItem
        var $newItem = $lastItem.clone(true);
        // Insert clone html after last custom leave html
        $newItem.insertAfter($lastItem);
        // Leave id increment logic
        $newItem.find(':input').each(function() {
        var name = $(this).attr('name');
        if (name) {
            var name = $(this).attr('name').replace('[' + (previousFormId) + ']', '[' + currentFormId + ']');
            //var id = $(this).attr('id').replace('[' + (previousFormId) + ']', '[' + currentFormId + ']');
            var id = $(this).attr('id').replace(previousFormId, currentFormId);
            // Clean name and id attribute of previous element. DO NOT REMOVE BELLOW LINE, Otherwise it remain old data of previous input.
            $(this).attr({
                'name': name,
                'id': id,
                'data-previewelement': currentFormId,
                'aria-describedby': name+'-error'
            }).data('previewelement', currentFormId).val('').removeAttr('checked');
        }
        });
        $newItem.find("input.datepicker")
            .removeClass('hasDatepicker')
            .removeData('datepicker')
            .unbind()
            .datepicker({
                startDate: today,
                endDate: endDate,
                autoclose: true,
                todayHighlight: true,
                format: 'yyyy-mm-dd',
            });
        // This is used for identify current raw of leave.
        $newItem.closest('.custom-leave-wrap').attr('data-order', currentFormId);
            $('#total-form-custom-leaves').val(currentFormId);
    });
    $('body').on('click', '.delete-custom-leave', function() {
        var customLeaveSelector = $(this).closest('.custom-leave-wrap');
        var totalCustomLeaveInForm = $(".zevo_form_submit").find('.custom-leave-wrap');
        if (totalCustomLeaveInForm.length == 1) {
            // toastr.error("custom leave has been delete");
        } else {
            customLeaveSelector.remove();
            $('#total-form-custom-leaves').val($(".zevo_form_submit").find('.custom-leave-wrap').length);
            //toastr.error(singleDeleteMessage);
        }
    });
    $(document).on('change', '#profileImage, #counsellor_cover', function(e) {
        var previewElement = $(this).data('previewelement');
        if (e.target.files.length > 0) {
            var fileName = e.target.files[0].name,
                allowedMimeTypes = ['image/png', 'image/jpeg', 'image/jpg'];
            if (!allowedMimeTypes.includes(e.target.files[0].type)) {
                toastr.error(messages.image_valid_error);
                $(e.currentTarget).empty().val('');
                readURL(null, previewElement);
            } else if (e.target.files[0].size > 2097152) {
                toastr.error(messages.image_size_2M_error);
                $(e.currentTarget).empty().val('');
                readURL(null, previewElement);
            } else {
                readURL(e.target, previewElement);
                $(this).parent('div').find('.custom-file-label').html(fileName);
            }
        } else {
            readURL(null, previewElement);
        }
    });
    $("#user_services").treeMultiselect({
        enableSelectAll: false,
        searchable: false,
        startCollapsed: false,
        freeze:true,
    });
});