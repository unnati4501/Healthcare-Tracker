@extends('layouts.app')

@section('after-styles')
@if($roleType != 'rca')
<link href="{{asset('assets/plugins/tree-multiselect/tree-multiselect.css?var='.rand())}}" rel="stylesheet"/>
@endif
<link href="{{asset('assets/plugins/jonthornton-timepicker/jquery.timepicker.min.css?var='.rand())}}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/datepicker/datepicker3.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/jonthornton-timepicker/jquery.timepicker.min.css?var='.rand()) }}" rel="stylesheet"/>
<style type="text/css">
    .event-form-duration-field .ui-timepicker-select option:disabled { display: none; }
    ul.select2-selection__rendered {
        max-height: 200px;
        overflow-y: auto !important;
        padding-left: 12px;
    }
</style>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.event.breadcrumb', [
    'mainTitle' => trans('event.title.add'),
    'breadcrumb' => Breadcrumbs::render('event.create'),
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content no-default-select2">
    <div class="container-fluid">
        <div class="card form-card">
            {{ Form::open(['route' => 'admin.event.store', 'class' => 'form-horizontal zevo_form_submit', 'method' => 'post', 'role' => 'form', 'id' => 'eventAdd', 'files' => true]) }}
            @if($roleType == 'rca')
            <div class="card-header detailed-header">
                <div>
                    <label class="custom-checkbox">
                        {{ trans('event.buttons.book_as_special_event') }}
                        {{ Form::checkbox('special_event', 'on', old('special_event', false), ['class' => 'form-control', 'id' => 'special_event']) }}
                        <span class="checkmark">
                        </span>
                        <span class="box-line">
                        </span>
                    </label>
                </div>
            </div>
            @endif
            <div class="card-body">
                @include('admin.event.form', ['edit' => false])
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{{ route('admin.event.index') }}">
                        {{ trans('buttons.general.cancel') }}
                    </a>
                    <button class="btn btn-primary" id="zevo_submit_btn" type="submit">
                        {{ trans('buttons.general.save') }}
                    </button>
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</section>
@endsection

