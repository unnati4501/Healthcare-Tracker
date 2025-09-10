@extends('layouts.app')

@section('after-styles')
@if($isSA)
<link href="{{asset('assets/plugins/tree-multiselect/tree-multiselect.css?var='.rand())}}" rel="stylesheet"/>
@endif
<link href="{{asset('assets/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css?var='.rand())}}" rel="stylesheet"/>
<style type="text/css">
    .datetimepicker{ padding: 4px !important; }
    .datetimepicker .table-condensed th,.datetimepicker .table-condensed td { padding: 4px 5px; }
    .is-valid-cstm { border-color: #28a745 !important; }
    .is-invalid-cstm { border-color: #dc3545 !important; }
</style>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.feed.breadcrumb',[
    'appPageTitle' => trans('feed.title.clone_form_title'),
    'breadcrumb' => 'feed.clone',
    'create'     => false,
    'back'       => false,
    'edit'       => false,
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content no-default-select2">
    <div class="container-fluid">
        <div class="card form-card">
            {{ Form::open(['route' => ['admin.feeds.storeClone', $id], 'class' => 'form-horizontal zevo_form_submit', 'method'=>'PATCH','role' => 'form', 'id' => 'feedClone', 'files' => true]) }}
            <div class="card-body">
                @include('admin.feed.form', [ 'edit'=>false, 'clone' => true ])
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{!! route('admin.feeds.index') !!}">
                        {{trans('buttons.general.cancel')}}
                    </a>
                    <button class="btn btn-primary" id="feedSubmit" type="submit">
                        {{trans('buttons.general.clone')}}
                    </button>
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</section>
@endsection
@section('after-scripts')
<script src="{{ asset('assets/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/ckeditor5/ckeditor.js?var='.rand()) }}">
</script>
<script src="{{ asset('js/external/external-ckeditor.js?var='.rand()) }}">
</script>
<script src="{{ asset('js/external/jquery.form.min.js?var='.rand()) }}">
</script>
@if($isSA)
<script src="{{ asset('assets/plugins/tree-multiselect/tree-multiselect.js?var='.rand()) }}">
</script>
@endif
{!! JsValidator::formRequest('App\Http\Requests\Admin\CloneFeedRequest','#feedClone') !!}
<script type="text/javascript">
    var url = {
        feed_index: `{{ route('admin.feeds.index') }}`,
    },
    condition = {
        isSA: '{{ $isSA }}',
        creator_id: `{{ $feedData->company_id }}`,
    },
    message = {
        something_wrong_try_again: `{{ trans('feed.message.something_wrong_try_again') }}`,
        start_end_interval_message: '{{ trans('feed.message.start_end_interval_message') }}',
        start_date_required_message: '{{ trans('feed.message.start_date_required_message') }}',
        end_date_required_message: '{{ trans('feed.message.end_date_required_message') }}',
        uploading_media: '{{ trans('feed.message.uploading_media') }}',
        processing_media: '{{ trans('feed.message.processing_media') }}',
    };
</script>
<script type="text/javascript">
var bar = $('#mainProgrssbar'),
    percent = $('#mainProgrssbar .progpercent'),
    start = new Date(),
    end,
    creator_id = condition.creator_id;

start.setHours(start.getHours()+1);
end = new Date(new Date().setYear(start.getFullYear() + 100));

$('document').ready(function() {
    $('#feedClone').validate().settings.ignore = [];

    $('.select2').select2({
        allowClear: true,
        width: '100%'
    });

    $("#start_date").datetimepicker({
        format: 'yyyy-mm-dd hh:00',
        startDate: start,
        endDate: end,
        autoclose: true,
        fontAwesome:true,
        minView:'day',
        todayHighlight: true,
        pickerPosition: "top-right"
    }).on('changeDate', function () {
        $('#end_date').datetimepicker('setStartDate', new Date($(this).val()));
        $('#start_date').valid();
    });

    $("#end_date").datetimepicker({
        format: 'yyyy-mm-dd hh:00',
        startDate: start,
        endDate: end,
        autoclose: true,
        fontAwesome:true,
        minView:'day',
        todayHighlight: true,
        pickerPosition: "top-right"
    }).on('changeDate', function () {
        $('#start_date').datetimepicker('setEndDate', new Date($(this).val()));
        $('#end_date').valid();
    });

    $("#start_date,#end_date").keypress(function(event) {
        event.preventDefault();
    });

    $("#end_date").change(function(event) {
        $("#end_date").trigger("changeDate");
    });

    $("#start_date").change(function(event) {
        $("#start_date").trigger("changeDate");
    });

    
    $('#feedClone').ajaxForm({
        beforeSend: function() {
            $('.progress-loader-wrapper .status-text').html(message.uploading_media);
            $('.progress-loader-wrapper').show();
            $('#feedClone .card-footer button, #feedClone .card-footer a').attr('disabled', 'disabled');
            var percentVal = '0%';
            bar.width(percentVal)
            percent.html(percentVal);
        },
        uploadProgress: function(event, position, total, percentComplete) {
            var percentVal = percentComplete + '%';
            bar.width(percentVal)
            percent.html(percentVal);
            if(percentComplete == 100) {
                $('.progress-loader-wrapper .status-text').html(message.processing_media);
            }
        },
        success: function(data) {
            $('.progress-loader-wrapper').hide();
            $('#feedClone .card-footer button, #feedClone .card-footer a').removeAttr('disabled');
            var percentVal = '100%';
            bar.width(percentVal)
            percent.html(percentVal);
            if(data.status && data.status == 1) {
                window.location.replace(url.feed_index);
            } else {
                toastr.error((data.message && data.message != '') ? data.message : message.something_wrong_try_again);
            }
        },
        error: function(data) {
            $('.progress-loader-wrapper').hide();
            $('#feedClone .card-footer button, #feedClone .card-footer a').removeAttr('disabled');

            if(data.responseJSON && data.responseJSON.message && data.responseJSON.message != '') {
                toastr.error(data.responseJSON.message);
            } else {
                toastr.error(message.something_wrong_try_again);
            }

            var percentVal = '0%';
            bar.width(percentVal)
            percent.html(percentVal);
        },
        complete: function(xhr) {
            $('.progress-loader-wrapper').hide();
            $('#feedClone .card-footer button, #feedEdit .card-footer a').removeAttr('disabled');
        }
    });

    var _feed_type = $('#feed_type').val();
    if(_feed_type != "") {
        $('#main_wrapper').show();
        if(_feed_type == '1') {
            $('#audio_wrapper').show();
        } else if(_feed_type == '2') {
            $('#video_wrapper').show();
        } else if(_feed_type == '3') {
            $('#youtube_wrapper').show();
            $('#setPermissionList #check_2, #check_1').parent().parent().remove();
        } else if(_feed_type == '4') {
            $('#content_wrapper').show();
        } else if(_feed_type == '5') {
            $('#vimeo_wrapper').show();
        }
    }
});
</script>
@endsection
