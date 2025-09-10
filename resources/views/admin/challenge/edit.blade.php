@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/tree-multiselect/tree-multiselect.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/datepicker/datepicker3.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.challenge.breadcrumb', [
  'mainTitle' => $pageTitle,
  'breadcrumb' => $route . '.edit'
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        {{ Form::open(['route' => ['admin.'.$route.'.update',$id], 'class' => 'form-horizontal zevo_form_submit', 'method'=>'PATCH','role' => 'form', 'id'=>'challengeEdit','files' => true]) }}
        <div class="card form-card">
            <div class="card-body">
                @include('admin.challenge.form', ['edit'=>true])
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{!! route('admin.'.$route.'.index') !!}">
                        {{ trans('buttons.general.cancel') }}
                    </a>
                    <button class="btn btn-primary" id="zevo_submit_btn" onclick="formSubmit()" type="submit">
                        {{ trans('buttons.general.update') }}
                    </button>
                </div>
            </div>
        </div>
        {{ Form::close() }}
    </div>
    <!-- /.container-fluid -->
</section>
@endsection

@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\EditChallengeRequest','#challengeEdit') !!}
<script src="{{ asset('assets/plugins/tree-multiselect/tree-multiselect.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/datepicker/bootstrap-datepicker.js?var='.rand()) }}">
</script>
<script id="ongoingBadgeTemplate" type="text/html">
    @include('admin.challenge.ongoing_badges', [
    'count'     => '0',
    'target'    => null,
    'inDays'    => null,
    'badge'     => null,
    'show_del'  => 'show_del',
])
</script>
<script type="text/javascript">
    var dayValue = <?php echo json_encode($dayValue); ?>;
    var uom = <?php echo json_encode($uom_data); ?>;
    var image_valid_error = '{{ trans('challenges.messages.image_valid_error') }}';
    var image_size_2M_error = '{{ trans('challenges.messages.image_size_2M_error') }}';
    var currentRoute = '{{ $route }}';
    var challengeData = '{{ $challengeData }}';
    var startdate = '{{ $challengeData ?? date('Y-m-d',strtotime($challengeData->start_date1)) }}';
    var currentDate = '{{ date('Y-m-d') }}';
    var uom1 = '{{ $challengeData ?? $challengeData->challengeRules[0]->uom }}';
    var uom2 = '{{ $challengeData ?? $challengeData->challengeRules[1]->uom }}';
    var upload_image_dimension = '{{ trans('challenges.messages.upload_image_dimension') }}';
    var ongoingFlag = '{{ (!empty($challengeData) && $challengeData->is_badge) ? true : false }}';
    var badgeCount = {{ (sizeof($challengeOngoingBadges) + 1) ?? 1 }};
    var stepsCompleted = `{{ trans('challenges.form.labels.steps_completed') }}`;
    var distanceCompleted = `{{ trans('challenges.form.labels.distance_completed') }}`;
    var getDepartment = '{{ route("admin.challenges.getDepartments") }}';
    var getMemberData = '{{ route("admin.challenges.getMemberData") }}';
