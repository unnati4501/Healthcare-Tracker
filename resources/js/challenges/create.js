$(document).ready(function () {
    $('.select2').select2({
        placeholder: "Select",
        allowClear: true,
        width: '100%'
    });

    var target_type = $('#target_type').val();
    if (target_type != '' && target_type != undefined) {
        $('#target_type').trigger('change');
    }

    var target_type1 = $('#target_type1').val();
    if (target_type1 != '' && target_type1 != undefined) {
        $('#target_type1').trigger('change');
    }

    $('#challenge_category').trigger('change');
    $('#recursive').trigger('change');
    $('#close').trigger('change');
    getTotalDays();
});

$("#start_date,#end_date").keypress(function(event) {
    event.preventDefault();
});

$("#end_date").change(function(event) {
    $("#end_date").trigger("changeDate");
    getTotalDays();
});

$("#start_date").change(function(event) {
    $("#start_date").trigger("changeDate");
    getTotalDays();
});

function getTotalDays()
{
    var totalDays = 0;
    if($("#end_date").val() != '' && $("#start_date").val() != '')
    {
        var d1 = new Date($('#start_date').val());
        var d2 = new Date($('#end_date').val());
        var oneDay = 24*60*60*1000;
        totalDays = Math.round(Math.abs((d2.getTime() - d1.getTime())/(oneDay))) +1;
    } else if($("#start_date").val() != '' && $('#recursive').is(':checked') && $('input[name=recursive_type]:checked').val() != '') {
        totalDays = dayValue[$('input[name=recursive_type]:checked').val()];
    }

    $('#numberOfday').val(totalDays);
}
var start = new Date(new Date().setDate(new Date().getDate() + 1));
var end = new Date(new Date().setDate(start.getDate() + 89));
$('#start_date').datepicker({
    startDate: start,
    endDate: end,
    autoclose: true,
    todayHighlight: true,
    format: 'yyyy-mm-dd',
}).on('changeDate', function () {
    var stdate = new Date();
    if($(this).val() != '')
        stdate = $(this).val();

    $('#end_date').datepicker('setStartDate', new Date(stdate));
    $('#end_date').datepicker('setEndDate', new Date(new Date(stdate).setDate(new Date(stdate).getDate() + 89)) );
    if(new Date($('#end_date').val()) < new Date($('#start_date').val()))
    {
        $('#end_date').val('');
        $('#end_date').datepicker('setDate', null);
    }
    $('#start_date').valid();

});
$('#end_date').datepicker({
    startDate: start,
    endDate: end,
    autoclose: true,
    todayHighlight: true,
    format: 'yyyy-mm-dd',
}).on('changeDate', function () {
    $('#end_date').valid();
});

$('.datepicker').datepicker({
    setDate: new Date(),
    autoclose: true,
    todayHighlight: true,
});

$('input[type="file"]').change(function(e) {
    var fileName = e.target.files[0].name;
    if (fileName.length > 40) {
        fileName = fileName.substr(0, 40);
    }
    var allowedMimeTypes = ['image/png', 'image/jpeg', 'image/jpg'];
    if (!allowedMimeTypes.includes(e.target.files[0].type)) {
        toastr.error(image_valid_error);
        $(e.currentTarget).empty().val('');
        $(this).parent('div').find('.custom-file-label').val('');
    } else if (e.target.files[0].size > 2097152) {
        toastr.error(image_size_2M_error);
        $(e.currentTarget).empty().val('');
        $(this).parent('div').find('.custom-file-label').val('');
    } else {
        $(this).parent('div').find('.custom-file-label').html(fileName);
    }
});

//--------- preview image
function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            $('#previewImg').attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);
    }
};
$("#logo").change(function () {
    readURL(this);
});

$("#target_type").change(function (e){
    if($(this).val() != '')
    {
        var selectedVal = '';
        selectedVal = $("#target_type option:selected").text();

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
            // $('#uom').attr('disabled', false);
        } else {
            $('#excercise_type').attr('disabled', true);
            $("#excercise_type").select2("val", "");
            $('.excercise_type').addClass('d-none');
            $('#uom').attr('disabled', true);
        }
    }
    $('#target_units').val("");
});

