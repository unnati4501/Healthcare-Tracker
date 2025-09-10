@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/OwlCarousel2/owl.carousel.min.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/OwlCarousel2/owl.theme.default.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.recipe.breadcrumb', [
  'mainTitle' => trans('recipe.title.view'),
  'breadcrumb' => Breadcrumbs::render('recipe.view'),
  'editRecipe' => (($role->group == "zevo" && is_null($recordData->company_id)) || ($role->group == "company" && ($recordData->company_id == $companyId) || ($recordData->creator_id == $user->id))),
  'backToListing' => true
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
                        <div class="col-xl-7 mb-4 col-md-6 order-md-2">
                            <h4 class="text-primary mb-3">
                                {{ $recordData->title }}
                            </h4>
                            <div class="d-flex justify-content-between align-items-center mb-xl-5 mb-4">
                                <div class="user-info-wrap">
                                    <div class="user-img">
                                        <img src="{{ $recordData->chefData['image']['url'] }}"/>
                                    </div>
                                    <h5>
                                        {{ trans('recipe.details.by') }} {{ $recordData->chefData['name'] }}
                                    </h5>
                                </div>
                                <span class="recipe-like-wrap">
                                    <i class="far fa-heart me-2 text-danger">
                                    </i>
                                    {{ numberFormatShort($recordData->getTotalLikes()) }}
                                </span>
                            </div>
                            <div class="row">
                                <div class="col-xl-8">
                                    <table class="table custom-table no-hover gray-900 mb-4">
                                        <tbody>
                                            <tr>
                                                <td class="border-top-0">
                                                    {{ trans('recipe.details.calories') }}
                                                </td>
                                                <td class="border-top-0">
                                                    {{ $recordData->calories }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    {{ trans('recipe.details.time') }}
                                                </td>
                                                <td>
                                                    {{ $recordData->cookingTimeFormated }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    {{ trans('recipe.details.servings') }}
                                                </td>
                                                <td>
                                                    {{ $recordData->servings }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    {{ trans('recipe.details.postdt') }}
                                                </td>
                                                <td>
                                                    {{ $recordData->postDateTime }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    {{ trans('recipe.details.type') }}
                                                </td>
                                                <td>
                                                    {{ (!empty($recordData->type) ? $recordData->type->type_name : 'NA') }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <div class="picked-time-info mw-100 w-xl-75">
                                        <span>
                                            {{ trans('recipe.details.category') }}
                                        </span>
                                        <span class="ms-5">
                                            {{ $recordData->recipeSubCategories->implode(', ') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-5 col-md-6">
                            <div class="owl-carousel owl-theme" id="recipeImagesCarousel">
                                @foreach($recordData->getAllMediaData('logo', ['w' => 640, 'h' => 320]) as $key => $media)
                                <div class="item {{ (($key == 0) ? 'selected' : '') }}">
                                    <img src="{{ $media['url'] }}"/>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-lg-4">
                            <h5 class="text-primary mb-3">
                                {{ trans('recipe.details.ingredients') }}
                            </h5>
                            <div class="ingredients-table">
                                <table class="table custom-table no-hover gray-900 mb-4">
                                    <tbody>
                                        @foreach($recordData->ingredients as $ingredient)
                                        <tr>
                                            <td>
                                                {{ $ingredient }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <h5 class="text-primary mb-3">
                                {{ trans('recipe.details.direction') }}
                            </h5>
                            <div class="form-group">
                                {!! $recordData->description !!}
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 ">
                        <h5 class="text-primary mb-3">
                            {{ trans('recipe.details.nutrition') }}
                        </h5>
                        <div class="nutrition-block">
                            @foreach($recordData->nutritions as $nutrition)
                            <div>
                                {{ $nutrition->title }}
                                <span>
                                    {{ $nutrition->value }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
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
            loop: false,
            margin: 5,
            nav: false,
            navText:  ["<i class='far fa-long-arrow-left'></i>", "<i class='far fa-long-arrow-right'></i>"],
            dots: true,
            items: 1,
            autoplayTimeout: 5000,
            autoplayHoverPause: true,
        });
    });
</script>
@endsection