</script>
<script type="text/javascript">
    function getTotalDays()
{
    var totalDays = 0;
    var dayValue = JSON.parse('<?php echo json_encode($dayValue); ?>');
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

function readURL(input, previewElement)
{
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
                    toastr.error(upload_image_dimension);
                    readURL(null, previewElement);
                }
            }
            $(previewElement).attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        $(previewElement).removeAttr('src');
    }
}
function isNumber(evt){
    var charCode = (evt.which) ? evt.which : event.keyCode
    if (charCode > 31 && (charCode < 48 || charCode > 57))
        return false;

    return true;
}
function formSubmit()
{
    $('#unite').val($('#uom').val());
    $('#unite1').val($('#uom1').val());

    var selectedMembers = $('#group_member').val().length;
    @if($route == 'challenges'){
        var maxMembers = 250;
    }
    @elseif($route == 'teamChallenges' || $route == 'companyGoalChallenges' ){
        var maxMembers = 100;
    }
    @elseif ($route == 'interCompanyChallenges'){
        var maxMembers = 0;
    }
    @else {
        var maxMembers = 20;
    }
    @endif
    if (selectedMembers == 0) {
        event.preventDefault();
        $('#challengeEdit').valid();
        $('#group_member-min-error').hide();
        $('#group_member-error').show();
        $('.tree-multiselect').css('border-color', '#f44436');
    } else if(selectedMembers < 2) {
        event.preventDefault();
        $('#challengeEdit').valid();
        $('#group_member-error').hide();
        $('#group_member-min-error').show();
        $('.tree-multiselect').css('border-color', '#f44436');
    } else if(!$('#close').is(':checked') && (maxMembers > 0 && selectedMembers > maxMembers)) {
        event.preventDefault();
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

    @if($route == 'interCompanyChallenges')
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
    @endif

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

    $('.target_required, .indays_required, .badges_required').removeClass('element-invalid');
    var numberOfDays = parseInt($('#numberOfday').val());
    $('#ongoing_badges-error,#ongoing_badges_min-error').hide();
    var minErrorFlag = false;
    var target = $('.target_required').map(function(idx, elem) {
        if($(elem).val().length <= 0){
            $(elem).addClass('element-invalid');
            return $(elem).val();
        }
    }).get();

    var indays = $('.indays_required').map(function(idx, elem) {
        if($(elem).val() > numberOfDays) {
            $(elem).addClass('element-invalid');
            minErrorFlag = true;
        }
        if($(elem).val().length <= 0){
            $(elem).addClass('element-invalid');
            return $(elem).val();
        }
    }).get();

    var badges = $('.badges_required').map(function(idx, elem) {
        if($(elem).val().length <= 0){
            $(elem).addClass('element-invalid');
            return $(elem).val();
        }
    }).get();

    var _challengeValue = $('#challenge_category').val();
    var _targetType = $('#target_type').val();
    if(_challengeValue != 4 && (_targetType == 1 || _targetType == 2) && $('#ongoing_challenge_badge').prop("checked")){
        if(target.length > 0 || indays.length > 0 || badges.length > 0){
            event.preventDefault();
            $('#challengeEdit').valid();
            $('#ongoing_badges-error').show();
        }
        if(minErrorFlag == true){
            event.preventDefault();
            $('#challengeEdit').valid();
            $('#ongoing_badges_min-error').show();
        }
    }
}

$(document).ready(function () {

    $('.select2').select2({
        placeholder: "Select",
        allowClear: true,
        width: '100%'
    });

    $('select.select2').select2();

    $('#challenge_category').trigger('change');
    $('#close').trigger('change');

    var target_type = $('#target_type').val();
    if (target_type != '' && target_type != undefined) {
            $('#target_type').trigger('change');
    }

    var target_type1 = $('#target_type1').val();
    if (target_type1 != '' && target_type1 != undefined) {

            $('#target_type1').trigger('change');

    }
    @if(empty($challengeData->parent_id))

            $('#recursive').trigger('change');

    @endif

    getTotalDays();

    var _challengeValue = $('#challenge_category').val();
    var _targetType = $('#target_type').val();
    if(_challengeValue != 4 && (_targetType == 1 || _targetType == 2)){
        $('#ongoing_badges_flag').removeClass('d-none');
    }

    if(ongoingFlag) {
        $('#ongoing_badges_div, #ongoing_badges_flag').removeClass('d-none');
    }

    $(document).on('change', '#ongoing_challenge_badge', function(){
        if(this.checked) {
            $('#ongoing_badges_div').removeClass('d-none');
        } else {
            $('#ongoing_badges_div').addClass('d-none');
        }
    });

    $(document).on('click', '#ongoingBadgeAdd', function () {
        var template = $('#ongoingBadgeTemplate').text().trim();
        var targetType = $('#target_type').val();
        template = template.replace(':badgeCount', badgeCount);
        if(targetType == 1) {
            template = template.replace(distanceCompleted, stepsCompleted);
        } else {
            template = template.replace(stepsCompleted, distanceCompleted);
        }
        badgeCount++;
        $("#ongoingBadgeTbl tbody").append(template);
        $(".ongoing-badge").select2();
    });

    $(document).on('keyup', '#ongoingBadgeTbl tbody tr:last input', function(e) {
        if($('#ongoingBadgeTbl tbody tr').length > 1) {
            $(this).parent().parent().next().toggleClass("show_del", $(this).val().length == 0);
        }
    });

    $(document).on('input','.on_type_required', function(){
        $('.target_required, .indays_required, .badges_required').removeClass('element-invalid');
        var numberOfDays = parseInt($('#numberOfday').val());
        $('#ongoing_badges-error,#ongoing_badges_min-error').hide();
        var minErrorFlag = false;
        var target = $('.target_required').map(function(idx, elem) {
            if($(elem).val().length <= 0){
                $(elem).addClass('element-invalid');
                return $(elem).val();
            }
        }).get();

        var indays = $('.indays_required').map(function(idx, elem) {
            if($(elem).val() > numberOfDays) {
                $(elem).addClass('element-invalid');
                minErrorFlag = true;
            }
            if($(elem).val().length <= 0){
                $(elem).addClass('element-invalid');
                return $(elem).val();
            }
        }).get();

        var badges = $('.badges_required').map(function(idx, elem) {
            if($(elem).val().length <= 0){
                $(elem).addClass('element-invalid');
                return $(elem).val();
            }
        }).get();

        if(target.length > 0 || indays.length > 0 || badges.length > 0){
            $('#ongoing_badges-error').show();
        }
        if(minErrorFlag == true){
            $('#ongoing_badges_min-error').show();
        }
    });
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
            toastr.error("{{trans('labels.common_title.image_valid_error')}}");
            $(e.currentTarget).empty().val('');
            $(this).parent('div').find('.custom-file-label').val('');
        } else if (e.target.files[0].size > 2097152) {
            toastr.error("{{trans('labels.common_title.image_size_2M_error')}}");
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

    $(document).on('click', ".ongoing-badge-remove", function (e) {
        e.preventDefault();
        $($(this).closest("tr")).remove();
        if($('#ongoingBadgeTbl tbody tr').length == 1) {
            $('#ongoingBadgeTbl tbody tr:last td:last').removeClass('show_del');
        }

        $('.target_required, .indays_required, .badges_required').removeClass('element-invalid');
        var numberOfDays = parseInt($('#numberOfday').val());
        $('#ongoing_badges-error,#ongoing_badges_min-error').hide();
        var minErrorFlag = false;
        var target = $('.target_required').map(function(idx, elem) {
            if($(elem).val().length <= 0){
                $(elem).addClass('element-invalid');
                return $(elem).val();
            }
        }).get();

        var indays = $('.indays_required').map(function(idx, elem) {
            if($(elem).val() > numberOfDays) {
                $(elem).addClass('element-invalid');
                minErrorFlag = true;
            }
            if($(elem).val().length <= 0){
                $(elem).addClass('element-invalid');
                return $(elem).val();
            }
        }).get();

        var badges = $('.badges_required').map(function(idx, elem) {
            if($(elem).val().length <= 0){
                $(elem).addClass('element-invalid');
                return $(elem).val();
            }
        }).get();

        if(target.length > 0 || indays.length > 0 || badges.length > 0){
            $('#ongoing_badges-error').show();
        }
        if(minErrorFlag == true){
            $('#ongoing_badges_min-error').show();
        }
    });

    $("#group_member").treeMultiselect({
        @if(!empty($challengeData->start_date1) && date('Y-m-d',strtotime($challengeData->start_date1)) <= date('Y-m-d') && $route == 'challenges')
        @else
            enableSelectAll: true,
        @endif
            searchable: true,
            startCollapsed: true,
        @if(!empty($challengeData->start_date1) && date('Y-m-d',strtotime($challengeData->start_date1)) <= date('Y-m-d') && $route == 'challenges')
            allowBatchSelection:false,
        @endif
        @if($route == 'challenges')
        onChange: function (allSelectedItems, addedItems, removedItems) {
            var selectedMembers = $('#group_member').val().length;
            if (selectedMembers == 0) {
                $('#challengeEdit').valid();
                $('#group_member-min-error').hide();
                $('#group_member-max-error').hide();
                $('#group_member-error').show();
                $('.tree-multiselect').css('border-color', '#f44436');
            } else if(selectedMembers < 2) {
                $('#challengeEdit').valid();
                $('#group_member-error').hide();
                $('#group_member-min-error').show();
                $('.tree-multiselect').css('border-color', '#f44436');
            } else if(!$('#close').is(':checked') && selectedMembers > 250) {
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
        }
        @elseif($route == 'teamChallenges')
        onChange: function (allSelectedItems, addedItems, removedItems) {
            var selectedMembers = $('#group_member').val().length;
            if (selectedMembers == 0) {
                $('#challengeEdit').valid();
                $('#group_member-min-error').hide();
                $('#group_member-max-error').hide();
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
        }
        @elseif($route == 'interCompanyChallenges')
        onChange: function (allSelectedItems, addedItems, removedItems) {
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
        @endif
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

    $('#close').change(function() {
        if($('#close').is(':checked')) {
            $("#recursiveSection").removeClass("d-none");
        } else {
            $("#recursiveSection").addClass("d-none");
        }
        $('#recursive').trigger('change');
    });

    $(document).on('change', '#target_type', function(e) {
        if($(this).val() != '')
        {
            var selectedVal = '';
            selectedVal = $("#target_type option:selected").text();

            var uom = JSON.parse('<?php echo json_encode($uom_data); ?>');
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
                @if(!empty($challengeData->challengeRules[0]->uom))
                $('#uom').select2('val', '{{ $challengeData->challengeRules[0]->uom }}');
                @endif
                $('.excercise_type').removeClass('d-none');
            } else {
                $('#excercise_type').attr('disabled', true);
                $("#excercise_type").select2("val", "");
                $('#uom').attr('disabled', true);
                $('.excercise_type').addClass('d-none');
            }

            if(selectedVal == "Content") {
                $('#content_type').attr('disabled', true);
                $('#uom').attr('disabled', true);
                $('.content_type').removeClass('d-none');
                @if(!empty($challengeData->challengeRules[0]->uom))
                $('#uom').select2('val', '{{ $challengeData->challengeRules[0]->uom }}');
                @endif
            } else {
                $('#content_type').attr('disabled', true);
                $("#content_type").select2("val", "");
                $('.content_type').addClass('d-none');
                $('#uom').attr('disabled', true);
            }

        }
    });

    $("#challenge_category").change(function (e){
        if($(this).val() == '4') {
            $('.rule2').removeClass('d-none');
            $('.challenge-rule-title').removeClass('d-none');
        } else if($(this).val() == '5') {
            $('.rule2').removeClass('d-none');
            $('.challenge-rule-title').removeClass('d-none');
            $('.targetUnits').addClass('d-none');
            $('.target_units1').addClass('d-none');
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

    $(document).on('change', "#target_type1", function(e) {
        var selectedVal = '';
        selectedVal = $("#target_type1 option:selected").text();

        var uom = JSON.parse('<?php echo json_encode($uom_data); ?>');
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
            @if(!empty($challengeData->challengeRules[1]->uom))
            $('#uom1').select2('val', '{{ $challengeData->challengeRules[1]->uom }}');
            @endif
            $('.excercise_type1').removeClass('d-none');
        } else {
            $('#excercise_type1').attr('disabled', true);
            $("#excercise_type1").select2("val", "");
            $('#uom1').attr('disabled', true);
            $('.excercise_type1').addClass('d-none');
        }

        if(selectedVal == "Content") {
            $('#content_type1').attr('disabled', true);
            $('#uom1').attr('disabled', true);
            @if(!empty($challengeData->challengeRules[1]->uom))
            $('#uom1').select2('val', '{{ $challengeData->challengeRules[1]->uom }}');
            @endif
            $('.content_type1').removeClass('d-none');
        } else {
            $('#content_type1').attr('disabled', true);
            $("#content_type1").select2("val", "");
            $('#uom1').attr('disabled', true);
            $('.content_type1').addClass('d-none');
        }
    });
    $(document).on('change', '#locations', function() {
        var _value = $(this).val();
        var _token = $('input[name="_token"]').val();
        if(_value.length <= 0) {
            return true;
        }
        $.ajax({
            url: getDepartment,
            method: 'post',
            data: {
                _token: _token,
                value: _value
            },
            success: function(result) {
                $('#department').empty();
                $.each(result, function(key, value) {
                    $('#department').append('<option value="' + key + '">' + value + '</option>');
                });
                $('#department').select2();
            }
        });
    });
    $(document).on('change', '#department', function() {
        var _value = $(this).val();
        var _token = $('input[name="_token"]').val();
        if(_value.length <= 0) {
            return true;
        }
        $("#group_member").empty();
        $("#group_member").treeMultiselect();
        $.ajax({
            url: getMemberData,
            method: 'post',
            data: {
                _token: _token,
                value: _value
            },
            success: function(result) {
                $('#group_member').parent().find('.tree-multiselect').remove();
                $.each(result, function(key, value) {
                    var departmentName = value['name'];
                    var teams = value['teams'];
                    $.each(teams, function(tKey, tValue) {
                        var teamName = tValue['name'];
                        var members = tValue['members'];
                        $.each(members, function(mKey, mValue) {
                            $('#group_member').append('<option value="' + mValue.id + '" data-section="' +departmentName+'/'+teamName+'">' + mValue.name + '</option>');
                        });
                    });
                });
                $("#group_member").treeMultiselect({
                    enableSelectAll: true,
                    searchable: true,
                    startCollapsed: true,
                });
            }
        });
    });
</script>
@endsection
