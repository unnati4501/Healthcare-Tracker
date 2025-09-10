@extends('layouts.app')

@section('after-styles')
<link href="{{asset('assets/plugins/tree-multiselect/tree-multiselect.css?var='.rand())}}" rel="stylesheet"/>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.team.breadcrumb',[
    'appPageTitle'=>trans('team.title.edit_form_title'),
    'breadcrumb' => 'team.edit',
    'create' => false,
    'setLimit' => false,
])
<!-- /.content-header -->
@endsection
@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="card form-card">
                {{ Form::open(['route' => ['admin.teams.update',$id], 'class' => 'form-horizontal zevo_form_submit', 'method'=>'PATCH','role' => 'form', 'id'=>'teamEdit','files' => true]) }}
                <div class="card-body">
                    <div class="row justify-content-center justify-content-md-start">
                        @include('admin.team.form')
                    </div>
                    @if($role->slug == 'super_admin')
                        @include('admin.team.manage-content')
                     @endif
                </div>
                <div class="card-footer">
                    <div class="save-cancel-wrap">
                        <a class="btn btn-outline-primary" href="{!! route('admin.teams.index') !!}">{{ trans('buttons.general.cancel') }}</a>
                        <button type="submit" class="btn btn-primary" onclick="formSubmit();">{{ trans('buttons.general.update') }}</button>
                    </div>
                </div>
              {{ Form::close() }}
            </div>
        </div>
    </section>
@endsection

@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\EditTeamRequest','#teamEdit') !!}
<script src="{{ asset('js/external/jquery.form.min.js?var='.rand()) }}"></script>
<script src="{{ asset('assets/plugins/tree-multiselect/tree-multiselect.js?var='.rand()) }}">
</script>
<script type="text/javascript">
    var defaultCourseImg = `{{ asset('assets/dist/img/placeholder-img.png') }}`;
    var contentValidateURL = '{{ route("admin.ajax.checkcompaniescontentvalidate") }}';
    var message = {
        imageValidError: `{{trans('team.message.image_valid_error')}}`,
        imageSizeError: `{{trans('team.message.image_size_2M_error')}}`,
        upload_image_dimension: `{{trans('team.message.upload_image_dimension')}}`
    };
    
    $("#group_content").treeMultiselect({
        enableSelectAll: true,
        searchable: true,
        startCollapsed: true,
        onChange: function (allSelectedItems, addedItems, removedItems) {
            var selectedContent = $('#group_content').val().length;
            console.log(selectedContent);
            if (selectedContent == 0) {
                $('#teamEdit').valid();
                $('#group_content-error').show();
                $('.tree-multiselect').css('border-color', '#f44436');
            } else {
                $('#group_content-error').hide();
                $('.tree-multiselect').css('border-color', '#D8D8D8');
            }
        }
    });

    function formSubmit() {
        var selectedMembers = $('#group_content').val().length;
        var _token = $('input[name="_token"]').val();
        if (selectedMembers != 0) {
            if($('#teamEdit').valid() == true) {
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
                        $('#teamEdit').valid();
                        $('#group_content-error').show();
                        $('.tree-multiselect').css('border-color', '#f44436');
                    } else {
                        if($('#teamEdit').valid() == true) {
                            $('#teamEdit').submit();
                        }
                        $('#group_content-error').hide();
                        $('.tree-multiselect').css('border-color', '#D8D8D8');
                    }
                }
            });
        } else {
            event.preventDefault();
            $('#teamEdit').valid();
            $('#group_content-error').show();
            $('.tree-multiselect').css('border-color', '#f44436');
        }
    }
</script>
<script src="{{ asset('js/team/edit-team.js') }}" type="text/javascript">
</script>
@endsection