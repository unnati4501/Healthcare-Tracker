<div class="main-header">
    <div class="container-fluid">
        <div class="d-flex">
            <div class="hamburger" data-widget="pushmenu">
                <div class="top-bun">
                </div>
                <div class="meat">
                </div>
                <div class="bottom-bun">
                </div>
            </div>
            <a class="worklife-logo" href="{{ url('/dashboard') }}">
                <img alt="Logo" class="header-logo" src="{{ (!empty(session('companyLogo')))? session('companyLogo') : asset('assets/dist/img/full-logo.png') }}"/>
            </a>
            <!-- Right navbar links -->
            <div class="d-flex ms-auto">
                <div class="align-self-center dropdown user profile-header-dropdown">
                    <div class="btn-group">
                        <a aria-expanded="false" data-bs-toggle="dropdown"href="javascript:void(0);">
                            <span class="user-image-span">
                                @if(!empty(Auth::user()->logo))
                                <img alt="User Image" class="user-image" src="{{ Auth::user()->logo }}" width="50"/>
                                @else
                                <img alt="User Image" class="user-image" src="{!! asset('assets/dist/img/user1-128x128.jpg') !!}"/>
                                @endif
                            </span>
                            <span class="hidden-xs user-name align-middle">
                                {{ trans('layout.navbar.texts.hi') }}, {{ Auth::user()->first_name }}
                            </span>
                            <i class="far fa-angle-down align-middle">
                            </i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                            <!-- User image -->
                            <div class="user-header">
                                <img src="{{ ((!empty(Auth::user()->logo)) ? Auth::user()->logo : asset('assets/dist/img/user1-128x128.jpg')) }}"/>
                                <p>
                                    {{ trans('layout.navbar.texts.hi') }}, {{ Auth::user()->first_name }}
                                    <a class="edit-profile" href="{{ route('admin.users.editProfile') }}" title="{{ trans('layout.navbar.texts.edit_profile') }}">
                                        <i class="far fa-pencil">
                                        </i>
                                    </a>
                                    <small class="d-block">
                                        {{ trans('layout.navbar.texts.member') }} {{ date('M, Y', strtotime(Auth::user()->created_at)) }}
                                    </small>
                                </p>
                            </div>
                            <!-- Menu Footer-->
                            <div class="user-footer">
                                <a class="" href="{{ route('admin.users.changepassword') }}">
                                    {{ trans('layout.navbar.buttons.change_password') }}
                                </a>
                                <a class="" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    {{ trans('layout.navbar.buttons.signout') }}
                                </a>
                                <form action="{{ route('logout') }}" id="logout-form" method="POST" style="display: none;">
                                    @csrf
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>