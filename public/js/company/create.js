$(document).ready(function() {
    $companyAddForm.validate().settings.ignore = ":disabled,:hidden";
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(function() {
        if($("#companyType").val() == 'reseller'){
            $('#is_branding').prop({'checked': true, 'disabled': true});
            $('#enable_survey').prop({'checked': true, 'disabled': true});
            $('#allow_app').prop({'checked': false, 'disabled': false});
        }
        $('.actions ul li a[href="#previous"]').addClass("disabled");
        
        $('#allow_app').on('change', function(e) {
            if($(this).is(":checked")) {
                $('#disable_sso').prop({'checked': false, 'disabled': true});
            } else {
                $('#disable_sso').prop({'disabled': false});
            }
        });

        if($("#allow_app").is(":checked") == true){
            $('#disable_sso').prop({'checked': false, 'disabled': true});
        }

        $("#group_content").treeMultiselect({
            enableSelectAll: true,
            searchable: true,
            startCollapsed: true,
            onChange: function (allSelectedItems, addedItems, removedItems) {
                var selectedMembers = $('#group_content').val().length;
                if (selectedMembers == 0) {
                    $('#companyAdd').valid();
                    $('#group_content-min-error').hide();
                    $('#group_content-error').show();
                    $('.tree-multiselect').css('border-color', '#f44436');
                } else {
                    $('#group_content-error').hide();
                    $('#group_content-min-error').hide();
                    $('#group_content-max-error').hide();
                    $('.tree-multiselect').css('border-color', '#D8D8D8');
                }
            }
        });
        $("#staffService").hide();

        $("#dt_wellbeing_sp_ids").on("select2:unselect", function (e) {
            var value=   e.params.data.id;
            $("#staffServiceManagment #"+value).remove();

            if($('#staffServiceManagment tbody tr').length == 0){
                $("#staffService").hide();
            }
        });

        $(document).on('click', '#staffServiceManagment .fa-times', function(e) {
            var sid = ($(this).data('sid') || 0);
            var wsid = ($(this).data('wsid') || 0);
            $("#service_"+wsid+"_"+sid).remove();

            if($("td#staff-service-"+wsid).html().trim().length == 0){
                $("#"+wsid).remove();
                var option_value = $('#dt_wellbeing_sp_ids option[value="'+ wsid +'"]');
                option_value.prop('selected', false);
                $('#dt_wellbeing_sp_ids').trigger('change.select2');
            }
            
            if($('#staffServiceManagment tbody tr').length == 0){
                $("#dt_wellbeing_sp_ids").val('').select2()
                $("#staffService").hide();
            }
        });
        
         $(document).on('select2:selecting', '#dt_wellbeing_sp_ids', function(e) {
            var _value = e.params.args.data.id
            //var _value = $(this).val();
            var _token = $('input[name="_token"]').val();
            // if(_value.length <= 0) {
            //     $("#staffService").hide();
            // }
            var html = '';
            $.ajax({
                url: getStaffServices,
                method: 'post',
                data: {
                    _token: _token,
                    value: _value,
                },
                success: function(result) {
                    //if(result.staffServices.length > 0){
                        $("#staffService").show();
                        $.each(result.staffServices, function (key, value) 
                        {
                            html+= '<tr id='+key+'><td>' + value.staffName + '</td><td id="staff-service-'+key+'">';
                            $.each(value.services, function (k, v) 
                            { 
                                html+= '<span class="service-badge" id="service_'+key+'_'+k+'">'+v+'<i class="fal fa-times" data-sid="'+k+'"  data-wsid="'+key+'"></i><input type="hidden" name="service['+key+'][]" value="'+k+'"></span>';
                            });
                            html+= '</td></tr>';
                            
                        });
                        $('#staffServiceManagment').append(html);
                    //}
                    
                }
            });
        });
        /*$(document).on('change', '#dt_wellbeing_sp_ids', function(e) {
            var _value = $(this).val();
            var _token = $('input[name="_token"]').val();
            if(_value.length <= 0) {
                $("#staffService").hide();
            }
            $.ajax({
                url: getStaffServices,
                method: 'post',
                data: {
                    _token: _token,
                    value: _value
                },
                success: function(result) {
                    if(result.staffServices!= null && result.staffServices.length != 'undefined' ){
                        $("#staffService").show();
                        $('#staffServiceManagment tbody').empty();
                        $.each(result.staffServices, function (key, value) 
                        {
                            $('#staffServiceManagment').append('<tr id='+value.user_id+'> <td>' + value.staffName + '</td>  <td>' + value.services + '</td></tr>');
                        });
                    }
                    
                }
            });
        });*/

        /*$('#survey_roll_out_time').timepicker({
            showInputs: false,
            showMeridian: false,
            defaultTime: '00:00'
        });*/

        $("#survey_roll_out_time").timepicker({
            'showDuration': true,
            'timeFormat': 'H:i',
            'step': 15,
            'useSelect': true,
        });

        $('#subscription_start_date').datepicker({
            startDate: start,
            endDate: end,
            autoclose: true,
            todayHighlight: true,
            format: 'yyyy-mm-dd',
        }).on('changeDate', function () {
            $('#subscription_end_date').datepicker('setStartDate', new Date($(this).val()));
            $('#subscription_start_date').valid();
        });

        $('#subscription_end_date').datepicker({
            startDate: start,
            endDate: end,
            autoclose: true,
            todayHighlight: true,
            format: 'yyyy-mm-dd',
        }).on('changeDate', function () {
            $('#subscription_start_date').datepicker('setEndDate', new Date($(this).val()));
            $('#subscription_end_date').valid();
        });

        //Add multiple moderators
        $('#addModerator').on('click', function() {
            // Get previous form value
            var currentFormId = $('#total-moderators').val();
            // Increase form value for next iteration.
            currentFormId++;
            // var previousFormId = currentFormId - 1;
           // Get last moderator html source
            var $lastItem = $('.zevo_form_submit .moderators-wrap').last();
            var previousFormId = $lastItem.attr('data-order');
            // Create new clone from lastItem
            var $newItem = $lastItem.clone(true);
            // Insert clone html after last moderator html
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
            
            // This is used for identify current raw of leave.
            $newItem.closest('.moderators-wrap').attr('data-order', currentFormId);
                $('#total-moderators').val(currentFormId);
        });

        $(document).on('click', '.delete_moderator', function(e) {
            var _id = ($(this).data('id') || 0);
            $('#remove-moderator-box').modal('show');
            $('#remove-moderator-box').attr('data-id',_id);
        
        });

        $(document).on('click', '#remove-moderator-confirm', function(e) {
            var id = $("#remove-moderator-box").attr("data-id");
            if (id) {
                $("tr").filter("[data-id='" + id + "']").remove();
                $('#remove-moderator-box').modal('hide');
                // $('.actions ul li a[href="#next"]').removeClass("disabled");
                if($('.error-feedback').length > 0){
                    $('.actions ul li a[href="#next"]').addClass("disabled");
                }
            }
        });
      });

    $(document).on('change', 'input[id="is_branding"]', function(e) {
        if($(this).is(":checked")) {
            $(".domainBranding-content").removeClass('d-none');
        } else {
            $(".domainBranding-content").addClass('d-none');
        }
    });

    $(document).on('change', 'input[id="enable_survey"]', function(e) {
        if($(this).is(":checked")) {
            $(".enableSurvey-content").removeClass('d-none');
        } else {
            $(".enableSurvey-content").addClass('d-none');
        }
    });

    /*$(document).on('change', 'input[id="eap_tab"]', function(e) {
        if($(this).is(":checked")) {
            $(".enableDigitalTherapy-content").removeClass('d-none');
        } else {
            $(".enableDigitalTherapy-content").addClass('d-none');
        }
    });*/

    if($("#companyType").val() != 'zevo'){
        $(document).on('change', '#companyplan', function(e) {
            if($(this).val() == 11 || $(this).val() == 12){
                $(".enableDigitalTherapy-content").removeClass('d-none');
            }else{
                $(".enableDigitalTherapy-content").addClass('d-none');
            }
        });
    }

    if($("#companyType").val() == 'zevo'){
        $(document).on('change', '#companyplan', function(e) {
            if($(this).val() == 1 || $(this).val() == 2){
                $(".enableDigitalTherapy-content").removeClass('d-none');
            }else{
                $(".enableDigitalTherapy-content").addClass('d-none');
            }
        });
    }
    

    stepObj = $("#companyAddStep").steps({
        headerTag: "h3",
        bodyTag: "div",
        transitionEffect: "fade",
        autoFocus: true,
        enableCancelButton: false,
        startIndex: 0,
        labels: {
            next: "Next",
            previous: "Previous",
            finish: "Finish",
            cancel: "Cancel",
        },
        onStepChanging: function(event, currentIndex, newIndex) {
            if (currentIndex > newIndex) {
                return true;
            }
            return portalDomainFlag && $companyAddForm.valid();

        },
        onStepChanged: function(event, currentIndex, priorIndex) {
            $('.companySteps').removeClass('current').hide();
            if(currentIndex == 1 && priorIndex == 0){
                $(".companyDetails-content").addClass("completed");
                $(".moderatorsDetails-content").addClass("active");
                $('#companyAddStep-p-1').show().addClass('current');
                $('.actions ul li a[href="#previous"]').removeClass("disabled");
                $('.select2').select2({allowClear: true,width: '100%'});
                if($("#first_name_0").val().length > 0 && $("#last_name_0").val().length > 0 && $("#email_0").val().length > 0 && $("#email_0-error").length == 0 && $("#first_name_0-error").length == 0 && $("#last_name_0-error").length == 0 && $('.error-feedback').length <= 0){
                    $('.actions ul li a[href="#next"]').removeClass("disabled");
                }else{
                    $('.actions ul li a[href="#next"]').addClass("disabled");
                }
            }else if(currentIndex == 2 && priorIndex == 1 ){
                if($("#is_branding").is(":checked") == true) {
                    //domain branding 
                    $('#companyAddStep-p-2').show().addClass('current');
                    $(".domainBranding-content").addClass("active");
                } else if($("#enable_survey").is(":checked") == true){
                    //survey branding
                    $('#companyAddStep-p-4').show().addClass('current');
                    $(".enableSurvey-content").addClass("active");
                }else{
                    $('#companyAddStep-p-5').show().addClass('current');
                    $(".enableLocation-content").addClass("active");
                }
                $(".moderatorsDetails-content").addClass("completed");
            }else if(currentIndex == 3 && priorIndex == 2 && $("#parent_company").val() != 'zevo'){
                if($("#is_branding").is(":checked") == true ) {
                    //domain branding 
                    $('#companyAddStep-p-3').show().addClass('current');
                    $(".portalBranding-content").addClass("active");
                } else if($("#enable_survey").is(":checked") == true){
                    //survey branding
                    $('#companyAddStep-p-4').show().addClass('current');
                    $(".enableSurvey-content").addClass("active");
                }else {
                    //location branding
                    $('#companyAddStep-p-5').show().addClass('current');
                    $(".enableLocation-content").addClass("active");
                }
                $('.select2').select2({allowClear: true,width: '100%'});
                $(".domainBranding-content").addClass("completed");
            }else if(currentIndex == 3 && priorIndex == 2 && $("#parent_company").val() == 'zevo'){
                if($('.domainBranding-content').hasClass( "active" ) == true) { 
                    //domain branding
                    if($("#enable_survey").is(":checked") == true){
                        $('#companyAddStep-p-4').show().addClass('current');
                        $(".enableSurvey-content").addClass("active");
                        $(".domainBranding-content").addClass("completed");
                    }else{
                        $('#companyAddStep-p-5').show().addClass('current');
                        $(".enableLocation-content").addClass("active");
                        $(".domainBranding-content").addClass("completed");
                    }
                }else if($('.enableSurvey-content').hasClass( "active" ) == true ) {
                    //domain branding 
                    $('#companyAddStep-p-5').show().addClass('current');
                    $(".enableLocation-content").addClass("active");
                    $(".enableSurvey-content").addClass("completed");
                }else if($('#companyplan').val() == 1 ||  $('#companyplan').val() == 2 ) {
                    //domain branding 
                    $('#companyAddStep-p-6').show().addClass('current');
                    $(".enableDigitalTherapy-content").addClass("active");
                    $(".enableLocation-content").addClass("completed");
                }else {
                    //location branding
                    $('#companyAddStep-p-7').show().addClass('current');
                    $(".enableManageContent-content").addClass("active");
                    $(".enableLocation-content").addClass("completed");
                    $('.actions ul li a[href="#next"]').parent().hide();
                    $('.actions ul li a[href="#finish"]').parent().show();
                }
            }else if(currentIndex == 4 && priorIndex == 3 && $("#parent_company").val() != 'zevo'){
                if($("#enable_survey").is(":checked") == true) {
                    //domain branding 
                    $('#companyAddStep-p-4').show().addClass('current');
                    $(".enableSurvey-content").addClass("active");
                } else {
                    //location branding
                    $('#companyAddStep-p-5').show().addClass('current');
                    $(".enableLocation-content").addClass("active");

                }
                $(".portalBranding-content").addClass("completed");
            }else if(currentIndex == 4 && priorIndex == 3 && $("#parent_company").val() == 'zevo'){
                if($('.domainBranding-content').hasClass( "active" ) == true && $("#enable_survey").is(":checked") == true) {
                    //domain branding 
                    /*$('#companyAddStep-p-4').show().addClass('current');
                    $(".enableSurvey-content").addClass("active");
                    $(".domainBranding-content").addClass("completed");*/
                    $('#companyAddStep-p-5').show().addClass('current');
                    $(".enableLocation-content").addClass("active");
                    $(".enableSurvey-content").addClass("completed");
                }else if($('.enableLocation-content').hasClass( "active" ) == true && ($('.enableDigitalTherapy-content').hasClass( "active" ) == false && ($('#companyplan').val() == 1 || $('#companyplan').val() == 2)) ) {
                        $('#companyAddStep-p-6').show().addClass('current');
                        $(".enableDigitalTherapy-content").addClass("active");
                        $(".enableLocation-content").addClass("completed");
                }else if($('.enableDigitalTherapy-content').hasClass( "active" ) == true ) {
                        $('#companyAddStep-p-7').show().addClass('current');
                        $(".enableManageContent-content").addClass("active");
                        $('.actions ul li a[href="#next"]').parent().hide();
                        $('.actions ul li a[href="#finish"]').parent().show();
                        $(".enableDigitalTherapy-content").addClass("completed");
                }else {
                    $('#companyAddStep-p-7').show().addClass('current');
                    $(".enableManageContent-content").addClass("active");
                    $(".enableLocation-content").addClass("completed");
                    $('.actions ul li a[href="#next"]').parent().hide();
                    $('.actions ul li a[href="#finish"]').parent().show();
                }
            }else if(currentIndex == 5 && priorIndex == 4 && $("#parent_company").val() != 'zevo'){
                    //location branding
                    if($('.enableDigitalTherapy-content').hasClass( "active" ) == true ) {
                        $('#companyAddStep-p-7').show().addClass('current');
                        $(".enableManageContent-content").addClass("active");
                        $('.actions ul li a[href="#next"]').parent().hide();
                        $('.actions ul li a[href="#finish"]').parent().show();
                        $(".enableDigitalTherapy-content").addClass("completed");
                    }else{
                        $('#companyAddStep-p-5').show().addClass('current');
                        $(".enableLocation-content").addClass("active");
                        $(".enableSurvey-content").addClass("completed");
                    }
                    
            }else if(currentIndex == 5 && priorIndex == 4 && $("#parent_company").val() == 'zevo'){
                //location branding
                if($('.enableLocation-content').hasClass( "active" ) == true && ($('.enableDigitalTherapy-content').hasClass( "active" ) == false && ($('#companyplan').val() == 1 || $('#companyplan').val() == 2))) {
                    $('#companyAddStep-p-6').show().addClass('current');
                    $(".enableDigitalTherapy-content").addClass("active");
                    $(".enableLocation-content").addClass("completed");
                }else if($('.enableDigitalTherapy-content').hasClass( "active" ) == true) {
                    $('#companyAddStep-p-7').show().addClass('current');
                    $(".enableManageContent-content").addClass("active");
                    $('.actions ul li a[href="#next"]').parent().hide();
                    $('.actions ul li a[href="#finish"]').parent().show();
                    $(".enableDigitalTherapy-content").addClass("completed");
                }else{
                    $('#companyAddStep-p-7').show().addClass('current');
                    $(".enableManageContent-content").addClass("active");
                    $('.actions ul li a[href="#next"]').parent().hide();
                    $('.actions ul li a[href="#finish"]').parent().show();
                    $(".enableLocation-content").addClass("completed");
                }
                
        }else if(currentIndex == 6 && priorIndex == 5 && $("#parent_company").val() != 'zevo'){
                //location branding
                if($('#companyplan').val() == 1 ||  $('#companyplan').val() == 2 ||  $('#companyplan').val() == 11 ||  $('#companyplan').val() == 12 ) {
                    //domain branding 
                    $('#companyAddStep-p-6').show().addClass('current');
                    $(".enableDigitalTherapy-content").addClass("active");
                } else {
                    //location branding
                    $('#companyAddStep-p-7').show().addClass('current');
                    $(".enableManageContent-content").addClass("active");
                    $('.actions ul li a[href="#next"]').parent().hide();
                    $('.actions ul li a[href="#finish"]').parent().show();
                }
                $(".enableLocation-content").addClass("completed");
            }else if(currentIndex == 6 && priorIndex == 5 && $("#parent_company").val() == 'zevo'){
                //location branding
                
                if($('#companyplan').val() == 1 ||  $('#companyplan').val() == 2) {
                    //domain branding 
                    $('#companyAddStep-p-7').show().addClass('current');
                    $(".enableManageContent-content").addClass("active");
                    $('.actions ul li a[href="#next"]').parent().hide();
                    $('.actions ul li a[href="#finish"]').parent().show();
                    $(".enableDigitalTherapy-content").addClass("completed");
                } 
                
            }else if(currentIndex == 7 && priorIndex == 6){
                //location branding
                $('#companyAddStep-p-7').show().addClass('current');
                $(".enableManageContent-content").addClass("active");
                $('.actions ul li a[href="#next"]').parent().hide();
                $('.actions ul li a[href="#finish"]').parent().show();

                if($('#companyplan').val() == 1 ||  $('#companyplan').val() == 2 || $('#companyplan').val() == 11 || $('#companyplan').val() == 12 ) {
                    $(".enableDigitalTherapy-content").addClass("completed");
                }else{
                    $(".enableLocation-content").addClass("completed");
                }
            }else{

            }

            //Previous fun
            if(currentIndex == 0 && priorIndex == 1){
                $(".companyDetails-content").addClass("active");
                $(".companyDetails-content").removeClass("completed");
                $(".moderatorsDetails-content").removeClass("active");
                $('#companyAddStep-p-0').show().addClass('current');
                $('.actions ul li a[href="#next"]').removeClass("disabled");
                $('.actions ul li a[href="#previous"]').addClass("disabled");
            }else if(currentIndex == 1 && priorIndex == 2 ){
                if($("#is_branding").is(":checked") == true) {
                    //domain branding 
                    $('#companyAddStep-p-2').hide().removeClass('current');
                    $(".domainBranding-content").removeClass("active");
                } else if($("#enable_survey").is(":checked") == true){
                    //survey branding
                    $('#companyAddStep-p-4').hide().removeClass('current');
                    $(".enableSurvey-content").removeClass("active");
                }else{
                    $('#companyAddStep-p-5').hide().removeClass('current');
                    $(".enableLocation-content").removeClass("active");
                }
                $(".moderatorsDetails-content").addClass("active");
                $(".moderatorsDetails-content").removeClass("completed");
                $('#companyAddStep-p-1').show().addClass('current');
                if($("#first_name_0").val().length > 0 && $("#last_name_0").val().length > 0 && $("#email_0").val().length > 0 && $("#email_0-error").length == 0 && $("#first_name_0-error").length == 0 && $("#last_name_0-error").length == 0 && $('.error-feedback').length <= 0){
                    $('.actions ul li a[href="#next"]').removeClass("disabled");
                }else{
                    $('.actions ul li a[href="#next"]').addClass("disabled");
                }

                /*if(($('#moderatorsManagment').find('tr').length!='undefined' && $('#moderatorsManagment').find('tr').length > 1) && $('.error-feedback').length <= 0){
                    $('.actions ul li a[href="#next"]').removeClass("disabled");
                }else{
                    $('.actions ul li a[href="#next"]').addClass("disabled");
                }*/
                
            }else if(currentIndex == 2 && priorIndex == 3 && $("#parent_company").val() != 'zevo'){
                if($("#is_branding").is(":checked") == true ) { //&& $('input[name="is_reseller"]:checked').val() == 'yes'
                    //domain branding 
                    $('#companyAddStep-p-3').hide().removeClass('current');
                    $(".portalBranding-content").removeClass("active");
                } else if($("#enable_survey").is(":checked") == true){
                    //survey branding
                    $('#companyAddStep-p-4').hide().removeClass('current');
                    $(".enableSurvey-content").removeClass("active");
                }else {
                    //location branding
                    $('#companyAddStep-p-5').hide().removeClass('current');
                    $(".enableLocation-content").removeClass("active");
                }
                $(".domainBranding-content").removeClass("completed");
                $(".domainBranding-content").addClass("active");
                $('#companyAddStep-p-2').show().addClass('current');
                
            }else if(currentIndex == 2 && priorIndex == 3 && $("#parent_company").val() == 'zevo'){
                if($("#is_branding").is(":checked") == true ) {
                    //domain branding
                    if($("#enable_survey").is(":checked") == true){
                        $('#companyAddStep-p-4').hide().removeClass('current');
                        $(".enableSurvey-content").removeClass("active");
                        $(".domainBranding-content").removeClass("completed");
                        $(".domainBranding-content").addClass("active");
                        $('#companyAddStep-p-2').show().addClass('current');
                    }else{
                        $('#companyAddStep-p-5').hide().removeClass('current');
                        $(".enableLocation-content").removeClass("active");
                        $(".domainBranding-content").removeClass("completed");
                        $(".domainBranding-content").addClass("active");
                        $('#companyAddStep-p-2').show().addClass('current');
                    }

                }else if($("#enable_survey").is(":checked") == true) {
                    //domain branding 
                    $('#companyAddStep-p-5').hide().removeClass('current');
                    $(".enableLocation-content").removeClass("active");
                    $(".enableSurvey-content").removeClass("completed");
                    $(".enableSurvey-content").addClass("active");
                    $('#companyAddStep-p-4').show().addClass('current');
                }else if($('#companyplan').val() == 1 ||  $('#companyplan').val() == 2 ) {
                    //domain branding 
                    $('#companyAddStep-p-6').hide().removeClass('current');
                    $(".enableDigitalTherapy-content").removeClass("active");
                    $(".enableLocation-content").addClass("active");
                    $('#companyAddStep-p-5').show().addClass('current');
                    $(".enableLocation-content").removeClass("completed");
                }else {
                    //location branding
                    $('#companyAddStep-p-7').hide().removeClass('current');
                    $(".enableManageContent-content").removeClass("active");
                    $(".enableLocation-content").removeClass("completed");
                    $(".enableLocation-content").addClass("active");
                    $('#companyAddStep-p-5').show().addClass('current');
                    $('.actions ul li a[href="#next"]').parent().show();
                    $('.actions ul li a[href="#finish"]').parent().hide();
                }
            }else if(currentIndex == 3 && priorIndex == 4 && $("#parent_company").val() != 'zevo'){
                if($("#enable_survey").is(":checked") == true) {
                    //domain branding 
                    $('#companyAddStep-p-4').hide().removeClass('current');
                    $(".enableSurvey-content").removeClass("active");
                } else {
                    //location branding
                    $('#companyAddStep-p-5').hide().removeClass('current');
                    $(".enableLocation-content").removeClass("active");
                }
                $(".portalBranding-content").removeClass("completed");
                $(".portalBranding-content").addClass("active");
                $('#companyAddStep-p-3').show().addClass('current');
            }else if(currentIndex == 3 && priorIndex == 4 && $("#parent_company").val() == 'zevo'){
                if($('.enableManageContent-content').hasClass( "active" ) == true) {
                    if($('#companyplan').val() == 1 || $('#companyplan').val() == 2){
                        $('#companyAddStep-p-7').hide().removeClass('current');
                        $(".enableManageContent-content").removeClass("active");
                        $(".enableDigitalTherapy-content").removeClass("completed");
                        $(".enableDigitalTherapy-content").addClass("active");
                        $("#companyAddStep-p-6").show().addClass('current');
                    }else{
                        $('#companyAddStep-p-7').hide().removeClass('current');
                        $(".enableManageContent-content").removeClass("active");
                        $(".enableLocation-content").removeClass("completed");
                        $(".enableLocation-content").addClass("active");
                        $("#companyAddStep-p-5").show().addClass('current');
                    }
                }else if($('.enableLocation-content').hasClass( "active" ) == false && $('#companyplan').val() == 1 || $('#companyplan').val() == 2){
                    $('#companyAddStep-p-6').hide().removeClass('current');
                    $(".enableDigitalTherapy-content").removeClass("active");
                    $(".enableLocation-content").removeClass("completed");
                    $(".enableLocation-content").addClass("active");
                    $("#companyAddStep-p-5").show().addClass('current');
                }else if($('.enableLocation-content').hasClass( "active" ) == true){
                    if($('.enableDigitalTherapy-content').hasClass( "active" ) == true &&  $('#companyplan').val() == 1 || $('#companyplan').val() == 2){
                        $('#companyAddStep-p-6').hide().removeClass('current');
                        $(".enableDigitalTherapy-content").removeClass("active");
                        $(".enableLocation-content").removeClass("completed");
                        $(".enableLocation-content").addClass("active");
                        $("#companyAddStep-p-5").show().addClass('current');
                    }else if($("#enable_survey").is(":checked") == true){
                        $('#companyAddStep-p-5').hide().removeClass('current');
                        $(".enableLocation-content").removeClass("active");
                        $(".enableSurvey-content").removeClass("completed");
                        $(".enableSurvey-content").addClass("active");
                        $("#companyAddStep-p-4").show().addClass('current');
                    }
                }else{
                    $('#companyAddStep-p-6').hide().removeClass('current');
                    $(".enableDigitalTherapy-content").removeClass("active");
                    $(".enableLocation-content").removeClass("completed");
                    $(".enableLocation-content").addClass("active");
                    $("#companyAddStep-p-5").show().addClass('current');
                }
            }else if(currentIndex == 4 && priorIndex == 5 && $("#parent_company").val() != 'zevo'){
                    //location branding
                    $('#companyAddStep-p-5').hide().removeClass('current');
                    $(".enableLocation-content").removeClass("active");
                    $(".enableSurvey-content").removeClass("completed");
                    $(".enableSurvey-content").addClass("active");
                    $('#companyAddStep-p-4').show().addClass('current');
            }else if(currentIndex == 4 && priorIndex == 5 && $("#parent_company").val() == 'zevo'){
                //location branding
                if($('.enableManageContent-content').hasClass( "active" ) == true && ($('#companyplan').val() == 1 || $('#companyplan').val() == 2)) {
                    $('#companyAddStep-p-7').hide().removeClass('current');
                    $(".enableManageContent-content").removeClass("active");
                    $('.actions ul li a[href="#next"]').parent().show();
                    $('.actions ul li a[href="#finish"]').parent().hide();
                    $('#companyAddStep-p-6').show().addClass('current');
                    $(".enableDigitalTherapy-content").addClass("active");
                    $(".enableDigitalTherapy-content").removeClass("completed");
                }else if($('#companyplan').val() == 1 || $('#companyplan').val() == 2){
                    $('#companyAddStep-p-6').hide().removeClass('current');
                    $(".enableDigitalTherapy-content").removeClass("active");
                    $('#companyAddStep-p-5').show().addClass('current');
                    $(".enableLocation-content").addClass("active");
                    $(".enableLocation-content").removeClass("completed");
                }else{
                    $('#companyAddStep-p-7').hide().removeClass('current');
                    $(".enableManageContent-content").removeClass("active");
                    $('.actions ul li a[href="#next"]').parent().show();
                    $('.actions ul li a[href="#finish"]').parent().hide();
                    $('#companyAddStep-p-5').show().addClass('current');
                    $(".enableLocation-content").addClass("active");
                    $(".enableLocation-content").removeClass("completed");
                }
            }else if(currentIndex == 5 && priorIndex == 6 && $("#parent_company").val() != 'zevo'){
                //location branding
                if($('#companyplan').val() == 1 ||  $('#companyplan').val() == 2 || $('#companyplan').val() == 11 || $('#companyplan').val() == 12 ) {
                    //domain branding 
                    $('#companyAddStep-p-6').hide().removeClass('current');
                    $(".enableDigitalTherapy-content").removeClass("active");
                } else {
                    //location branding
                    $('#companyAddStep-p-7').hide().removeClass('current');
                    $(".enableManageContent-content").removeClass("active");
                    $('.actions ul li a[href="#next"]').parent().show();
                    $('.actions ul li a[href="#finish"]').parent().hide();
                }
                $(".enableLocation-content").removeClass("completed");
                $(".enableLocation-content").addClass("active");
                $('#companyAddStep-p-5').show().addClass('current');
            }else if(currentIndex == 5 && priorIndex == 6 && $("#parent_company").val() == 'zevo'){
                //location branding
                $('#companyAddStep-p-7').hide().removeClass('current');
                $(".enableManageContent-content").removeClass("active");
                $('.actions ul li a[href="#next"]').parent().hide();
                $('.actions ul li a[href="#finish"]').parent().show();
                if($('#companyplan').val() == 1 ||  $('#companyplan').val() == 2) {
                    $(".enableDigitalTherapy-content").removeClass("completed");
                    $('#companyAddStep-p-6').show().addClass('current');
                    $(".enableDigitalTherapy-content").addClass("active");
                }else{
                    $(".enableLocation-content").removeClass("completed");
                    $(".enableLocation-content").addClass("active");
                    $('#companyAddStep-p-5').show().addClass('current');
                }
            }else if(currentIndex == 6 && priorIndex == 7){
                //location branding
                $('#companyAddStep-p-7').hide().removeClass('current');
                $(".enableManageContent-content").removeClass("active");
                $('.actions ul li a[href="#next"]').parent().show();
                $('.actions ul li a[href="#finish"]').parent().hide();
                if($('#companyplan').val() == 1 ||  $('#companyplan').val() == 2 || $('#companyplan').val() == 11 || $('#companyplan').val() == 12) {
                    $(".enableDigitalTherapy-content").removeClass("completed");
                    $(".enableDigitalTherapy-content").addClass("active");
                    $('#companyAddStep-p-6').show().addClass('current');
                }else{
                    $(".enableLocation-content").removeClass("completed");
                    $(".enableLocation-content").addClass("active");
                    $('#companyAddStep-p-5').show().addClass('current');
                }
            }else{

            }
        },
        onCanceled: function(event) {
            // console.log("Step canceled");
        },
        onFinishing: async function(event, currentIndex) {
        },
        onFinished: function(event, currentIndex) {
            var selectedMembers = $('#group_content').val().length;
            var _token = $('input[name="_token"]').val();
            if (selectedMembers != 0) {
                if($companyAddForm.valid() == true) {
                    event.preventDefault();
                }
                $.ajax({
                    url: contentValidateURL,
                    method: 'post',
                    data: {
                        _token: _token,
                        'content': $('#group_content').val()
                    },
                    success: function(result) {
                        if(result == 0){
                            event.preventDefault();
                            $('#companyAdd').valid();
                            $('#group_content-min-error').hide();
                            $('#group_content-error').show();
                            $('.tree-multiselect').css('border-color', '#f44436');
                        } else {
                            if($companyAddForm.valid() == true) {
                                $companyAddForm.submit();
                            }
                            $('#group_content-error').hide();
                            $('#group_content-min-error').hide();
                            $('#group_content-max-error').hide();
                            $('.tree-multiselect').css('border-color', '#D8D8D8');
                        }
                    }
                });
            } else {
                    event.preventDefault();
                    $companyAddForm.valid();
                    $('#group_content-min-error').hide();
                    $('#group_content-error').show();
                    $('.tree-multiselect').css('border-color', '#f44436');
            }
        },
    });

    $('#survey_roll_out_day').change(displayUpcomingSurveyDetails);
    $('#survey_roll_out_time').change(displayUpcomingSurveyDetails);
    $('#survey_frequency').change(displayUpcomingSurveyDetails);
    $('#subscription_start_date').change(displayUpcomingSurveyDetails);
});

