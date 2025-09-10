$(document).ready(function () {
    $('.select2').select2({
        placeholder: "Select",
        allowClear: true,
        width: '100%'
    });

    var badge_type = $('#badge_type').val();
    if (badge_type != '' && badge_type != undefined) {
        $('#badge_type').trigger('change');
    }

    var badge_target = $('#badge_target').val();
    if (badge_target != '' && badge_target != undefined) {
        $('#badge_target').trigger('change');
    }
    $('#will_badge_expire').trigger('change');
    $("#target_values").focusout(function () { $(this).val($.trim($(this).val()).replace(/^0+/, '')); });

    var extype = $('#excercise_type').val();
    if (extype != '' && extype != undefined) {
        $('#excercise_type').trigger('change');
    }

    $('input[type="file"]').change(function (e) {
        var fileName = e.target.files[0].name;
        if (fileName.length > 40) {
            fileName = fileName.substr(0, 40);
        }
        var allowedMimeTypes = ['image/png', 'image/jpeg', 'image/jpg'];
        if (!allowedMimeTypes.includes(e.target.files[0].type)) {
            toastr.error(message.image_valid_error);
            $(e.currentTarget).empty().val('');
            $(this).parent('div').find('.custom-file-label').val('');
        } else if (e.target.files[0].size > 2097152) {
            toastr.error(message.image_size_2M_error);
            $(e.currentTarget).empty().val('');
            $(this).parent('div').find('.custom-file-label').val('');
        } else {
            $(this).parent('div').find('.custom-file-label').html(fileName);
        }
    });


    $("#logo").change(function () {
        var id = '#previewImg';
        readURL(this, id);
    });
    $('#badge_type').change(function (e){
        var selectedVal = $("#badge_type option:selected").val();
        if(selectedVal == 'ongoing'){
            var ongoingChallengeTarget = JSON.parse(data.ongoingChallengeTarget);
            $('#badge_target').empty();
            $.each(ongoingChallengeTarget, function(key, value) {
                $('#badge_target').append('<option value="' + key + '">' + value + '</option>');
            });
            $('.generalbadgediv').hide().addClass('d-none');
        } else {
            var challengeTargets = JSON.parse(data.challenge_targets);
            $('#badge_target').empty();
            $.each(challengeTargets, function(key, value) {
                $('#badge_target').append('<option value="' + key + '">' + value + '</option>');
            });
            $('.generalbadgediv').show().removeClass('d-none');
        }
    });
    $("#badge_target").change(function (e){
        if($(this).val() != '')
        {
            var selectedVal = '';
            selectedVal = $("#badge_target option:selected").text();

            var uom = JSON.parse(data.uom_data);
            $('#uom').empty();
            $.each(uom[selectedVal], function(key, value) {
                $('#uom').append('<option value="' + key + '">' + value + '</option>');
            });

            $.each(uom[selectedVal], function(key, value) {
                $('#uom').select2('val', key);
            });

            if(selectedVal == "Exercises") {
                $('#excercise_type').attr('disabled', false);
                $('.excercise_type').removeClass('d-none');
                $("#uom").select2("val", "");
            } else {
                $('#excercise_type').attr('disabled', true);
                $("#excercise_type").select2("val", "");
                $('.excercise_type').addClass('d-none');
                $('#uom').attr('disabled', true);
            }

        }
    });

    $('#badge_type').change(function (e) {
      	if($(this).val() != '')
    	{
    		if($(this).val() == 'challenge')
    		{
    			$('.badge_target').removeClass('d-none');
                $('.uom').removeClass('d-none');
                $('#will_badge_expire').prop("checked",false);
                $('.expire_days').addClass('d-none');
                $('#no_of_days').val('');
                $('#will_badge_expire').prop("disabled",true);
                $('.willExpireVisibility').addClass("d-none");
    		} else if($(this).val() == 'general') {
    			$('.badge_target').removeClass('d-none');
                $('.uom').removeClass('d-none');
                $('.willExpireVisibility').removeClass('d-none');
                $('#will_badge_expire').prop("disabled",false);
    		} else if($(this).val() == 'course') {
    			$('.badge_target').addClass('d-none');
    			$('.excercise_type').addClass('d-none');
                $('.uom').addClass('d-none');
    			$('#badge_target').val('');
    		}
    		$('#badge_target').trigger('change');
    	}
    });

    $('#will_badge_expire').change(function (e){
        if($('#will_badge_expire').is(':checked')) {
            $('.expire_days').removeClass('d-none');
        } else {
            $('.expire_days').addClass('d-none');
            $('#no_of_days').val('');
        }
    });

    $("#excercise_type").change(function (e){
        if($(this).val() != '')
        {
            var exerciseTypes = JSON.parse(data.exerciseType);
            var selectedVal = exerciseTypes[$(this).val()];
            var uom = JSON.parse(data.uom_data);
            $("#uom").select2("val", "");
            $('#uom').empty();

            if(selectedVal != 'both') {
                $('#uom').attr('disabled', true);
                $('#uom').append('<option value="' + selectedVal + '">' + selectedVal.charAt(0).toUpperCase() + selectedVal.slice(1) + '</option>');
                $('#uom').select2('val', selectedVal);
            } else {
                $('#uom').attr('disabled', false);
                $.each(uom['Exercises'], function(key, value) {
                    $('#uom').append('<option value="' + key + '">' + value + '</option>');
                });
                $("#uom").select2();
            }
        }
    });

    $(document).on('click', '#badgeFormsubmit', function(){
        if($("#badgeAdd").valid()) {
            if($('#badge_type').val() == ''){
                event.preventDefault();
                toastr.error(message.badge_type_required);
            }
            if($('#badge_target').val() == "4" && $('#uom').val() == null){
                event.preventDefault();
                toastr.error(message.unit_of_measurement);
            }
            $('#unite').val($('#uom').val());
            $('#unite1').val($('#badge_type').val());
        }
    });
});
//--------- preview image
function readURL(input, previewElement) {
    if (input && input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
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
                    $(input).parent('div').find('.custom-file-label').html('Choose File');
                    $(previewElement).removeAttr('src');
                    toastr.error(message.upload_image_dimension);
                    readURL(null, previewElement);
                }
            }
            $('#previewImg').attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        $(previewElement).removeAttr('src');
    }
};