$("#challenge_category").change(function (e){
    if($(this).val() == '4')
    {
        $('.rule2').removeClass('d-none');
        $('.challenge-rule-title').removeClass('d-none');
    } else {
        $('#target_units1').val('');
        $("#target_type1").select2("val", "");
        $("#uom1").select2("val", "");
        $('#target_type1').trigger('change');
        $('.rule2').addClass('d-none');
        $('.challenge-rule-title').addClass('d-none');
    }

    if($(this).val() == '2')
    {
        $('#target_units').val(0);
        $('#target_units').attr('disabled',true);
        $('.target_units').addClass('d-none');
    } else {
        $('#target_units').val("");
        $('#target_units').attr('disabled',false);
        $('.target_units').removeClass('d-none');
    }
});

$("#target_type1").change(function (e){

    var selectedVal = '';
    selectedVal = $("#target_type1 option:selected").text();

    $('#uom1').empty();
    $.each(uom[selectedVal], function(key, value) {
        $('#uom1').append('<option value="' + key + '">' + value + '</option>');
    });

    $.each(uom[selectedVal], function(key, value) {
        $('#uom1').select2('val', key);
    });

    if(selectedVal == "Exercises") {
        $('#excercise_type1').attr('disabled', false);
        $('.excercise_type1').removeClass('d-none');
        $("#uom1").select2("val", "");
    } else {
        $('#excercise_type1').attr('disabled', true);
        $("#excercise_type1").select2("val", "");
        $('#uom1').attr('disabled', true);
        $('.excercise_type1').addClass('d-none');
    }
    $('#target_units1').val('');
});

$("#group_member").treeMultiselect({
    enableSelectAll: true,
    searchable: true,
    startCollapsed: true,
    onChange: function(allSelectedItems, addedItems, removedItems) {
        if (currentRoute == 'challenges') {
            var selectedMembers = $('#group_member').val().length;
            if (selectedMembers == 0) {
                $('#challengeAdd').valid();
                $('#group_member-error').show();
                $('#group_member-min-error').hide();
                $('#group_member-max-error').hide();
                $('.tree-multiselect-box').css('border-color', '#f44436');
            } else if (selectedMembers < 2) {
                $('#challengeAdd').valid();
                $('#group_member-error').hide();
                $('#group_member-min-error').show();
                $('#group_member-max-error').hide();
                $('.tree-multiselect-box').css('border-color', '#f44436');
            } else if (!$('#close').is(':checked') && selectedMembers > 100) {
                $('#challengeAdd').valid();
                $('#group_member-error').hide();
                $('#group_member-min-error').hide();
                $('#group_member-max-error').show();
                $('.tree-multiselect-box').css('border-color', '#f44436');
            } else {
                $('#group_member-error').hide();
                $('#group_member-min-error').hide();
                $('#group_member-max-error').hide();
                $('.tree-multiselect-box').css('border-color', '#D8D8D8');
            }
        } else if (currentRoute == 'teamChallenges' || currentRoute == 'companyGoalChallenges') {
            var selectedMembers = $('#group_member').val().length;
            if (selectedMembers == 0) {
                $('#challengeAdd').valid();
                $('#group_member-error').show();
                $('#group_member-min-error').hide();
                $('#group_member-max-error').hide();
                $('.tree-multiselect-box').css('border-color', '#f44436');
            } else if (selectedMembers < 2) {
                $('#challengeAdd').valid();
                $('#group_member-error').hide();
                $('#group_member-min-error').show();
                $('#group_member-max-error').hide();
                $('.tree-multiselect-box').css('border-color', '#f44436');
            } else if (!$('#close').is(':checked') && selectedMembers > 100) {
                $('#challengeAdd').valid();
                $('#group_member-error').show();
                $('#group_member-min-error').hide();
                $('#group_member-max-error').show();
                $('.tree-multiselect-box').css('border-color', '#f44436');
            } else {
                $('#group_member-error').hide();
                $('#group_member-min-error').hide();
                $('#group_member-max-error').hide();
                $('.tree-multiselect-box').css('border-color', '#D8D8D8');
            }
        } else if (currentRoute == 'interCompanyChallenges') {
            var selectedMembers = $('#group_member').val().length,
                companies = {},
                selectedCompaniesCount = 0;
            $.each(allSelectedItems, function(index, element) {
                var key = $(element.node).parent().parent().data('key');
                companies[key] = 1;
            });
            selectedCompaniesCount = Object.keys(companies).length;
            if (selectedMembers == 0) {
                $('#challengeAdd').valid();
                $('#group_member-error').show();
                $('#group_member-min-error').hide();
                $('.tree-multiselect-box').css('border-color', '#f44436');
            } else if (selectedCompaniesCount < 2) {
                $('#challengeAdd').valid();
                $('#group_member-error').hide();
                $('#group_member-min-error').show();
                $('.tree-multiselect-box').css('border-color', '#f44436');
            } else {
                $('#group_member-error').hide();
                $('#group_member-min-error').hide();
                $('.tree-multiselect-box').css('border-color', '#D8D8D8');
            }
        }
    }
});

