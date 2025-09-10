@extends('layouts.app')

@section('after-styles')
<link href="{{asset('assets/plugins/datepicker/datepicker3.css?var='.rand())}}" rel="stylesheet"/>
<link href="{{asset('assets/plugins/jonthornton-timepicker/jquery.timepicker.min.css?var='.rand())}}" rel="stylesheet"/>
<link href="{{asset('assets/plugins/tree-multiselect/tree-multiselect.css?var='.rand())}}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.user.breadcrumb', [
    'mainTitle' => trans('labels.user.add_form_title'),
    'breadcrumb' => Breadcrumbs::render('user.create'),
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card form-card">
            {{ Form::open(['route' => ['admin.users.store'], 'class' => 'form-horizontal zevo_form_submit', 'role' => 'form', 'id' => 'userAdd', 'files' => true]) }}
            <div class="card-body">
                @include('admin.user.form', ['edit' => false])
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{{ route('admin.users.index') }}">
                        {{ trans('labels.buttons.cancel') }}
                    </a>
                    <button class="btn btn-primary" onclick="formSubmit()" type="submit">
                        {{ trans('labels.buttons.save') }}
                    </button>
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</section>
@endsection

@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\CreateUserRequest', '#userAdd') !!}
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
        teamUrl = '{{ route("admin.ajax.get-teams", ":id") }}',
        roleUrl = '{{ route("admin.ajax.roles", ":id") }}',
        getRoleDataUrl = '{{ route("admin.users.getRoleWiseCompanies") }}',
        today = new Date(),
        endDate = new Date(new Date().setYear(today.getFullYear() + 100)),
        minDOBDate = new Date(new Date().setYear(today.getFullYear() - 18)),
        defaultCourseImg = `{{ asset('assets/dist/img/placeholder-img.png') }}`,
        message = {
            upload_image_dimension: `{{ trans('user.messages.upload_image_dimension') }}`,
            choose_file: `{{ trans('user.form.placeholder.choose_file') }}`
        };
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
    }

    $(document).ready(function() {
        $('#userAdd').validate().settings.ignore = "";
        $('#date_of_birth').datepicker({
            startDate: new Date(new Date().setYear(today.getFullYear() - 100)),
            endDate: minDOBDate,
            autoclose: true,
            todayHighlight: false,
            format: 'yyyy-mm-dd',
        });

        $('#start_date').datepicker({
            startDate: today,
            autoclose: true,
            todayHighlight: true,
            format: 'yyyy-mm-dd',
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

        $(document).on('change', 'input[name="role_group"]', function(e) {
            var role_group = $("input[name='role_group']:checked").val();
            if(role_group == 'company' || role_group == 'reseller') {
                $('#company_wrapper, #start_date_div').show();
                $('[data-health-coach-wrapper], [data-availability-wrapper], [data-coach-details-wrapper], [data-ws-details-wrapper]').hide();
                $('#company').val('').trigger('change');
                $('#department').empty().val('').trigger('change').attr('disabled', true);
                $('#team').empty().val('').trigger('change').attr('disabled', true);
                $('#user_type').val('user').trigger('change');
            } else {
                $('#company').val('').trigger('change');
                $('#department').empty().val('').trigger('change').attr('disabled', false);
                $('#team').empty().val('').trigger('change').attr('disabled', false);
                $('#company_wrapper, #start_date_div').hide();
                $('[data-health-coach-wrapper]').show();
                $('#user_type').val('user').trigger('change');
            }

            $('#role, #company').attr('disabled', true);

            $.ajax({
                url: getRoleDataUrl,
                method: 'get',
                data: {
                    role_group: role_group,
                    _token: $('input[name="_token"]').val(),
                },
            })
            .done(function(data) {
                $('#company, #role')
                    .empty()
                    .attr('disabled', false)
                    .val('')
                    .trigger('change')
                    .append('<option value="">Select</option>')
                    .removeClass('is-valid');

                if (data.companies) {
                    $.each(data.companies, function(key, value) {
                        $('#company').append(`<option value="${key}">${value}</option>`);
                    });
                }

                if (data.roles) {
                    $.each(data.roles, function(key, value) {
                        $('#role').append(`<option value="${key}">${value}</option>`);
                    });
                    $("#companyRolesarr").val(JSON.stringify(data.roles));
                }

                if(role_group == "zevo") {
                    if($('#user_type').val() == 'health_coach') {
                        $('#role').prop('disabled', true);
                    } else {
                        $('#role').prop('disabled', false);
                    }
                } else {
                    $('#role').prop('disabled', true);
                }
            })
            .fail(function(error) {
                toastr.error('Failed to load role group data');
            });
        });

        $(document).on('change', '#user_type', function(e) {
            var value = $(this).val(),
                availability = $('#availability').val();
            $('[data-ws-details-wrapper],.timezone').hide();
            if(value == 'health_coach') {
                $('[data-availability-wrapper], [data-coach-details-wrapper]').show();
                $('#role').prop('disabled', true);
                $('.dateofbirth,.height,.weight').hide();
                $('.timezone').show();
            } else if(value == 'wellbeing_specialist'){
                $('[data-ws-details-wrapper]').show();
                $('[data-availability-wrapper], [data-coach-details-wrapper]').hide();
                $('#role').prop('disabled', true);
                $('.dateofbirth,.height,.weight').hide();
                $('.timezone').show();
            } else if(value == 'counsellor') {
                $('[data-availability-wrapper], [data-coach-details-wrapper]').hide();
                $('#role').prop('disabled', true);
                $('.dateofbirth,.height,.weight').hide();
                $('.timezone').show();
            } else if(value == 'wellbeing_team_lead'){
                $('[data-availability-wrapper], [data-coach-details-wrapper], [data-ws-details-wrapper]').hide();
                $('#role').prop('disabled', true);
                $('.dateofbirth,.height,.weight').hide();
                $('.timezone').show();
            } else {
                $('[data-availability-wrapper], [data-coach-details-wrapper]').hide();
                $('#role').prop('disabled', false);
                $('.dateofbirth,.height,.weight').show();
            }
            $('#description').val($('#description').val() + ' ').trigger('keyup');
            $('#description').val($.trim($('#description').val())).trigger('keyup');
            $('#availability').trigger('change');
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
        
        $(document).on('change', '#video_conferencing_mode', function() {
            $('.videoLink').show();
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
        $('.event-presenter-availability, .wbs-availability').hide();
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
            } else {
                $(this).parent('div').find('.custom-file-label').html(message.choose_file);
                readURL(null, previewElement);
            }
        });

        $("#date_of_birth, #start_date, .custom-leave-from-date, .custom-leave-to-date").keypress(function(event) {
            event.preventDefault();
        });

        // Set default start date on document ready
        $('#start_date').datepicker("setDate", new Date());

        // to keep county and team field enabled when editing locations.

        // to populate county and team field after redirecting with error.
        setTimeout(function(){
            var department = "{{ old('department') }}",
                team = "{{ old('team') }}";

            if (department != '' && department != undefined) {
                $('#department').select2('val', department);
            }
            if (team != '' && team != undefined) {
                $('#team').select2('val', team);
            }
        }, 1000);

        $("#height").focusout(function () {
            $(this).val($.trim($(this).val()).replace(/^0+/, ''));
        });

        $(document).on('change', '#company', function(e) {
            var value = ($(this).val() || "");
            if (value != "") {
                $('#role, #department').attr('disabled', true);
                $.ajax({
                    url: getRoleDataUrl,
                    method: 'get',
                    data: {
                        company: value,
                        _token: $('input[name="_token"]').val()
                    }
                }).done(function(data) {
                    $('#department, #role').empty().attr('disabled', false).val('').trigger('change').append('<option value="">Select</option>').removeClass('is-valid');
                    if (data.departments) {
                        $.each(data.departments, function(key, value) {
                            $('#department').append(`<option value="${key}">${value}</option>`);
                        });
                    }
                    if (data.roles) {
                        $.each(data.roles, function(key, value) {
                            $('#role').append(`<option value="${key}">${value}</option>`);
                        });
                        $("#companyRolesarr").val(JSON.stringify(data.roles));
                    }
                });
            }
        });
        $(document).on('change', '#department', function(e) {
            var value = ($(this).val() || "");
            if (value != "") {
                $('#team').attr('disabled', true);
                $.ajax({
                    url: teamUrl.replace(':id', value),
                    method: 'get',
                    data: {
                        _token: $('input[name="_token"]').val()
                    }
                }).done(function(data) {
                    $('#team').empty().attr('disabled', false).val('').trigger('change').append('<option value="">Select</option>').removeClass('is-valid');
                    if (data.result) {
                        $.each(data.result, function(key, value) {
                            $('#team').append(`<option value="${value.id}">${value.name}</option>`);
                        });
                    }
                });
            }
        });
        $('#userAdd').ajaxForm({
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

        $('#user_type').change(function() {
            var _type = $(this).val();
            if(_type == 'counsellor'){
                $('.counsellor_skills_section').show();
            } else if(_type == 'wellbeing_specialist') {
                $('.counsellor_skills_section').hide();
                $('.cover_picture').show();
            } else {
                $('.counsellor_skills_section').hide();
            }
        });
        
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
        

        //Add multiple custom leave boxes
        $('.addCustomLeaveDates').on('click', function() {
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
                    showButtonPanel: false,
                });
            
            // This is used for identify current raw of leave.
            $newItem.closest('.custom-leave-wrap').attr('data-order', currentFormId);
                $('#total-form-custom-leaves').val(currentFormId);
        });

        // Delete custome leave.
        $('body').on('click', '.delete-custom-leave', function() {
            var customLeaveSelector = $(this).closest('.custom-leave-wrap');
            var totalCustomLeaveInForm = $(".zevo_form_submit").find('.custom-leave-wrap');
            if (totalCustomLeaveInForm.length == 1) {
                // toastr.error("custom leave has been delete");
            } else {
                customLeaveSelector.remove();
                $('#total-form-custom-leaves').val($(".zevo_form_submit").find('.custom-leave-wrap').length);
            }
        });
    });

    function formSubmit() {
        $('#userAdd').valid();
        var selectedMembers = $('#user_services').val().length;
        if (selectedMembers == 0 && $('#user_type').val() == 'wellbeing_specialist') {
            event.preventDefault();
            $('#userAdd').valid();
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
