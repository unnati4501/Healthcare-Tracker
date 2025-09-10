@if (\Auth::check())
    @include('errors.layouts.logged')
@else
    @include('errors.layouts.public')
@endif