$("#excercise_type, #excercise_type1").change(function (e){
    if($(this).val() != '')
    {
        if($(this).attr('id') == 'excercise_type'){
            var selectedUom = '#uom';
        } else {
            var selectedUom = '#uom1';
        }
        var selectedVal = exerciseTypes[$(this).val()];
        $(selectedUom).empty();

        if(selectedVal != 'both') {
            $(selectedUom).attr('disabled', true);
            $(selectedUom).append('<option value="' + selectedVal + '">' + selectedVal.charAt(0).toUpperCase() + selectedVal.slice(1) + '</option>');
            $(selectedUom).select2('val', selectedVal);
        } else {
            $(selectedUom).attr('disabled', false);
            $.each(uom['Exercises'], function(key, value) {
                $(selectedUom).append('<option value="' + key + '">' + value + '</option>');
            });
            $(selectedUom).select2();
        }
    }
});

$('#challengeAdd').submit(function(e) {
    console.log(currentRoute);
    $('#unite').val($('#uom').val());
    $('#unite1').val($('#uom1').val());
    //if (currentRoute != 'companyGoalChallenges') {
        var selectedMembers = $('#group_member').val().length;
        if (currentRoute == 'challenges' || currentRoute == 'teamChallenges' || currentRoute == 'companyGoalChallenges') {
            var maxMembers = 100;
        } else if (currentRoute == 'interCompanyChallenges') {
            var maxMembers = 0;
        } else {
            var maxMembers = 20;
        }
        if (selectedMembers == 0) {
            event.preventDefault();
            $('#challengeAdd').valid();
            $('#group_member-min-error').hide();
            $('#group_member-error').show();
            $('.tree-multiselect-box').css('border-color', '#f44436');
        } else if (selectedMembers < 2) {
            event.preventDefault();
            $('#challengeAdd').valid();
            $('#group_member-error').hide();
            $('#group_member-min-error').show();
            $('.tree-multiselect-box').css('border-color', '#f44436');
        } else if (!$('#close').is(':checked') && (maxMembers > 0 && selectedMembers > maxMembers)) {
            event.preventDefault();
            $('#challengeAdd').valid();
            $('#group_member-error').hide();
            $('#group_member-max-error').show();
            $('.tree-multiselect-box').css('border-color', '#f44436');
        } else {
            $('#zevo_submit').prop('disabled', true);
            $('#group_member-error').hide();
            $('#group_member-min-error').hide();
            $('#group_member-max-error').hide();
            $('.tree-multiselect-box').css('border-color', '#D8D8D8');
        }
    //}
    if (currentRoute == 'interCompanyChallenges') {
        var companies = $('#group_member option:selected'),
            checkCompaniesCount = {};
        $(companies).each(function(index, element) {
            var key = $(element).data('cid');
            checkCompaniesCount[key] = 1;
        });
        checkCompaniesCount = Object.keys(checkCompaniesCount).length;
        if (checkCompaniesCount < 2) {
            event.preventDefault();
            $('#challengeAdd').valid();
            $('#group_member-error').hide();
            $('#group_member-min-error').show();
            $('#group_member-max-error').hide();
            $('.tree-multiselect-box').css('border-color', '#f44436');
        }
    }
    if ($('#challenge_category').val() == '4') {
        if ($('#target_type').val() != "" && $('#target_type1').val() != "") {
            if ($('#target_type').val() == $('#target_type1').val() && $('#target_type').val() != "4") {
                event.preventDefault();
                toastr.error("You can not enter same target multiple time.");
            } else if ($('#target_type').val() == $('#target_type1').val() && $('#target_type').val() == "4" && $("#excercise_type").val() != "" && $("#excercise_type1").val() && $("#excercise_type").val() == $("#excercise_type1").val()) {
                event.preventDefault();
                toastr.error("You can not enter same exercise multiple time.");
            }
        }
    }
    if (($('#target_type').val() == "4" && $('#uom').val() == '') || $('#target_type1').val() == "4" && $('#uom1').val() == '') {
        event.preventDefault();
        toastr.error("The Unit of Measurement field is required when Target is Exercises.");
    }
    if ($("#end_date").val() != '' && $("#start_date").val() != '') {
        var totalDays = 0;
        var totalDays1 = 0;
        var d1 = new Date($('#start_date').val());
        var d2 = new Date($('#end_date').val());
        var oneDay = 24 * 60 * 60 * 1000;
        totalDays = Math.round(Math.abs((d2.getTime() - d1.getTime()) / (oneDay))) + 1;
        var st1 = new Date(new Date().setDate(new Date().getDate() + 1));
        var oneDay1 = 24 * 60 * 60 * 1000;
        totalDays1 = Math.round(Math.abs((st1.getTime() - d1.getTime()) / (oneDay))) + 1;
        if (totalDays1 > 90) {
            event.preventDefault();
            toastr.error("You are not allowed to create challenge for the date after 90 days");
        }
        if (totalDays > 90) {
            event.preventDefault();
            toastr.error("Challenge duration can not be greater than 90 days");
        }
    }
    if ($('#challenge_category').val() == '2') {
        $('#tunit').remove();
        var tunit = $('<input type="hidden" name="target_units" id="tunit" value="0" />');
        $('#challengeAdd').append(tunit);
    } else {
        $('#tunit').remove();
    }
});