// to display survey rollout visiblity block.
function displayUpcomingSurveyDetails(){
    var survey_day = $('#survey_roll_out_day').val();
    var roll_out_time = $('#survey_roll_out_time').val();
    if(survey_day != '' && survey_day != undefined){
        $.ajax({
            url: upcomingSurveyDetails,
            type: 'POST',
            dataType: 'json',
            data: {
                survey_roll_out_day: survey_day,
                roll_out_time : roll_out_time,
                company: null,
                survey_frequency : $('#survey_frequency').val(),
                subscription_start_date : $('#subscription_start_date').val(),
            },
        }).done(function(data) {
            if(data['upcomingRollOutDay'] != null && data['upcomingExpiredDay'] != null){
                $('#upcomingSurveyDetails').show();
                $('span#upRollout').html(data['upcomingRollOutDay']);
                $('span#upExpire').html(data['upcomingExpiredDay']);
            }
        }).fail(function(error) {
           // toastr.error(error.responseJSON.data || message.something_went_wrong);
        });
    }
}

/*  function that will reset the wizard form */
function resetJQuerySteps(elementTarget, noOfSteps){
    var noOfSteps = noOfSteps - 1;

    var currentIndex = $(elementTarget).steps("getCurrentIndex");
        if(currentIndex >= 0){
            for(var x = 0; x < currentIndex;x++){
                $(elementTarget).steps("previous");
            }
        }
    $('.companySteps').removeClass('current').hide();    
    setTimeout(function resetHeaderCall(){ 
    var y, steps;
        for(y = 0, steps= 9; y < noOfSteps;y++){
            try{
                $(`${elementTarget} > .steps > ul > li:nth-child(${steps})`).removeClass("done");
                    $(`${elementTarget} > .steps > ul > li:nth-child(${steps})`).removeClass("current");
                    $(`${elementTarget} > .steps > ul > li:nth-child(${steps})`).addClass("disabled");

            }
            catch(err){}
        steps++;
        }
       
    }, 50);
    setTimeout(function (){
        $('.companySteps').removeClass('current').hide();   
        $(".comanytabs").removeClass('completed active');
        $(".companyDetails-content").addClass('active');
        $('#companyAddStep-p-0').show().addClass('current');
        $(".enableDigitalTherapy-content").addClass('d-none');
        $('.actions ul li a[href="#next"]').removeClass("disabled");
        $('.actions ul li a[href="#prev"]').addClass("disabled");
        $('#assigned_roles').select2().trigger('change');
    }, 500);
}