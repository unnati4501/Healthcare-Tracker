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

function readURL(input)
{
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            $('#previewImg').attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);
    }
}

$('#challengeEdit').submit(function(e) {
    $('#unite').val($('#uom').val());
    $('#unite1').val($('#uom1').val());

    // if (currentRoute != 'companyGoalChallenges') {
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
            $('#challengeEdit').valid();
            $('#group_member-min-error').hide();
            $('#group_member-error').show();
            $('.tree-multiselect-box').css('border-color', '#f44436');
        } else if (selectedMembers < 2) {
            event.preventDefault();
            $('#challengeEdit').valid();
            $('#group_member-error').hide();
            $('#group_member-min-error').show();
            $('.tree-multiselect-box').css('border-color', '#f44436');
        } else if (!$('#close').is(':checked') && (maxMembers > 0 && selectedMembers > maxMembers)) {
            event.preventDefault();
            $('#challengeEdit').valid();
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
    // }

    if (currentRoute != 'interCompanyChallenges') {
        var companies = $('#group_member option:selected'),
        checkCompaniesCount = {};
        $(companies).each(function(index, element) {
            var key = $(element).data('cid');
            checkCompaniesCount[key] = 1;
        });
        checkCompaniesCount = Object.keys(checkCompaniesCount).length;
        if(checkCompaniesCount < 2) {
            event.preventDefault();
            $('#challengeEdit').valid();
            $('#group_member-error').hide();
            $('#group_member-min-error').show();
            $('#group_member-max-error').hide();
            $('.tree-multiselect').css('border-color', '#f44436');
        }
    }

    if($("#end_date").val() != '' && $("#start_date").val() != '')
    {
        var totalDays = 0;
        var d1 = new Date($('#start_date').val());
        var d2 = new Date($('#end_date').val());
        var oneDay = 24*60*60*1000;
        totalDays = Math.round(Math.abs((d2.getTime() - d1.getTime())/(oneDay))) +1;

        var st1 = new Date(new Date().setDate(new Date().getDate() + 1));
        var oneDay1 = 24*60*60*1000;
        totalDays1 = Math.round(Math.abs((st1.getTime() - d1.getTime())/(oneDay))) +1;

        if(totalDays1 > 90)
        {
            event.preventDefault();
            toastr.error("You are not allowed to create challenge for the date after 90 days");
        }

        if(totalDays > 90)
        {
            event.preventDefault();
            toastr.error("Challenge duration can not be greater than 90 days");
        }
    }
});