$('#recursive').change(function (e){
    if($('#recursive').is(':checked')) {
        $('#end_date').attr('disabled', true);
        $('#end_date').val('');
        $('#end_date').datepicker('setDate', null);
        $('#recursive_count').attr('disabled', false);
        $('.recursive_type').attr('disabled', false);
    } else {
        $('.expire_days').addClass('d-none');
        $('#end_date').attr('disabled', false);
        $('#recursive_count').attr('disabled', true);
        $('.recursive_type').attr('disabled', true);
        $(".recursive_type").prop("checked", false);
        $('#recursive_count').val('');
        getTotalDays();
    }
});

$('input[name=recursive_type]').change(function (e){
    getTotalDays();
});

$("#target_units,#target_units1,#recursive_count").focusout(function () {
    $(this).val($.trim($(this).val()).replace(/^0+/, ''));
});

$('#close').change(function() {
    if($('#close').is(':checked')) {
        $('#recursive').attr('disabled', false);
        $('.recursive_type').attr('disabled', false);
        $('#recursive_count').attr('disabled', false);
        $('#recursive_count').val('');
        $(".recursive_type").prop("checked", false);
        $("#recursive").prop("checked", false);
        $("#recursiveSection").removeClass("d-none");
    } else {
        $('#recursive').attr('disabled', true);
        $('.recursive_type').attr('disabled', true);
        $('#recursive_count').attr('disabled', true);
        $('#recursive_count').val('');
        $(".recursive_type").prop("checked", false);
        $("#recursive").prop("checked", false);
        $("#recursiveSection").addClass("d-none");
    }
    $('#recursive').trigger('change');
});