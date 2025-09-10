@extends('layouts.app')

@section('after-styles')
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.report.event.breadcrumb',[
    'mainTitle'  => trans('bookingreport.summarytab.title.index_title'),
    'breadcrumb' => 'bookingreport.calendar',
    'back'       => true,
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card form-card">
            <div class="card-body">
                <div class="card-inner">
                    <div class="row">
                        <div class="col-xxl-4 col-lg-3 mb-4">
                            <img src="{{ $event->logo }}"/>
                        </div>
                        <div class="col-xxl-8 col-lg-9">
                            <div class="row event-row-list">
                                <div class="col-lg-4 gray-900">
                                    {{ trans('marketplace.booking_details.form.labels.event_name') }}
                                </div>
                                <div class="col-lg-8 gray-600">
                                    {{ $event->name }}
                                </div>
                                <div class="col-lg-4 gray-900">
                                    {{ trans('marketplace.booking_details.form.labels.duration') }}
                                </div>
                                <div class="col-lg-8 gray-600" data-duration="">
                                    {{ convertToHoursMins(timeToDecimal($event->duration), false, '%s %s') }}
                                </div>
                                <div class="col-lg-4 gray-900">
                                    {{ trans('marketplace.booking_details.form.labels.location') }}
                                </div>
                                <div class="col-lg-8 gray-600">
                                    {{ $locationTypes[$event->location_type] }}
                                </div>
                                <div class="col-lg-4 gray-900">
                                    {{ trans('marketplace.booking_details.form.labels.capacity') }}
                                </div>
                                <div class="col-lg-8 gray-600">
                                    {{ $event->capacity }}
                                </div>
                                <div class="col-lg-4 gray-900">
                                    {{ trans('marketplace.booking_details.form.labels.description') }}
                                </div>
                                <div class="col-lg-8 gray-600">
                                    {!! $event->description !!}
                                    {!! $eventBookingId->notes !!}
                                </div>
                                <div class="col-lg-4 gray-900">
                                    {{ trans('marketplace.booking_details.form.labels.company') }}
                                </div>
                                <div class="col-lg-8 gray-600">
                                    {{ $eventCompany->name }}
                                </div>
                                <div class="col-lg-4 gray-900">
                                    {{ trans('marketplace.booking_details.form.labels.date') }}
                                </div>
                                <div class="col-lg-8 gray-600">
                                    {{ $bookingDate }}
                                </div>
                                <div class="col-lg-4 gray-900">
                                    {{ trans('marketplace.booking_details.form.labels.time-from') }}
                                </div>
                                <div class="col-lg-8 gray-600">
                                    {{ $startTime }}
                                </div>
                                <div class="col-lg-4 gray-900">
                                    {{ trans('marketplace.booking_details.form.labels.time-to') }}
                                </div>
                                <div class="col-lg-8 gray-600">
                                    {{ $endTime }}
                                </div>
                                <div class="col-lg-4 gray-900">
                                    {{ trans('marketplace.booking_details.form.labels.time_presenter') }}
                                </div>
                                <div class="col-lg-7 gray-600">
                                    <div class="picked-time-info mw-100">
                                        {!! $presenterString !!}
                                    </div>
                                </div>
                            </div>
                            <div class="pe-none" style="display: none;">
                                <label class="custom-checkbox">
                                    {{ trans('marketplace.booking_details.form.labels.register_all') }}
                                    {{ Form::checkbox('', 'on', $eventBookingId->register_all_users, ['class' => 'form-control', 'disabled' => true]) }}
                                    <span class="checkmark">
                                    </span>
                                    <span class="box-line">
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
