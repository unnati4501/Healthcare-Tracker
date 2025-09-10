@extends('layouts.app')

@section('after-styles')
<link href="{{asset('assets/plugins/tree-multiselect/tree-multiselect.css?var='.rand())}}" rel="stylesheet"/>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.team.breadcrumb',[
    'appPageTitle'=>trans('team.title.add_form_title'),
    'breadcrumb' => 'team.create',
    'create' => false,
    'setLimit' => false,
])
<!-- /.content-header -->
@endsection
@section('content')

<section class="content">
    <div class="container-fluid">
        <div class="card form-card">
            {{ Form::open(['route' => 'admin.teams.store', 'class' => 'form-horizontal zevo_form_submit', 'method'=>'post','role' => 'form', 'id'=>'teamAdd','files' => true]) }}
            <div class="card-body">
                <div class="row justify-content-center justify-content-md-start">
                    <div class="row">
                        @include('admin.team.form')
                    </div>
                    
                </div>
                @if($role->slug == 'super_admin')
                    @include('admin.team.manage-content')
                @endif
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{!! route('admin.teams.index') !!}">
                        {{ trans('buttons.general.cancel') }}
                    </a>
                    <button class="btn btn-primary" onclick="formSubmit();" type="submit">
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
{!! JsValidator::formRequest('App\Http\Requests\Admin\CreateTeamRequest','#teamAdd') !!}
<script src="{{ asset('js/external/jquery.form.min.js?var='.rand()) }}"></script>
<script src="{{ asset('assets/plugins/tree-multiselect/tree-multiselect.js?var='.rand()) }}">
</script>
<script type="text/javascript">
    var contentValidateURL = '{{ route("admin.ajax.checkcompaniescontentvalidate") }}';
    var companyDepartmentUrl = `{{ route("admin.ajax.companyDepartment", ":id") }}`,
        departmentLocationUrl = `{{ route("admin.ajax.departmentLocation", ":id") }}`,
        oldTeamLocation = `{{ old('teamlocation') }}`,
        oldCompany = "{{ old('company') }}",
        defaultCourseImg = `{{ asset('assets/dist/img/placeholder-img.png') }}`
        oldDepartment = "{{ old('department') }}";
    var message = {
        imageValidError: `{{trans('team.message.image_valid_error')}}`,
        imageSizeError: `{{trans('team.message.image_size_2M_error')}}`,
        upload_image_dimension: `{{trans('team.message.upload_image_dimension')}}`,
    };

    $("#group_content").treeMultiselect({
        enableSelectAll: true,
        searchParams: ['section', 'text'],
        searchable: true,
        startCollapsed: true
    });

    function formSubmit() {
        var selectedMembers = $('#group_content').val().length;
        var _token = $('input[name="_token"]').val();
        if (selectedMembers != 0) {
            if($('#teamAdd').valid() == true) {
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
                        $('#teamAdd').valid();
                        $('#group_content-error').show();
                        $('.tree-multiselect').css('border-color', '#f44436');
                    } else {
                        if($('#teamAdd').valid() == true) {
                            $('#teamAdd').submit();
                        }
                        $('#group_content-error').hide();
                        $('.tree-multiselect').css('border-color', '#D8D8D8');
                    }
                }
            });
        } else {
            event.preventDefault();
            $('#teamAdd').valid();
            $('#group_content-error').show();
            $('.tree-multiselect').css('border-color', '#f44436');
        }
    }
</script>
<script src="{{ asset('js/team/create-team.js') }}" type="text/javascript">
</script>
@endsection