@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\CreateEventRequest','#eventAdd') !!}
<script src="{{ asset('assets/plugins/jonthornton-timepicker/jquery.timepicker.min.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{asset('assets/plugins/jquery.numeric/jquery.numeric.min.js?var='.rand())}}" type="text/javascript">
</script>
@if($roleType != 'rca')
<script src="{{ asset('assets/plugins/tree-multiselect/tree-multiselect.js?var='.rand()) }}" type="text/javascript">
</script>
@endif
<script src="{{ asset('assets/plugins/ckeditor5/ckeditor.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{ asset('js/external/external-ckeditor.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{ asset('assets/plugins/datepicker/bootstrap-datepicker.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{ asset('assets/plugins/jonthornton-timepicker/jquery.timepicker.min.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{ asset('assets/plugins/moment/moment.min.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{ asset('assets/plugins/moment/moment-timezone-with-data-10-year-range.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{ asset('assets/plugins/jonthornton-timepicker/jquery.datepair.min.js?var='.rand()) }}" type="text/javascript">
</script>
<script type="text/javascript">
    var defaultCourseImg = `{{ asset('assets/dist/img/placeholder-img.png') }}`;
    var upload_image_dimension = `{{trans('event.messages.upload_image_dimension')}}`;
    var countOfPresenter = `{{ count($eventPresenters) }}`;
    var _a = moment('2021-01-01 00:00:00');
    var _b = moment('2021-01-01 00:30:00');
    var _minDiff = (_b.diff(_a, 'milliseconds', true) || 9000000);
    var _maxDuration = moment('2021-01-02 00:00:00').subtract(_minDiff + 1800000, 'ms').format('hh:mm A');
    function readURL(input, selector) {
        if (input != null && input.files.length > 0) {
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
                        $(input).parent('div').find('.invalid-feedback').remove();
                        $(selector).removeAttr('src');
                        toastr.error(upload_image_dimension);
                        readURL(null, selector);
                    }
                }
                $(selector).attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            $(selector).attr('src', defaultCourseImg);
        }
    }
    function loadPresenters() {
        const type = ($("#presenter").data('type') || "zsa"),
            params = {
                type: type,
            };
        if(type == "zsa") {
            params.subcategory = $('#subcategory').val();
        }
        $.ajax({
            url: "{{ route('admin.event.getPresenters') }}",
            dataType: "json",
            type: "GET",
            delay: 250,
            cache: false,
            data: params,
        })
        .done(function(data) {
            $("#presenter").removeAttr('disabled').html(data.data).select2('destroy').select2({
                multiple: true, closeOnSelect: false, width: '100%'
            });
            countOfPresenter = data.count;
            $("#select_all_presenters").removeAttr('disabled').prop('checked', false);
        })
        .fail(function() {
            toastr.error(`Failed to load presenters, please try again.`);
        });
    }

    $(document).ready(function() {

        $('#presenter').on('select2:unselecting', function(e) {
            var selectedCount = $("#presenter :selected").length - 1;
            if(countOfPresenter != selectedCount) {
                $('#select_all_presenters').prop('checked', false);
            }
        });

        $('#presenter').on('select2:selecting', function(e) {
            var selectedCount = $("#presenter :selected").length + 1;
            if(countOfPresenter == selectedCount) {
                $('#select_all_presenters').prop('checked', true);
            }
        });

        $("#special_event_category").hide();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('.numeric').numeric({ decimal: false, negative: false });
        $('.select2').select2({allowClear: true, width: '100%'});
        $('.select2-multiple').select2({multiple: true, closeOnSelect: false, width: '100%'});
        $('#duration').timepicker({
            timeFormat: 'H:i',
            show2400: true,
            step: 30,
            useSelect: true,
            disableTimeRanges: [
                ['12:00am', '12:29am']
            ]
        });
        setTimeout(() => {
            $(`[name="ui-timepicker-duration"] option[value="24:00"]`).prop('disabled', true);
        }, 100);
        @if($roleType == 'zsa')
        $(document).on('change.select2', '#subcategory', function(e) {
            var state = ($(this).val() == "");
            $('#presenter').prop('disabled', state);
            $('#presenter').val('').trigger('change');
            $('#presenter-error').hide();
            loadPresenters();
        });
        @endif

        if ($("#eventCompanyList").length > 0) {
            $.mCustomScrollbar.defaults.scrollButtons.enable = true;
            $.mCustomScrollbar.defaults.axis = "yx";
            $("#eventCompanyList").mCustomScrollbar({
                axis: "y",
                theme: "inset-dark"
            });
        }

        $(document).on('change.select2', '#presenter', function(e) {
            $(this).valid();
        });

        $(document).on('change', '#select_all_presenters', function(e) {
            var isChecked = $(this).is(":checked");
            $('#presenter option').prop('selected', isChecked).trigger('change');
        });

        // initialize date picker
        $('#date').datepicker({
            startDate: '+1d',
            endDate: '+180d',
            autoclose: true,
            format: 'dd-mm-yyyy',
        }).on('changeDate', function(e) {
            $('#date').valid();
        });
        // initialize time picker
        $('.time').timepicker({
            showDuration: false,
            timeFormat: 'h:i A',
            step: 30,
            useSelect: true,
        });
        $('.time-range').datepair({
            'defaultTimeDelta': _minDiff // 60000 milliseconds => 1 Minute
        });

        $(document).on('change', '#timeFrom', function(e) {
            $('.time-range').off('change');
            var duration = $('#duration').val();
            var timeParts = duration.split(":");
            var milisecond = (+timeParts[0] * (60000 * 60)) + (+timeParts[1] * 60000);
            var timeTo = moment($('#timeFrom').timepicker('getTime')).add(milisecond, 'milliseconds');
            setTimeout(function() {
                $('#timeTo').timepicker('setTime', timeTo.toDate());
            }, 1);
        });

        $(document).on('change', '#duration', function(e) {
            $('.time-range').off('change');
            var duration = $('#duration').val();
            var timeParts = duration.split(":");
            var milisecond = (+timeParts[0] * (60000 * 60)) + (+timeParts[1] * 60000);
            var timeTo = moment($('#timeFrom').timepicker('getTime')).add(milisecond, 'milliseconds');
            setTimeout(function() {
                $('#timeTo').timepicker('setTime', timeTo.toDate());
            }, 1);
        });

        $('#special_event').change(function(){
            var _value = $(this).prop('checked');
            if(_value == true){
                $('#multiplepresenter').hide();
                $('#special_event_category').show();
                $('#main_subcategory').hide();
                $('.specialevent').show();
                $("#is_special").val(1);
            } else {
                $('.specialevent').hide();
                $('#multiplepresenter').show();
                $('#special_event_category').hide();
                $('#main_subcategory').show();
                $("#is_special").val(0);
            }
        });

        // update from time as per the event duration considering to time
        var timeTo = moment($('#timeFrom').timepicker('getTime')).add(_minDiff, 'milliseconds');
        $('#timeTo').timepicker('setTime', timeTo.toDate());
        // hide other options
        setTimeout(() => {
            $(`[name="ui-timepicker-timeFrom"] option[value="${_maxDuration}"]`).nextAll().hide();
            hidesPageLoader();
        }, 100);

        $(document).on('change', '#logo', function (e) {
            var previewElement = $(this).data('previewelement');
            $(this).valid();
            if(e.target.files.length > 0) {
                var fileName = e.target.files[0].name,
                    allowedMimeTypes = ['image/png', 'image/jpeg', 'image/jpg'];

                if (!allowedMimeTypes.includes(e.target.files[0].type)) {
                    toastr.error("{{trans('labels.common_title.image_valid_error')}}");
                    $(e.currentTarget).empty().val('');
                    $(this).parent('div').find('.custom-file-label').html('Choose File');
                    readURL(null, previewElement);
                } else if (e.target.files[0].size > 2097152) {
                    toastr.error("{{trans('labels.common_title.image_size_2M_error')}}");
                    $(e.currentTarget).empty().val('');
                    $(this).parent('div').find('.custom-file-label').html('Choose File');
                    readURL(null, previewElement);
                } else {
                    readURL(e.target, previewElement);
                    $(this).parent('div').find('.custom-file-label').html(fileName);
                }
            } else {
                readURL(null, previewElement);
            }
        });

        $("#fees, #capacity").focusout(function () {
            $(this).val($.trim($(this).val()).replace(/^0+/, ''));
        });

        @if($roleType != 'rca')
        // Custom Scrolling
        if ($("#eventCompanyList").length > 0) {
            $("#eventCompanyList").mCustomScrollbar({
                axis: "y",
                theme: "inset-dark",
                scrollButtons: {
                    enable: true,
                }
            });
        }
        $("#event_company").treeMultiselect({
            enableSelectAll: true,
            searchable: true,
            startCollapsed: true,
            onChange: function (allSelectedItems, addedItems, removedItems) {
                $('#eventAdd').validate().element("#event_company");
                var eventCompany = $('#event_company').val().length;
                if (eventCompany == 0) {
                    $('#eventAdd').valid();
                    $('#event_company').addClass('is-invalid');
                    $('#event_company-error').show();
                    $('.tree-multiselect').css('border-color', '#f44436');
                } else {
                    $('#event_company').removeClass('is-invalid');
                    $('#event_company-error').hide();
                    $('.tree-multiselect').css('border-color', '#D8D8D8');
                }
            }
        });
        @endif

        $(document).on('click', '#zevo_submit_btn', function(event) {
            @if($roleType != 'rca')
            var eventCompany = $('#event_company').val().length;
            if (eventCompany == 0) {
                event.preventDefault();
                $('#eventAdd').valid();
                $('#event_company').addClass('is-invalid');
                $('#event_company-error').show();
                $('.tree-multiselect').css('border-color', '#f44436');
            } else {
                $('#event_company').removeClass('is-invalid');
                $('#event_company-error').hide();
                $('.tree-multiselect').css('border-color', '#D8D8D8');
            }
            @endif

            var domEditableElement = document.querySelector( '.ck-editor__editable' );
                editorInstance = domEditableElement.ckeditorInstance;
                description = editorInstance.getData();
                description = $(description).text().trim();
                
            if (description == '') {
                event.preventDefault();
                $('#eventAdd').valid();
                $('#description-error').html('The event description field is required.').addClass('is-invalid').show();
            } else {
                if (description.length > 2500) {
                    event.preventDefault();
                    $('#description-error').html('The description field may not be greater than 2500 characters.').addClass('is-invalid').show();
                } else {
                    $('#description-error').removeClass('is-invalid').hide();
                }
            }
        });
    });
</script>
@endsection