$(document).ready(function () {
    $('.select2').select2({
        placeholder: "Select",
        allowClear: true,
        width: '100%'
    });

    $('select.select2').select2();

    $('#challenge_category').trigger('change');

    var target_type = $('#target_type').val();
    if (target_type != '' && target_type != undefined) {
        $('#target_type').trigger('change');
    }

    var target_type1 = $('#target_type1').val();
    if (target_type1 != '' && target_type1 != undefined) {
        $('#target_type1').trigger('change');
    }
    if (!challengeData.parent_id) {
        $('#recursive').trigger('change');
    }

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

var start1 = new Date(new Date().setDate(new Date().getDate() + 1));
var end1 = new Date(new Date().setDate(start1.getDate() + 89));
// set end date to max one year peri<p></p>od:
if($('#start_date').val() != "") {
    if(new Date() > new Date($('#start_date').val())) {
        var start = new Date();
    } else {
        var start = new Date($('#start_date').val());
    }
    var end = new Date(new Date($('#start_date').val()).setDate(new Date($('#start_date').val()).getDate() + 89));
} else {
    var start = new Date(new Date().setDate(new Date().getDate() + 1));
    var end = new Date(new Date().setDate(start.getDate() + 89));
}

$('#start_date').datepicker({
    startDate: start1,
    endDate: end1,
    autoclose: true,
    todayHighlight: true,
    format: 'yyyy-mm-dd',
}).on('changeDate', function () {
    var stdate = new Date();
    if($(this).val() != '')
        stdate = $(this).val();

    if(new Date() > new Date($(this).val())) {
        $('#end_date').datepicker('setStartDate', new Date());
    } else {
        $('#end_date').datepicker('setStartDate', new Date(stdate));
    }

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
    // $('#start_date').datepicker('setEndDate', new Date($(this).val()));
    $('#end_date').valid();
});

$('.datepicker').datepicker({
    setDate: new Date(),
    autoclose: true,
    todayHighlight: true,
});

$('input[type="file"]').change(function (e) {
    var fileName = e.target.files[0].name;
    if (fileName.length > 40) {
        fileName = fileName.substr(0, 40);
    }
    var allowedMimeTypes = ['image/png', 'image/jpeg', 'image/jpg'];
    if (!allowedMimeTypes.includes(e.target.files[0].type)) {
        toastr.error(image_valid_error);
        $(this).parent('div').find('.custom-file-label').val('');
    } else if (e.target.files[0].size > 2097152) {
        toastr.error(image_size_2M_error);
        $(e.currentTarget).empty().val('');
        $(this).parent('div').find('.custom-file-label').val('');
    } else {
        $(this).parent('div').find('.custom-file-label').html(fileName);
    }
});

$("#logo").change(function () {
    readURL(this);
});

$("#group_member").treeMultiselect({
    searchable: true,
    startCollapsed: true,
    enableSelectAll: !(challengeData.start_date1 && startdate <= currentDate && currentRoute == 'challenges') ?? true,
    allowBatchSelection: (challengeData.start_date1 && startdate <= currentDate && currentRoute == 'challenges') ?? false,
    onChange: function (allSelectedItems, addedItems, removedItems) {
        if (currentRoute == 'challenges') {
            var selectedMembers = $('#group_member').val().length;
            if (selectedMembers == 0) {
                $('#challengeEdit').valid();
                $('#group_member-min-error').hide();
                $('#group_member-error').show();
                $('.tree-multiselect').css('border-color', '#f44436');
            } else if(selectedMembers < 2) {
                $('#challengeEdit').valid();
                $('#group_member-error').hide();
                $('#group_member-min-error').show();
                $('.tree-multiselect').css('border-color', '#f44436');
            } else if(!$('#close').is(':checked') && selectedMembers > 100) {
                $('#challengeEdit').valid();
                $('#group_member-error').hide();
                $('#group_member-max-error').show();
                $('.tree-multiselect').css('border-color', '#f44436');
            } else {
                $('#group_member-error').hide();
                $('#group_member-min-error').hide();
                $('#group_member-max-error').hide();
                $('.tree-multiselect').css('border-color', '#D8D8D8');
            }
        } else if (currentRoute == 'teamChallenges' || currentRoute == 'companyGoalChallenges') {
            var selectedMembers = $('#group_member').val().length;
            if (selectedMembers == 0) {
                $('#challengeEdit').valid();
                $('#group_member-min-error').hide();
                $('#group_member-error').show();
                $('.tree-multiselect').css('border-color', '#f44436');
            } else if(selectedMembers < 2) {
                $('#challengeEdit').valid();
                $('#group_member-error').hide();
                $('#group_member-min-error').show();
                $('.tree-multiselect').css('border-color', '#f44436');
            } else if(!$('#close').is(':checked') && selectedMembers > 100) {
                $('#challengeEdit').valid();
                $('#group_member-error').hide();
                $('#group_member-max-error').show();
                $('.tree-multiselect').css('border-color', '#f44436');
            } else {
                $('#group_member-error').hide();
                $('#group_member-min-error').hide();
                $('#group_member-max-error').hide();
                $('.tree-multiselect').css('border-color', '#D8D8D8');
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
                $('#challengeEdit').valid();
                $('#group_member-min-error').hide();
                $('#group_member-error').show();
                $('.tree-multiselect').css('border-color', '#f44436');
            } else if(selectedCompaniesCount < 2) {
                $('#challengeEdit').valid();
                $('#group_member-error').hide();
                $('#group_member-min-error').show();
                $('#group_member-max-error').hide();
                $('.tree-multiselect').css('border-color', '#f44436');
            } else {
                $('#group_member-error').hide();
                $('#group_member-min-error').hide();
                $('#group_member-max-error').hide();
                $('.tree-multiselect').css('border-color', '#D8D8D8');
            }
        }
    }
});

$('input[name=recursive_type]').change(function (e){
    getTotalDays();
});

$("#target_units,#target_units1,#recursive_count").focusout(function () {
    $(this).val($.trim($(this).val()).replace(/^0+/, ''));
});

$('#recursive').change(function (e){
    if($('#recursive').is(':checked')) {
        $('#end_date').attr('disabled', true);
        $('#end_date').val('');
        $('#end_date').datepicker('setDate', null);
        // $('#recursive_count').attr('disabled', false);
        // $('.recursive_type').attr('disabled', false);
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

$("#challenge_category").change(function (e){
    if($(this).val() == '4')
    {
        $('.rule2').removeClass('d-none');
        $('.challenge-rule-title').removeClass('d-none');
    } else {
        $('#target_units1').val('');
        $("#target_type1").val("");
        $('#target_type1').trigger('change');
        $('.rule2').addClass('d-none');
        $('.challenge-rule-title').addClass('d-none');
    }

    if($(this).val() == '2')
    {
        $('.target_units').addClass('d-none');
    }
});

$(document).on('change', '#target_type', function(e) {
    if($(this).val() != ''){
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
            $('#excercise_type').attr('disabled', true);
            $('#uom').attr('disabled', true);
            if(uom1){
                $('#uom').select2('val', uom1);
            }
            $('.excercise_type').removeClass('d-none');
        } else {
            $('#excercise_type').attr('disabled', true);
            $("#excercise_type").select2("val", "");
            $('#uom').attr('disabled', true);
            $('.excercise_type').addClass('d-none');
        }

    }
});

$(document).on('change', "#target_type1", function(e) {
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
        $('#excercise_type1').attr('disabled', true);
        $('#uom1').attr('disabled', true);
        if(uom2){
            $('#uom1').select2('val', uom2);
        }
        $('.excercise_type1').removeClass('d-none');
    } else {
        $('#excercise_type1').attr('disabled', true);
        $("#excercise_type1").select2("val", "");
        $('#uom1').attr('disabled', true);
        $('.excercise_type1').addClass('d-none');
    }
});