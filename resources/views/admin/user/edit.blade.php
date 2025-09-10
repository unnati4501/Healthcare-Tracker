@extends('layouts.app')
@section('after-styles')
<link href="{{asset('assets/plugins/datepicker/datepicker3.css?var='.rand())}}" rel="stylesheet"/>
<link href="{{asset('assets/plugins/datepicker/datepicker3.css?var='.rand())}}" rel="stylesheet"/>
<link href="{{asset('assets/plugins/jonthornton-timepicker/jquery.timepicker.min.css?var='.rand())}}" rel="stylesheet"/>
<link href="{{asset('assets/plugins/tree-multiselect/tree-multiselect.css?var='.rand())}}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.user.breadcrumb', [
    'mainTitle' => trans('labels.user.edit_form_title'),
    'breadcrumb' => Breadcrumbs::render('user.edit'),
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card form-card">
            {{ Form::open(['route' => ['admin.users.update', $record->id], 'class' => 'form-horizontal zevo_form_submit', 'method' => 'PATCH', 'role' => 'form', 'id' => 'userEdit', 'files' => true]) }}
            <div class="card-body">
                @include('admin.user.form', ['edit' => true])
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{{ route('admin.users.index') }}">
                        {{ trans('labels.buttons.cancel') }}
                    </a>
                    <button class="btn btn-primary" onclick="formSubmit()" type="submit">
                        {{ trans('labels.buttons.update') }}
                    </button>
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</section>
<!-- Delete Model Popup -->
@include('admin.user.delete-model')
@endsection
@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\EditUserRequest','#userEdit') !!}
<script src="{{ asset('assets/plugins/datepicker/bootstrap-datepicker.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/jonthornton-timepicker/jquery.timepicker.min.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/moment/moment.min.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/moment/moment-timezone-with-data-10-year-range.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/jonthornton-timepicker/jquery.datepair.min.js?var='.rand()) }}">
</script>
<script src="{{ asset('js/external/jquery.form.min.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/tree-multiselect/tree-multiselect.js?var='.rand()) }}">
</script>
<script type="text/javascript">
    var deptUrl = '{{ route("admin.ajax.companyDepartment", ":id") }}',
        teamUrl = '{{ route("admin.ajax.get-teams", [":id", $currTeam]) }}',
        roleUrl = '{{ route("admin.ajax.roles", ":id") }}',
        getRoleDataUrl = '{{ route("admin.users.getRoleWiseCompanies") }}',
        deleteCustomLeave =  `{{ route('admin.users.delete-custom-leave', ':id') }}`,
        defaultCourseImg = `{{ asset('assets/dist/img/placeholder-img.png') }}`;
        today = new Date(),
        group = '{{ $group }}',
        endDate = new Date(new Date().setYear(today.getFullYear() + 100)),
        message = {
            upload_image_dimension: `{{ trans('user.messages.upload_image_dimension') }}`,
            choose_file: `{{ trans('user.form.placeholder.choose_file') }}`,
            deleted_custom_leave : `{{trans('user.messages.delete_custom_leave')}}`,
            unable_to_delete_leave : `{{trans('user.messages.unable_to_delete_leave')}}`,
        };

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
                        $(input).parent('div').find('.invalid-feedback').hide();
                        $(previewElement).removeAttr('src');
                        $(input).parent('div').find('.custom-file-label').html(message.choose_file);
                        toastr.error(message.upload_image_dimension);
                        readURL(null, previewElement);
                    }
                }
                $(previewElement).attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            if(previewElement == '#previewImg') {
                $(previewElement).attr('src', defaultCourseImg);
            } else {
                $(previewElement).removeAttr('src');
            }
        }
    };
    $('document').ready(function() {

        if (group == 'wellbeing_specialist') {
            $("div").remove("#data-coach-details-wrapper");
        } else if (group == 'health_coach') {
            $("div").remove("#data-ws-details-wrapper");
        }
        
        $('#userEdit').validate().settings.ignore = true;
        // set end date to max one year period:
        $('#date_of_birth').datepicker({
            startDate: new Date(new Date().setYear(today.getFullYear() - 100)),
            endDate: today,
            autoclose: true,
            todayHighlight: true,
            format: 'yyyy-mm-dd',
        });

        $('#start_date').datepicker({
            startDate: today,
            autoclose: true,
            todayHighlight: true,
            format: 'yyyy-mm-dd',
        });

        $("#date_of_birth, #start_date, .custom-leave-from-date, .custom-leave-to-date").keypress(function(event) {
            event.preventDefault();
        });

        $("#profileImage, #counsellor_cover").change(function (e) {
            const allowedMimeTypes = ['image/png', 'image/jpeg', 'image/jpg'],
                previewElement = $(this).data('previewelement');
            if(e.target.files.length > 0) {
                const fileName = e.target.files[0].name;
                if (!allowedMimeTypes.includes(e.target.files[0].type)) {
                    toastr.error("{{ trans('labels.common_title.image_valid_error') }}");
                    $(e.currentTarget).empty().val('');
                    $(this).parent('div').find('.custom-file-label').html(message.choose_file);
                } else if (e.target.files[0].size > 2097152) {
                    toastr.error("{{ trans('labels.common_title.image_size_2M_error') }}");
                    $(e.currentTarget).empty().val('');
                    $(this).parent('div').find('.custom-file-label').html(message.choose_file);
                } else {
                    $(this).parent('div').find('.custom-file-label').html(fileName);
                    readURL(this, previewElement);
                }
            }
        });

        // to keep county and team field enabled when editing locations.
        var company = $('#company').val();
        if (company != '' && company != undefined) {
            // $('#company').trigger('change');
        }

        // to populate county and team field after redirecting with error.
        setTimeout(function(){
            var department = "{{old('department')}}";
            if (department != '' && department != undefined) {
                $('#department').select2('val', department);
            }
            var team = "{{old('team')}}";
            if (team != '' && team != undefined) {
                $('#team').select2('val', team);
            }
        }, 1000);

        $("#height").focusout(function () {
            $(this).val($.trim($(this).val()).replace(/^0+/, ''));
        });

        $(document).on('change', '#department', function(e) {
            var value = ($(this).val() || "");
            if(value != "") {
                $('#team').attr('disabled', true);
                $.ajax({
                    url: teamUrl.replace(':id', value),
                    method: 'get',
                    data: {
                        _token: $('input[name="_token"]').val()
                    }
                }).done(function(data) {
                    $('#team')
                        .empty()
                        .attr('disabled', false)
                        .val('')
                        .trigger('change')
                        .append('<option value="">Select</option>')
                        .removeClass('is-valid');

                    if (data.result) {
                        $.each(data.result, function(key, value) {
                            $('#team').append(`<option value="${value.id}">${value.name}</option>`);
                        });
                    }
                });
            }
        });

        $(document).on('change', '#availability', function(e) {
            var value = $(this).val();
            if(value == '2') {
                $('[data-availability-dates-wrapper]').show();
            } else {
                $('[data-availability-dates-wrapper]').hide();
                $('.custom-leave-from-date, .custom-leave-to-date').val('').removeClass('is-valid is-invalid');
            }
        });

        $(document).on('change', '#responsibilities', function(e) {
            var value = $(this).val();
            if(value == '2' || value == '3') {
                $('.expertise_wbs, .advance_notice_wbs').show();
            } else {
                $('.expertise_wbs, .advance_notice_wbs').hide();
            }
        });

        $('#userEdit').ajaxForm({
            beforeSend: function() {
                $('.page-loader-wrapper').show();
            },
            success: function(data) {
                if(data.status && data.status == true) {
                    window.location.replace("{{ route('admin.users.index') }}");
                }
            },
            error: function(data) {
                toastr.error(data?.responseJSON?.message || `{{ trans('labels.common_title.something_wrong_try_again') }}`);
            },
            complete: function(xhr) {
                $('.page-loader-wrapper').hide();
            }
        });

        var _type = $('#user_type').val();
        if(_type == 'counsellor') {
            $('.counsellor_skills_section').show();
        } else if(_type == 'wellbeing_specialist') {
            $('.cover_picture').show();
        }

        // Delete custome leave.

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

        // $('body').on('click', '.delete-custom-leave', function() {
        //     var deleteConfirmModalBox = '#delete-model-box';
        //     $(deleteConfirmModalBox).attr("data-id", $(this).data('id'));
        //     $(deleteConfirmModalBox).modal('show');
        // });
        $('body').on('focus',".custom-leave-from-date", function(){
            var previewelement = $(this).attr('data-previewelement');
            $(this).datepicker({
                startDate: today,
                endDate: endDate,
                autoclose: true,
                todayHighlight: true,
                format: 'yyyy-mm-dd',
            }).on('changeDate', function () {
                $('#to_date_'+previewelement).datepicker('setStartDate', new Date($(this).val()));
                $('#'+$(this).attr('id')).valid();
            });
        });
        $('body').on('focus',".custom-leave-to-date", function(){
            var previewelement = $(this).attr('data-previewelement');
            $(this).datepicker({
                startDate: today,
                endDate: endDate,
                autoclose: true,
                todayHighlight: true,
                format: 'yyyy-mm-dd',
            }).on('changeDate', function () {
                $('#from_date_'+previewelement).datepicker('setEndDate', new Date($(this).val()));
                $('#'+$(this).attr('id')).valid();
            });
        });

        $(document).on('click', '#delete-model-box-confirm', function (e) {
            $('.page-loader-wrapper').show();
            var deleteConfirmModalBox = '#delete-model-box';
            var objectId = $(deleteConfirmModalBox).attr("data-id");
            $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
            });

            $.ajax({
                type: 'DELETE',
                url: deleteCustomLeave.replace(':id', objectId),
                data: null,
                crossDomain: true,
                cache: false,
                contentType: 'json',
                success: function (data) {
                    var deleteConfirmModalBox = '#delete-model-box';
                    $(deleteConfirmModalBox).modal('hide');
                    $('.page-loader-wrapper').hide();
                    if (data['deleted'] == 'true') {
                        toastr.success(message.deleted_custom_leave);
                        setTimeout(function () {
                            window.location.reload()
                        }, 1000)
                    } else {
                        toastr.error(message.unable_to_delete_leave);
                    }
                },
                error: function (data) {
                    toastr.error(message.unable_to_delete_leave);
                    var deleteConfirmModalBox = '#delete-model-box';
                    $(deleteConfirmModalBox).modal('hide');
                    $('.page-loader-wrapper').hide();
                }
            });
        });

        $(document).on('change', '#video_conferencing_mode', function() {
            $('.videoLink').show();
        });

        $("#user_services").treeMultiselect({
            enableSelectAll: true,
            searchable: true,
            startCollapsed: true,
            onChange: function (allSelectedItems, addedItems, removedItems) {
                var userServices = $('#user_services').val().length;
                if (userServices == 0) {
                    $('#user_services_error').show();
                    $('.tree-multiselect').css('border-color', '#f44436');
                } else {
                    $('#user_services_error').hide();
                    $('.tree-multiselect').css('border-color', '#D8D8D8');
                }
            }
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
            var $lastItem = $('.zevo_form_submit .custom-leave-wrap').last();
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

        if ($('#responsibilities').val() == 1){
            $('.wbs-availability').show();
            $('.event-presenter-availability').hide();
        } else if ($('#responsibilities').val() == '2'){
            $('.wbs-availability').hide();
            $('.event-presenter-availability').show();
        } else {
            $('.event-presenter-availability, .wbs-availability').show();
        }

        $(document).on('change', '#responsibilities', function(e) {
            var value = $(this).val();
            if(value == '2' || value == '3') {
                $('.expertise_wbs, .advance_notice_wbs').show();
            } else {
                $('.expertise_wbs, .advance_notice_wbs').hide();
            }

            if (value == '1'){
                $('.wbs-availability').show();
                $('.event-presenter-availability').hide();
            } else if (value == '2'){
                $('.wbs-availability').hide();
                $('.event-presenter-availability').show();
            } else {
                $('.event-presenter-availability, .wbs-availability').show();
            }
        });
        
        
    });

    function formSubmit() {
        $('#userEdit').valid();
        var selectedMembers = $('#user_services').val().length;
        if (selectedMembers == 0 && $('#user_type').val() == 'wellbeing_specialist') {
            event.preventDefault();
            $('#userEdit').valid();
            $('#user_services_error').show();
            $('.tree-multiselect').css('border-color', '#f44436');
        } else {
            $('#user_services_error').hide();
            $('.tree-multiselect').css('border-color', '#D8D8D8');
        }
    }
</script>
<script src="{{ mix('js/users/slots.js') }}">
</script>
@endsection
