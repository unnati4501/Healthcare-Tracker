@extends('layouts.app')

@section('after-styles')
<link href="{{asset('assets/plugins/OwlCarousel2/owl.carousel.min.css?var='.rand())}}" rel="stylesheet"/>
<link href="{{asset('assets/plugins/OwlCarousel2/owl.theme.default.min.css?var='.rand())}}" rel="stylesheet"/>
<style>
    #recipeImagesCarousel .owl-nav { display: none; }
    #recipeImagesCarousel .owl-dots { margin-top: 10px; }
    #recipeImagesCarousel .owl-item img { width: auto; margin: 0 auto; }
</style>
@endsection

@section('content')
@include('admin.recipe.breadcrumb', ['mainTitle' => trans('labels.recipe.details')])
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <section class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            {{ $recordData->title }}
                        </h3>
                        <div class="card-tools">
                            <a class="btn btn-outline-primary btn-sm btn-effect me-1" href="{{ route('admin.recipe.index') }}">
                                Back
                            </a>
                            @permission('update-recipe')
                                @if((\Auth::user()->roles->first()->group == "zevo" && is_null($recordData->company_id)) || (\Auth::user()->roles->first()->group == "company" && ($recordData->company_id == \Auth::user()->company->first()->id) || ($recordData->creator_id == \Auth::user()->getKey())))
                            <a class="btn btn-primary btn-sm btn-effect text-white" href="{{ route('admin.recipe.edit', $recordData->id) }}">
                                Edit
                            </a>
                            @endif
                            @endauth
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="recipe-cook-view-area">
                                    <img alt="{{ $recordData->chefData['name'] }}" class="recipe-cook-img" src="{{ $recordData->chefData['image']['url'] }}" title="{{ $recordData->chefData['name'] }}"/>
                                </div>
                            </div>
                            <div class="col">
                                <div>
                                    <h5>
                                        <small class="m-0 text-muted">
                                            By
                                        </small>
                                        {{ $recordData->chefData['name'] }}
                                    </h5>
                                    <div>
                                        <i class="far fa-heart text-danger">
                                        </i>
                                        {{ numberFormatShort($recordData->getTotalLikes()) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr/>
                        <div class="row">
                            <div class="col-md-5">
                                <div class="text-center">
                                    <div class="owl-carousel owl-theme" id="recipeImagesCarousel">
                                        @foreach($recordData->getAllMediaData('logo', 'th_md') as $media)
                                        <div class="item">
                                            <img class="recipe-img" src="{{ $media['url'] }}" />
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <table class="table table-hover table-bordered recipe-list-table">
                                    <tr>
                                        <th>
                                            Calories (KCal)
                                        </th>
                                        <td>
                                            {{ $recordData->calories }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            Time
                                        </th>
                                        <td>
                                            {{ $recordData->cookingTimeFormated }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            Servings
                                        </th>
                                        <td>
                                            {{ $recordData->servings }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            Post Date / Time
                                        </th>
                                        <td>
                                            {{ $recordData->postDateTime }}
                                        </td>
                                    </tr>
                                </table>
                                <div class="">
                                    <label class="me-3">
                                        Sub-category :
                                    </label>
                                    @foreach($recordData->recipeSubCategories as $key => $subCategory)
                                    <label class="custom-checkbox" style="cursor: default;">
                                        {{ $subCategory }}
                                        <input checked="" disabled="" type="checkbox">
                                            <span class="checkmark">
                                            </span>
                                            <span class="checkbox-line">
                                            </span>
                                        </input>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @if(!empty($recordData->description))
                        <hr/>
                        <div>
                            <label>
                                Directions:
                            </label>
                            <div class="view-text-area bg-gray-1 border">
                                {!! $recordData->description !!}
                            </div>
                        </div>
                        @endif
                        <hr/>
                        <div class="row">
                            <div class="col-md-6">
                                <label>
                                    Ingredients
                                </label>
                                <table class="table table-bordered">
                                    @foreach($recordData->ingredients as $ingredient)
                                    <tr>
                                        <td>
                                            {{ $ingredient }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </table>
                            </div>
                            <div class="col-md-6">
                                <div>
                                    <label>
                                        Nutrition
                                    </label>
                                    <table class="table table-bordered">
                                        @foreach($recordData->nutritions as $nutrition)
                                        <tr>
                                            <td width="50%">
                                                {{ $nutrition->title }}
                                            </td>
                                            <td width="50%">
                                                {{ $nutrition->value }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</section>
@endsection
@section('after-scripts')
<script src="{{ asset('assets/plugins/OwlCarousel2/owl.carousel.min.js?var='.rand()) }}">
</script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#recipeImagesCarousel').owlCarousel({
            items: 1,
            loop: false,
            autoplay: true,
            autoplayTimeout: 5000,
            autoplayHoverPause: true,
            nav: true
        });
    });
</script>
@endsection
