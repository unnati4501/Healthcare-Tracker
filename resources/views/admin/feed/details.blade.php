@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.feed.breadcrumb',[
    'appPageTitle' => trans('feed.title.details'),
    'breadcrumb' => 'feed.details',
    'create'     => false,
    'back'       => true,
    'edit'       => (($role->group == 'zevo') || ($role->group == 'company' && $feedData->company_id == $user->company->first()->id) || ($role->group == 'reseller' && ($feedData->company_id == $user->company->first()->id || $isShowButton == true ))),
])
<!-- /.content-header -->
@endsection
@section('content')
<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="card form-card">
            <div class="card-body">
                <div class="card-inner">
                    <div class="row">
                        <div class="col-xl-7 mb-4 order-xl-2">
                           @if($feedData->type == 1)
                            <div class="mus-player">
                                <img src="{{ $feedData->audio_background }}"/>
                                <audio class="audio-player" controls="">
                                    <source src="{{ $feedData->getFirstMediaUrl('audio') }}">
                                    </source>
                                </audio>
                            </div>
                            @elseif($feedData->type == 2)
                            <div class="video-wrap">
                                <video class="video-tag" controls="" poster="{{ $feedData->video_background }}">
                                    <source src="{{ $feedData->getFirstMediaUrl('video') }}" type="video/mp4">
                                        {{ trans('feed.message.video_not_supported') }}
                                    </source>
                                </video>
                            </div>
                            @elseif($feedData->type == 3)
                            <div class="video-wrap">
                                <iframe allowfullscreen="" frameborder="0" height="350" src="https://www.youtube.com/embed/{{ $feedData->getFirstMedia('youtube')->getCustomProperty('ytid') }}?playsinline=1&rel=0&showinfo=0&color=white" width="100%">
                                </iframe>
                            </div>
                            @elseif($feedData->type == 4)
                            <div class="view-text-area">
                                {!! $feedData->description !!}
                            </div>
                            @elseif($feedData->type == 5)
                            <div class="view-text-area">
                                <iframe title="vimeo-player" src="https://player.vimeo.com/video/{{ $feedData->getFirstMedia('vimeo')->getCustomProperty('vmid') }}" width="100%" height="350" frameborder="0" allowfullscreen></iframe>
                            </div>
                            @endif
                        </div>
                        <div class="col-xl-5">
                           <div class="row event-row-list">
                               <div class="col-lg-6 gray-900">
                                    {{ trans('feed.table.logo') }}
                               </div>
                               <div class="col-lg-6 gray-600">
                                    <div class="logo-preview">
                                        <img src="{{ $feedData->logo }}" alt="">
                                    </div>
                               </div>
                               <div class="col-lg-6 gray-900">
                                    {{ trans('feed.table.feed_name') }}
                                </div>
                                <div class="col-lg-6 gray-600">
                                   {{$feedData->title}}
                                </div>
                                <div class="col-lg-6 gray-900">
                                    {{ trans('feed.table.feed_subtitle') }}
                                </div>
                                <div class="col-lg-6 gray-600">
                                   {{$feedData->subtitle}}
                                </div>
                                <div class="col-lg-6 gray-900">
                                    {{ trans('feed.table.sub_category') }}
                                </div>
                                <div class="col-lg-6 gray-600">
                                    {{ (!empty($feedData->subCategory()->first()))? $feedData->subCategory()->first()->name : "" }}
                                </div>
                                <div class="col-lg-6 gray-900">
                                    {{ trans('feed.table.start_date_time') }}
                                </div>
                                <div class="col-lg-6 gray-600">
                                    <div class="seperator-block">
                                        <span><i class="far fa-calendar-alt me-2 align-middle"></i> {{ Illuminate\Support\Carbon::parse($feedData->start_date)->setTimezone($feedData->timezone)->format(config('zevolifesettings.date_format.default_date')) }} </span> <span><i class="far fa-clock me-2 align-middle"></i> {{ Illuminate\Support\Carbon::parse($feedData->start_date)->setTimezone($feedData->timezone)->format(config('zevolifesettings.date_format.default_time')) }} </span>
                                    </div>
                                </div>
                                <div class="col-lg-6 gray-900">
                                    {{ trans('feed.table.end_date_time') }}
                                </div>
                                <div class="col-lg-6 gray-600">
                                    <div class="seperator-block">
                                        <span><i class="far fa-calendar-alt me-2 align-middle"></i> {{ Illuminate\Support\Carbon::parse($feedData->end_date)->setTimezone($feedData->timezone)->format(config('zevolifesettings.date_format.default_date')) }} </span> <span><i class="far fa-clock me-2 align-middle"></i> {{ Illuminate\Support\Carbon::parse($feedData->end_date)->setTimezone($feedData->timezone)->format(config('zevolifesettings.date_format.default_time')) }} </span>
                                    </div>
                                </div>
                           </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{!! route('admin.feeds.index') !!}">
                        {{ trans('buttons.general.cancel') }}
                    </a>
                    @if(($role->group == 'zevo') || ($role->group == 'company' && $feedData->company_id == $user->company->first()->id) || ($role->group == 'reseller' && ($feedData->company_id == $user->company->first()->id || $isShowButton == true )))
                    <a class="btn btn-primary" href="{!! route('admin.feeds.edit',$feedData->id) !!}">
                        {{ trans('buttons.general.edit') }}
                    </a>
                    @endif
                </div>
            </div> --}}
        </div>
    </div>
    <!-- /.container-fluid -->
</section>
<!-- /.content -->
@endsection
