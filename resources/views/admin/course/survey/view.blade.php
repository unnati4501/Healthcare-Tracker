@extends('layouts.app')

@section('after-styles')
<style type="text/css">
    .qus-option-table tbody tr:not(:first-child) td.show_del .remove-option {
        display: block !important;
    }
    .qus-option-table tbody tr:not(:first-child) td.show_del .add-option {
        display: none !important;
    }
    .qus-option-table tbody tr:last-child td .remove-option,
    .qus-option-table tbody tr:not(:last-child) td .add-option {
        display: none;
    }
</style>
@endsection

@section('content')
@include('admin.course.survey.breadcrumb', [ 'appPageTitle' => trans('labels.course.view_survey'), 'type' => ucfirst($record->type) . " Survey", "showbackbutton" => true ])
<section class="content no-default-select2" style="pointer-events: none;">
    <div class="container-fluid">
        <div class="row">
            <section class="col-lg-12">
                <div class="card">
                    {{ Form::open(['class' => 'form-horizontal', 'role' => 'form']) }}
                    <div class="card-body">
                        @include('admin.course.survey.view-form')
                    </div>
                    {{ Form::close() }}
                </div>
            </section>
        </div>
    </div>
</section>
@endsection
