@extends('layouts.app')

@section('after-styles')
@if($roleType != 'rca')
<link href="{{asset('assets/plugins/tree-multiselect/tree-multiselect.css?var='.rand())}}" rel="stylesheet"/>
@endif
<link href="{{asset('assets/plugins/jonthornton-timepicker/jquery.timepicker.min.css?var='.rand())}}" rel="stylesheet"/>
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
    'mainTitle' => trans('event.title.edit'),
    'breadcrumb' => Breadcrumbs::render('event.edit'),
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content no-default-select2">
    <div class="container-fluid">
        <div class="card form-card">
            {{ Form::open(['url' => $submitURL, 'class' => 'form-horizontal zevo_form_submit', 'method' => 'PATCH', 'role' => 'form', 'id' => 'eventEdit', 'files' => true]) }}
            <div class="card-body">
                @include('admin.event.form', ['edit' => true])
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{{ $cancelButtonUrl }}">
                        {{ trans('buttons.general.cancel') }}
                    </a>
                    <button class="btn btn-primary" id="zevo_submit_btn" type="submit">
                        {{ trans('buttons.general.update') }}
                    </button>
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</section>
@endsection

@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\EditEventRequest','#eventEdit') !!}
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
<script type="text/javascript">
    var _bookedCompanyId = {!! json_encode($bookedCompanies) !!},
        _bookedPresenterId = {!! json_encode($bookedPresenters) !!},
        _bookedSelectedCompany = '{{ $bookedSelectedCompany }}';
    var defaultCourseImg = `{{ asset('assets/dist/img/placeholder-img.png') }}`;
    var upload_image_dimension = `{{trans('event.messages.upload_image_dimension')}}`;
    var countOfPresenter = `{{ count($eventPresenters) }}`;

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
        var selected = $('#presenter option:selected').length,
            total_option = $('#presenter option').length;
        $('#select_all_presenters').prop('checked', (selected == total_option));
        
        if ($("#is_special").val() == "1") {
            $("#special_event_category").show();
            $('#main_subcategory').hide();
        } else {
            $("#special_event_category").hide();
            $('#main_subcategory').show();
        }

        if ($("#eventCompanyList").length > 0) {
            $.mCustomScrollbar.defaults.scrollButtons.enable = true;
            $.mCustomScrollbar.defaults.axis = "yx";
            $("#eventCompanyList").mCustomScrollbar({
                axis: "y",
                theme: "inset-dark"
            });
        }

        // Showing message for tree selection remove data
        if(localStorage.getItem("tree-selection-error-message")){
            toastr.error(localStorage.getItem("tree-selection-error-message"));
            localStorage.clear();
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });


        $(document).on('click', '#zevo_submit_btn', function(event) {
            @if($roleType != 'rca')
            var eventCompany = $('#event_company').val().length;
            if (eventCompany == 0) {
                event.preventDefault();
                $('#eventEdit').valid();
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
               
            if(description == ''){
                event.preventDefault();
                $('#eventEdit').valid();
                $('#description-error').html('The event description field is required.').addClass('is-invalid').show();
            } else {
                if(description.length > 2500) {
                    event.preventDefault();
                    $('#description-error').html('The description field may not be greater than 2500 characters.').addClass('is-invalid').show();
                } else {
                    $('#description-error').removeClass('is-invalid').hide();
                }
            }
        });

        $('.numeric').numeric({ decimal: false, negative: false });
        $('.select2').select2({allowClear: true, width: '100%'});
        $('.select2-multiple').select2({multiple: true, closeOnSelect: false, width: '100%'});
        $('#duration').timepicker({
            timeFormat: 'H:i',
            show2400: true,
            step: 30,
            useSelect: '{{ (!$fieldDisableStatus) }}',
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

        // $("#presenter").select2({
        //     multiple: true,
        //     closeOnSelect: false,
        //     minimumResultsForSearch: Infinity,
        //     minimumInputLength: 1,
        //     language: {
        //         errorLoading: function () {
        //             return "{{ trans('labels.event.presenter_messages.errorLoading') }}";
        //         },
        //         inputTooShort: function (args) {
        //            return "{{ trans('labels.event.presenter_messages.inputTooShort') }}";
        //         },
        //         loadingMore: function () {
        //             return "{{ trans('labels.event.presenter_messages.loadingMore') }}";
        //         },
        //         noResults: function () {
        //             return "{{ trans('labels.event.presenter_messages.noResults') }}";
        //         },
        //         searching: function () {
        //             return "{{ trans('labels.event.presenter_messages.searching') }}";
        //         }
        //     },
        //     ajax: {
        //         url: "{{ route('admin.event.getPresenters') }}",
        //         dataType: "json",
        //         type: "get",
        //         delay: 250,
        //         cache: false,
        //         data: function (params) {
        //             var type = ($("#presenter").data('type') || "zsa"),
        //                 params = {
        //                     q: params.term,
        //                     page: (params.page || 1),
        //                     perPage: 25,
        //                     type: type,
        //                 };
        //             if(type == "zsa") {
        //                 params.subcategory = $('#subcategory').val();
        //             }
        //             return params;
        //         },
        //         processResults: function (data) {
        //             return {
        //                 results: data.data,
        //                 pagination: {
        //                     more: data.pagination.more,
        //                 }
        //             };
        //         }
        //     }
        // });

        $(document).on('change.select2', '#presenter', function(e) {
            $(this).valid();
        });

        $('#presenter').on('select2:unselecting', function(e) {
            if($.inArray(parseInt(e.params.args.data.id), _bookedPresenterId) !== -1) {
                toastr.clear();
                toastr.error("Presenter is having an upcoming event so you cannot remove.");
                e.preventDefault();
            }
            $('#select_all_presenters').prop('checked', false);
        });

        $('#presenter').on('select2:selecting', function(e) {
            var selectedCount = $("#presenter :selected").length + 1;
            if(countOfPresenter == selectedCount) {
                $('#select_all_presenters').prop('checked', true);
            }
        });

        $(document).on('change', '#select_all_presenters', function(e) {
            var isChecked = $(this).is(":checked");
            $('#presenter option').prop('selected', isChecked).trigger('change');
        });

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
            }
        });

        $("#fees, #capacity").focusout(function () {
            $(this).val($.trim($(this).val()).replace(/^0+/, ''));
        });

        @if($roleType != 'rca')
        $("#event_company").treeMultiselect({
            enableSelectAll: true,
            searchable: true,
            startCollapsed: true,
            onChange: function (allSelectedItems, addedItems, removedItems) {
                $('#eventEdit').validate().element("#event_company");
                var eventCompany = $('#event_company').val().length;
                if(removedItems.length > 0) {
                    var _removeItem = removedItems[0].value;
                    if($.inArray(parseInt(_removeItem), _bookedCompanyId) !== -1){
                        localStorage.setItem("tree-selection-error-message","Company is having an upcoming event so you cannot remove the company.");
                        window.location.reload();
                        return false;
                    }
                }
                if (eventCompany == 0) {
                    $('#eventEdit').valid();
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
        $(document).on('click', '.remove-selected', function(e) {
            e.stopPropagation();
            var _id = $(this).parent().data('value');
            if($.inArray(parseInt(_id), _bookedCompanyId) !== -1) {
                localStorage.setItem("tree-selection-error-message","Company is having an upcoming event so you cannot remove the company.");
                window.location.reload();
                return false;
            }
        });
         $(document).on('click', '#eventCompanyList .unselect-all', function(e) {
            if(_bookedSelectedCompany == false){
                localStorage.setItem("tree-selection-error-message","Some company(s) is having an upcoming event so you cannot remove those company.");
                window.location.reload();
                return false;
            }
        });
        @endif
    });
</script>
@endsection
