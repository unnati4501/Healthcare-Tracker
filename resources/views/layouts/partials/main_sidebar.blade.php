<!-- Main Sidebar Container -->
<aside class="main-sidebar">
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <!-- Sidebar Menu -->
        <nav class="pt-2">
            <ul class="nav nav-sidebar flex-column" data-accordion="false" data-widget="treeview" role="menu">
                @php
                    $user     = auth()->user();
                    $userRole = $user->roles()->where('slug', '=', 'company_admin')->first();
                    $role     = getUserRole();
                    $company  = $user->company->first();
                    $wsDetails = $user->wsuser()->first();
                    $wcDetails = $user->healthCoachUser()->first();
                @endphp
                <li class="nav-item">
                    <a class="nav-link {{ request()->is(app()->getLocale().'/dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        <span class="icon-dashboard-icon">
                        </span>
                        <p>
                            {{ trans('layout.sidebar.dashboard') }}
                        </p>
                    </a>
                </li>
                @if($role->group == 'zevo')
                @if (access()->allow('manage-category') || access()->allow('manage-category-tags'))
                <li class="nav-item has-treeview {{ ((request()->is(app()->getLocale().'/admin/categories*') || request()->is(app()->getLocale().'/admin/subcategories*') || request()->is(app()->getLocale().'/admin/category-tags*')) ? 'menu-open' : '') }}">
                    <a class="nav-link" href="javascript:void(0);">
                        <i class="fal icon-manage-categories-icon menu-icon nav-icon">
                        </i>
                        <p>
                            {{ trans('layout.sidebar.categories.title') }}
                            <i class="far fa-angle-down treeview-arrow">
                            </i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @permission('manage-category')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/categories*') || request()->is(app()->getLocale().'/admin/subcategories*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.categories.master-category') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                        @permission('manage-category-tags')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/category-tags*') || request()->is(app()->getLocale().'/admin/category-tags*') ? 'active' : '' }}" href="{{ route('admin.categoryTags.tag-index') }}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.categories.category-tags') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                    </ul>
                </li>
                @endauth
                @endif
                <?php if (access()->
                allow('manage-user') || access()->allow('manage-role') || access()->allow('import-users') ): ?>
                <li class="nav-item has-treeview {{ request()->is(app()->getLocale().'/admin/roles*') || request()->is(app()->getLocale().'/admin/users*') ? 'menu-open' : '' }}">
                    <a class="nav-link" href="javascript:void(0);">
                        <i class="fal fa-users-cog menu-icon fa-fw">
                        </i>
                        <p>
                            {{ trans('layout.sidebar.users.title') }}
                            <i class="far fa-angle-down treeview-arrow">
                            </i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @if($role->group == 'zevo')
                            @permission('manage-role')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/roles*') ? 'active' : '' }}" href="{!! route('admin.roles.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.users.roles') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                        @endif
                        @permission('manage-user')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/users*') ? 'active' : '' }}" href="{!! route('admin.users.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.users.user_list') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                    </ul>
                </li>
                @endauth
                <?php if ( access()->
                allow('manage-company') || access()->allow('manage-department') || access()->allow('manage-team') || access()->allow('manage-location') ):
                ?>
                <li class="nav-item has-treeview {{ request()->is(app()->getLocale().'/admin/companies*') || request()->is(app()->getLocale().'/admin/domains*') || request()->is(app()->getLocale().'/admin/departments*') || request()->is(app()->getLocale().'/admin/teams*') || request()->is(app()->getLocale().'/admin/locations*') || request()->is(app()->getLocale().'/admin/team-assignment*') || request()->is(app()->getLocale().'/admin/old-team-assignment*') ? 'menu-open' : '' }}">
                    <a class="nav-link" href="javascript:void(0);">
                        <i class="fal fa-building menu-icon fa-fw">
                        </i>
                        <p>
                            {{ trans('layout.sidebar.companies.title') }}
                            <i class="far fa-angle-down treeview-arrow">
                            </i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @php
                            $company = auth()->user()->company->first();
                        @endphp
                        @if($role->group == 'zevo')
                        @permission('manage-company')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/companies/zevo*') ? 'active' : '' }}" href="{{route('admin.companies.index', 'zevo')}}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.companies.zevo_companies') }}
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/companies/reseller*') ? 'active' : '' }}" href="{{route('admin.companies.index', 'reseller')}}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.companies.reseller_companies') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                        @endif
                        @if($role->group != 'zevo' && ($role->group == 'reseller' && $company->parent_id == null ))
                        @permission('manage-company')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/companies*') ? 'active' : '' }}" href="{{route('admin.companies.index', 'normal')}}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.companies.company_list') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                        @endif
                        @if(!empty($company) && $company->has_domain)
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/domains*') ? 'active' : '' }}" href="{!! route('admin.domains.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.companies.domain_list') }}
                                </p>
                            </a>
                        </li>
                        @endif
                        @if($role->group != 'company' || ($role->group == 'company' && getCompanyPlanAccess($user, 'team-selection')))
                        @permission('manage-department')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/departments*') ? 'active' : '' }}" href="{!! route('admin.departments.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.companies.department_list') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                        @permission('manage-team')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/teams*') ? 'active' : '' }}" href="{!! route('admin.teams.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.companies.team_list') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                        @endif
                        @permission('manage-location')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/locations*') ? 'active' : '' }}" href="{!! route('admin.locations.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.companies.location_list') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                        @if(($role->group == 'company' && getCompanyPlanAccess($user, 'team-selection')) || ($role->group == 'reseller' && $company->parent_id != null ))
                        @permission('team-assignment')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/team-assignment*') ? 'active' : '' }}" href="{!! route('admin.team-assignment.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.companies.team_assignment') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                        @endif
                    </ul>
                </li>
                @endauth
                <?php if (access()->
                allow('manage-meditation-category') || access()->allow('manage-meditation-library') || (access()->allow('manage-exercise') || access()->allow('manage-badge') || access()->allow('manage-course') || (access()->allow('manage-story') && ($role->group == 'zevo' || ($role->group == 'company' && getCompanyPlanAccess($user, 'explore')) || ($role->group == 'reseller' && getDTAccessForParentsChildCompany($user, 'explore')))) || access()->allow('manage-group') || access()->allow('manage-recipe')  && ($role->group == 'zevo' || ($role->group == 'company' && getCompanyPlanAccess($user, 'explore')) || ($role->group == 'reseller' && getDTAccessForParentsChildCompany($user, 'explore')))) || (access()->allow('manage-support') && ($role->group == 'zevo' || ($role->group == 'company' && getCompanyPlanAccess($user, 'supports')) || ($role->group == 'reseller' && getDTAccessForParentsChildCompany($user, 'supports')))) || access()->allow('manage-goal-tags') || access()->allow('webinar-management') || access()->allow('manage-mood-tags') || access()->allow('manage-moods') || access()->allow('view-moods-analysis') ||  access()->allow('manage-podcast') || access()->allow('manage-shorts')): ?>
                <li class="nav-item has-treeview {{ request()->is(app()->getLocale().'/admin/meditationtracks*') || request()->is(app()->getLocale().'/admin/exercises*') || (request()->is(app()->getLocale().'/admin/badges*') && $role->group != 'company') || request()->is(app()->getLocale().'/admin/masterclass*') || request()->is(app()->getLocale().'/admin/stories*') || request()->is(app()->getLocale().'/admin/recipe*') || request()->is(app()->getLocale().'/admin/groups*') || request()->is(app()->getLocale().'/admin/support/*') || request()->is(app()->getLocale().'/admin/goals*') || (request()->is(app()->getLocale().'/admin/moodAnalysis*') && $role->group != 'company') || request()->is(app()->getLocale().'/admin/moods*') || request()->is(app()->getLocale().'/admin/moodTags*') || request()->is(app()->getLocale().'/admin/webinar*') || request()->is(app()->getLocale().'/admin/podcasts*') || request()->is(app()->getLocale().'/admin/shorts*') ? 'menu-open' : '' }}">
                    <a class="nav-link" href="javascript:void(0);">
                        <i class="fal fa-file-alt menu-icon nav-icon">
                        </i>
                        <p>
                            {{ trans('layout.sidebar.contents.title') }}
                            <i class="far fa-angle-down treeview-arrow">
                            </i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @if($role->group == 'zevo')
                        @permission('manage-meditation-library')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/meditationtracks*') ? 'active' : '' }}" href="{!! route('admin.meditationtracks.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.contents.meditations') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                        @permission('manage-exercise')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/exercises*') ? 'active' : '' }}" href="{!! route('admin.exercises.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.contents.exercises') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                        @endif
                        @if($role->group == 'zevo' || ($role->group == 'reseller' && $company->parent_id != null && $company->allow_app == true))
                        @permission('manage-badge')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/badges*') ? 'active' : '' }}" href="{!! route('admin.badges.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.contents.badges') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                        @endif
                        @if($role->group == 'zevo')
                        @permission('manage-course')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/masterclass*') ? 'active' : '' }}" href="{!! route('admin.masterclass.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.contents.masterclass') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                        @endif
                        @if($role->group == 'zevo' || $role->group == 'company' || ($role->group == 'reseller' && getDTAccessForParentsChildCompany($user, 'explore')))
                        @permission('manage-story')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/stories*') ? 'active' : '' }}" href="{!! route('admin.feeds.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.contents.feeds') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                        @endif
                        @if($role->group == 'company' || ($role->group == 'reseller' && $company->parent_id != null && $company->allow_app == true ))
                        @permission('manage-group')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/groups*') ? 'active' : '' }}" href="{!! route('admin.groups.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.contents.groups') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                        @endif
                        @if($role->group == 'zevo' || $role->group == 'company' || ($role->group == 'reseller' && getDTAccessForParentsChildCompany($user, 'explore')))
                        @permission('manage-recipe')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/recipe*') ? 'active' : '' }}" href="{!! route('admin.recipe.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.contents.recipes') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                        @endif
                        @permission('manage-support')
                        @if($role->group == 'zevo' || ($role->group == 'company' && getCompanyPlanAccess($user, 'supports')) || ($role->group == 'reseller' && getDTAccessForParentsChildCompany($user, 'supports')))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/support*') ? 'active' : '' }}" href="{!! route('admin.support.list') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.contents.eap') }}
                                </p>
                            </a>
                        </li>
                        @endif
                        @endauth
                        @if($role->group == 'zevo')
                        @permission('manage-goal-tags')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/goals*') ? 'active' : '' }}" href="{!! route('admin.goals.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.contents.goals') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                        @permission('webinar-management')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/webinar*') ? 'active' : '' }}" href="{!! route('admin.webinar.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.contents.webinars') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                        @endif
                        @if($role->group == 'zevo' || ($role->group == 'reseller' && $company->parent_id != null && $company->allow_app == true))
                        @permission('view-moods-analysis')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/moodAnalysis*') ? 'active' : '' }}" href="{!! route('admin.moodAnalysis.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.moods.moods_analysis') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                        @endif
                        @if($role->group == 'zevo')
                        @permission('manage-moods')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/moods*') ? 'active' : '' }}" href="{!! route('admin.moods.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.moods.moods') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                        @permission('manage-mood-tags')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/moodTags*') ? 'active' : '' }}" href="{!! route('admin.moodTags.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.moods.tags') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                        @permission('manage-podcast')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/podcasts*') ? 'active' : '' }}" href="{!! route('admin.podcasts.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.contents.podcasts') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                        @permission('manage-shorts')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/shorts*') ? 'active' : '' }}" href="{!! route('admin.shorts.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.contents.shorts') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                        @endif
                    </ul>
                </li>
                @endauth
                @if($role->group == 'zevo' || $role->group == 'company' || ($role->group == 'reseller' && $company->allow_app == true ))
                @permissions(['manage-challenge', 'manage-inter-company-challenge', 'manage-challenge-image-library'])
                <li class="nav-item has-treeview {{ request()->is(app()->getLocale().'/admin/challenges*') || request()->is(app()->getLocale().'/admin/teamChallenges*') || request()->is(app()->getLocale().'/admin/companyGoalChallenges*') || request()->is(app()->getLocale().'/admin/interCompanyChallenges*') || request()->is(app()->getLocale().'/admin/personalChallenges*') || request()->is(app()->getLocale().'/admin/challenge-image-library*') || (request()->is(app()->getLocale().'/admin/badges*') && $role->group == 'company') || request()->is(app()->getLocale().'/admin/challenge-map-library*') || request()->is(app()->getLocale().'/admin/contentChallenge*') || request()->is(app()->getLocale().'/admin/contentChallengeActivity*')? 'menu-open' : '' }}">
                    <a class="nav-link" href="javascript:void(0);">
                        <i class="fal fa-trophy-alt menu-icon nav-icon">
                        </i>
                        <p>
                            @if(getCompanyPlanAccess($user, 'my-challenges'))
                            {{ trans('layout.sidebar.challenges.title') }}
                            @else
                            {{ trans('layout.sidebar.challenges.goal_title') }}
                            @endif
                            <i class="far fa-angle-down treeview-arrow">
                            </i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @if(($role->group == 'company' || ($role->group == 'reseller' && $company->parent_id != null && $company->allow_app == true)) && getCompanyPlanAccess($user, 'my-challenges'))
                        @permissions('manage-challenge')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/challenges*') ? 'active' : '' }}" href="{!! route('admin.challenges.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.challenges.individual') }}
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/teamChallenges*') ? 'active' : '' }}" href="{!! route('admin.teamChallenges.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.challenges.team') }}
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/companyGoalChallenges*') ? 'active' : '' }}" href="{!! route('admin.companyGoalChallenges.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.challenges.company') }}
                                </p>
                            </a>
                        </li>
                        @endauth()
                        @endif
                        @if($role->group == 'zevo' || $role->group == 'company' || ($role->group == 'reseller' && $company->parent_id != null) || getCompanyPlanAccess($user, 'my-challenges'))
                        @if(getCompanyPlanAccess($user, 'my-challenges'))
                        @permissions('manage-inter-company-challenge')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/interCompanyChallenges*') ? 'active' : '' }}" href="{!! route('admin.interCompanyChallenges.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.challenges.inter-company') }}
                                </p>
                            </a>
                        </li>
                        @endauth()
                        @endif
                        @if($role->group == 'zevo' || $role->group == 'company' || ($role->group == 'reseller' && $company->allow_app == true))
                        @permissions('manage-personal-challenge')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/personalChallenges*') ? 'active' : '' }}" href="{!! route('admin.personalChallenges.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    @if(getCompanyPlanAccess($user, 'my-challenges'))
                                    {{ trans('layout.sidebar.challenges.personal') }}
                                    @else
                                    {{ trans('layout.sidebar.challenges.goals') }}
                                    @endif
                                </p>
                            </a>
                        </li>
                        @endauth()
                        @endif
                        @endif
                        @if($role->group == 'zevo')
                        @permissions('manage-challenge-image-library')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/challenge-image*') ? 'active' : '' }}" href="{!! route('admin.challengeImageLibrary.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.challenges.image_library') }}
                                </p>
                            </a>
                        </li>
                        @endauth()
                        @endif
                        @if($role->group == 'zevo')
                        @permissions('manage-challenge-map-library')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/challenge-map-library*') ? 'active' : '' }}" href="{!! route('admin.challengeMapLibrary.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.challenges.map_library') }}
                                </p>
                            </a>
                        </li>
                        @endauth()
                        @endif
                        @if($role->group == 'company')
                        @permission('manage-badge')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/badges*') ? 'active' : '' }}" href="{!! route('admin.badges.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.contents.badges') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                        @endif
                        @if($role->group == 'zevo')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/contentChallenge*') || request()->is(app()->getLocale().'/admin/contentChallengeActivity*') ? 'active' : '' }}" href="{!! route('admin.contentChallenge.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.challenges.content_challenge') }}
                                </p>
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endauth
                @endif
                <?php if (access()->
                allow('manage-app-settings') || access()->allow('manage-onboarding') || access()->allow('manage-notification') || access()->allow('label-setting')  || access()->allow('app-theme') || access()->allow('manage-admin-alert')): ?>
                <li class="nav-item has-treeview {{ request()->is(app()->getLocale().'/admin/appsettings*') || request()->is(app()->getLocale().'/admin/appslides*') || request()->is(app()->getLocale().'/admin/notifications*') || request()->is(app()->getLocale().'/admin/label-settings*') || request()->is(app()->getLocale().'/admin/app-themes*') || request()->is(app()->getLocale().'/admin/company-plan*') || request()->is(app()->getLocale().'/admin/broadcast-message*') || request()->is(app()->getLocale().'/admin/admin-alerts*')? 'menu-open' : '' }}">
                    <a class="nav-link" href="javascript:void(0);">
                        <i class="fal fa-cog menu-icon nav-icon">
                        </i>
                        <p>
                            {{ trans('layout.sidebar.settings.title') }}
                            <i class="far fa-angle-down treeview-arrow">
                            </i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @if($role->group == 'zevo')
                        @permission('manage-app-settings')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/appsettings*') ? 'active' : '' }}" href="{!! route('admin.appsettings.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.settings.app_settings') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                        @permission('manage-onboarding')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/appslides*') ? 'active' : '' }}" href="{!! route('admin.appslides.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.settings.onboarding') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                        @endif
                        @permission('manage-notification')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/notifications*') ? 'active' : '' }}" href="{!! route('admin.notifications.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.settings.notifications') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                        @permission('label-setting')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/label-settings*') ? 'active' : '' }}" href="{!! route('admin.labelsettings.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.settings.label_settings') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                        @permission('app-theme')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/app-themes*') ? 'active' : '' }}" href="{{ route('admin.app-themes.index') }}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.settings.app_themes') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                        @permission('manage-broadcast-message')
                        @if(is_null($company) || (!is_null($company) && $company->allow_app == true))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/broadcast-message*') ? 'active' : '' }}" href="{!! route('admin.broadcast-message.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.settings.broadcast_message') }}
                                </p>
                            </a>
                        </li>
                        @endif
                        @endauth
                        @permission('manage-company-plan')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/company-plan*') ? 'active' : '' }}" href="{!! route('admin.company-plan.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.settings.manage_company_plans') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                        @permission('manage-admin-alert')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/admin-alerts*') ? 'active' : '' }}" href="{!! route('admin.admin-alerts.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.settings.admin_alerts') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                    </ul>
                </li>
                @endauth

                <!-- Wellbeing Team lead menu -->
                @if ($role->slug == 'wellbeing_team_lead')
                @permission('manage-clients')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is(app()->getLocale().'/admin/cronofy/clientlist*') ? 'active' : '' }}" href="{!! route('admin.cronofy.clientlist.index') !!}">
                            <i class="fal fa-calendar-check menu-icon nav-icon">
                            </i>
                            <p>
                                {{ trans('layout.sidebar.digital_therapy.client_list') }}
                            </p>
                        </a>
                    </li>
                @endauth
                
                @permission('manage-sessions')
                @php
                    $style = "";
                    if(request()->is(app()->getLocale().'/admin/cronofy/sessions*')){ 
                        $style = 'background:#4b599e;color:#fff';
                    }
                    @endphp
                    <li class="nav-item">
                        <a class="nav-link" style="{{$style}}" href="{{route('admin.cronofy.sessions.index')}}">
                            <i class="fal fa-calendar-check menu-icon nav-icon">
                            </i>
                            <p>
                                {{ trans('layout.sidebar.digital_therapy.sessions') }}
                            </p>
                        </a>
                    </li>
                @endauth
                @endif
                <!-- Wellbeing Team lead menu end-->
                
                <?php if (access()->
                allow('view-user-activities') || access()->allow('view-nps-feedbacks') || access()->allow('inter-company-report') || (access()->allow('booking-report') && $role->group != 'company') || access()->allow('manage-portal-survey') || access()->allow('view-digital-therapy') || access()->allow('masterclass-feedback') ||  access()->allow('eap-feedback') || (access()->allow('view-user-registrations') && $role->group != 'company') || access()->allow('view-digital-therapy')): ?>
                <li class="nav-item has-treeview {{ request()->is(app()->getLocale().'/admin/reports*') ? 'menu-open' : '' }}">
                    <a class="nav-link" href="javascript:void(0);">
                        <i class="fal fa-file-chart-line menu-icon nav-icon">
                        </i>
                        <p>
                            {{ trans('layout.sidebar.reports.title') }}
                            <i class="far fa-angle-down treeview-arrow">
                            </i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @if($role->group == 'zevo')
                        @permission('view-user-activities')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/reports/users-activities') ? 'active' : '' }}" href="{!! route('admin.reports.users-activities') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.reports.user_activities') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                        @endif
                        @if($role->group == 'zevo' || ($role->group == 'reseller' && $company->parent_id == null))
                        @if (access()->allow('view-nps-feedbacks') || access()->allow('manage-portal-survey'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/reports/nps') ? 'active' : '' }}" href="{!! route('admin.reports.nps') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.reports.customer_satisfaction') }}
                                </p>
                            </a>
                        </li>
                        @endif
                        @endif
                        @if($role->group == 'zevo')
                        @permission('inter-company-report')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/reports/inter-company') ? 'active' : '' }}" href="{!! route('admin.reports.intercompanyreport') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.reports.inter_company') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                        @endif
                        @if($role->group == 'zevo')
                        @permission('challenge-activity-report')
                        @php
                            $style = "";
                            if(request()->is(app()->getLocale().'/admin/reports/challenge-activity') || request()->is(app()->getLocale().'/admin/reports/getUserDailyHistoryData')){ 
                                $style = 'background:#4b599e;color:#fff';
                            }
                        @endphp
                        <li class="nav-item">
                            <a class="nav-link" style="{{$style}}" href="{!! route('admin.reports.challengeactivityreport') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.reports.challenge_activity') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                        @endif
                        @if($role->group == 'zevo' || ($role->group == 'company' && getCompanyPlanAccess($user, 'event')) || ($role->group == 'reseller' && getDTAccessForParentsChildCompany($user, 'event')))
                        @if (access()->allow('booking-report-detailed-view') || access()->allow('booking-report-summary-view') || access()->allow('booking-report-company-wise'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/reports/booking-report') ? 'active' : '' }}" href="{{ route('admin.reports.booking-report') }}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.reports.bookings_report') }}
                                </p>
                            </a>
                        </li>
                        @endif
                        @endif
                        @if(access()->allow('masterclass-feedback'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/reports/masterclass-feedback') ? 'active' : '' }}" href="{{ route('admin.reports.masterclass-feedback') }}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.reports.masterclass_feedback') }}
                                </p>
                            </a>
                        </li>
                        @endif
                        @if($role->group == 'zevo' || ($role->group == 'company' && getCompanyPlanAccess($user, 'explore')) || ($role->group == 'reseller' && getDTAccessForParentsChildCompany($user, 'explore')))
                        @if(access()->allow('content-report'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/reports/content-report') ? 'active' : '' }}" href="{{ route('admin.reports.content-report') }}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.reports.content_report') }}
                                </p>
                            </a>
                        </li>
                        @endif
                        @endif
                        @if($role->group == 'zevo')
                        @if (access()->allow('eap-feedback'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/reports/eap-feedback') ? 'active' : '' }}" href="{!! route('admin.reports.eap-feedback') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.reports.eap_feedback') }}
                                </p>
                            </a>
                        </li>
                        @endif
                        @endif
                        @if($role->group == 'zevo' || $role->group == 'reseller')
                        @if (access()->allow('view-user-registrations'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/reports/user-registration') ? 'active' : '' }}" href="{!! route('admin.reports.user-registration') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.reports.user_registration') }}
                                </p>
                            </a>
                        </li>
                        @endif
                        @endif
                        @if($role->group == 'zevo' || $role->slug == 'wellbeing_team_lead')
                        @if (access()->allow('view-digital-therapy'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/reports/digital-therapy') ? 'active' : '' }}" href="{!! route('admin.reports.digital-therapy') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.reports.digital_therapy') }}
                                </p>
                            </a>
                        </li>
                        @endif
                        @if (access()->allow('occupational-health-report'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/reports/occupational-health') ? 'active' : '' }}" href="{!! route('admin.reports.occupational-health') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.reports.occupational_health') }}
                                </p>
                            </a>
                        </li>
                        @endif
                        @endif
                        @if($role->group == 'zevo')
                        @if (access()->allow('usage-report'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/reports/usage-report') ? 'active' : '' }}" href="{!! route('admin.reports.usage-report') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.reports.usage_report') }}
                                </p>
                            </a>
                        </li>
                        @endif
                        @endif
                        @if($role->group == 'zevo')
                        @if (access()->allow('realtime-availability'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/reports/realtime-availability') ? 'active' : '' }}" href="{!! route('admin.reports.realtime-availability') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.reports.realtime_report') }}
                                </p>
                            </a>
                        </li>
                        @endif
                        @endif
                    </ul>
                </li>
                @endauth

                {{-- block for ZCA only for booking report --}}
                @if(access()->allow('booking-report') && !empty($company) &&  $role->group == 'company' && getCompanyPlanAccess($user, 'event') && access()->allow('view-user-registrations'))
                <li class="nav-item has-treeview {{ request()->is(app()->getLocale().'/admin/reports/booking-report*') || (request()->is(app()->getLocale().'/admin/moodAnalysis*') && $role->group == 'company') || request()->is(app()->getLocale().'/admin/reports/user-registration*')? 'menu-open' : '' }}">
                    <a class="nav-link" href="javascript:void(0);">
                        <i class="fal fa-file-chart-line menu-icon nav-icon">
                        </i>
                        <p>
                            {{ trans('layout.sidebar.reports.title') }}
                            <i class="far fa-angle-down treeview-arrow">
                            </i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/reports/booking-report') ? 'active' : '' }}" href="{{ route('admin.reports.booking-report') }}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.reports.bookings_report') }}
                                </p>
                            </a>
                        </li>
                        @if (access()->allow('view-user-registrations'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/reports/user-registration') ? 'active' : '' }}" href="{!! route('admin.reports.user-registration') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.reports.user_registration') }}
                                </p>
                            </a>
                        </li>
                        @endif

                        @if($role->group == 'company')
                        @permission('view-moods-analysis')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/moodAnalysis*') ? 'active' : '' }}" href="{!! route('admin.moodAnalysis.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.moods.moods_analysis') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                        @endif
                    </ul>
                </li>
                @endif
                {{-- end --}}

                @if(false)
                @if($role->group == 'zevo' || $role->group == 'company' || ($role->group == 'reseller' && $company->parent_id != null && $company->allow_app == true))
                <?php if (access()->
                allow('survey-questioners') || access()->allow('view-wellbeing-survey-board') ): ?>
                <li class="nav-item has-treeview {{ request()->is(app()->getLocale().'/admin/questions*') || request()->is(app()->getLocale().'/admin/wellbeing-survey-board') ? 'menu-open' : '' }}">
                    <a class="nav-link" href="javascript:void(0);">
                        <i class="fal fa-sliders-h-square menu-icon nav-icon">
                        </i>
                        <p>
                            {{ trans('layout.sidebar.wellbeing_survey.title') }}
                            <i class="far fa-angle-down treeview-arrow">
                            </i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @if($role->group == 'zevo')
                        @permission('survey-questioners')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/questions*') ? 'active' : '' }}" href="{!! route('admin.questions.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.wellbeing_survey.survey_questions') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                        @endif
                        @if($role->group == 'zevo' || $role->group == 'company' || ($role->group == 'reseller' && $company->parent_id != null && $company->allow_app == true))
                        @permission('view-wellbeing-survey-board')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/wellbeing-survey-board') ? 'active' : '' }}" href="{!! route('admin.wellbeingSurveyBoard.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.wellbeing_survey.survey_board') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                        @endif
                    </ul>
                </li>
                @endauth
                @endif
                @endif
                <?php if (access()->
                allow('manage-survey-category') || access()->allow('manage-question-bank') || access()->allow('manage-survey') || access()->allow('review-suggestion') || access()->allow('survey-insights') || access()->allow('hr-report')): ?>
                @if($role->group == 'zevo' || ($role->group == 'company' && getCompanyPlanAccess($user, 'wellbeing-score-card')) || ($role->group == 'reseller' && getDTAccessForParentsChildCompany($user, 'wellbeing-scorecard')))
                <li class="nav-item has-treeview {{ ((request()->is(app()->getLocale().'/admin/surveycategories*') || request()->is(app()->getLocale().'/admin/surveysubcategories*') || request()->is(app()->getLocale().'/admin/zcquestionbank*') || request()->is(app()->getLocale().'/admin/zcsurvey*') || request()->is(app()->getLocale().'/admin/review-suggestion*') || request()->is(app()->getLocale().'/admin/survey-insights*') || request()->is(app()->getLocale().'/admin/hr-report*')) ? 'menu-open' : '') }}">
                    <a class="nav-link" href="javascript:void(0);">
                        <i class="fal fa-clipboard menu-icon nav-icon">
                        </i>
                        <p>
                            {{ trans('layout.sidebar.surveys.title') }}
                            <i class="far fa-angle-down treeview-arrow">
                            </i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @if($role->group == 'zevo')
                        @permission('manage-survey-category')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/surveycategories*') || request()->is(app()->getLocale().'/admin/surveysubcategories*') ? 'active' : '' }}" href="{!! route('admin.surveycategories.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.surveys.categories') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                        @permission('manage-question-bank')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/zcquestionbank*') ? 'active' : '' }}" href="{!! route('admin.zcquestionbank.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.surveys.question_library') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                        @permission('manage-survey')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/zcsurvey*') ? 'active' : '' }}" href="{!! route('admin.zcsurvey.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.surveys.surveys') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                        @endif
                        @permission('review-suggestion')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/review-suggestion*') ? 'active' : '' }}" href="{!! route('admin.reviewSuggestion.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.surveys.feedback') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                        @permission('survey-insights')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/survey-insights*') ? 'active' : '' }}" href="{!! route('admin.surveyInsights.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.surveys.insights') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                        @permission('hr-report')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/hr-report*') ? 'active' : '' }}" href="{!! route('admin.hrReport.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.surveys.hr_report') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                    </ul>
                </li>
                @endif
                @endauth
                @if(!empty($userRole))
                @if($role->group == 'zevo' || $role->group == 'company')
                @permission('manage-project-survey')
                <li class="nav-item has-treeview {{ request()->is(app()->getLocale().'/admin/reports/nps*') ? 'menu-open' : '' }} {{ request()->is(app()->getLocale().'/admin/projectsurvey*') ? 'menu-open' : '' }}">
                    <a class="nav-link" href="javascript:void(0);">
                        <i class="fal fa-file-chart-line menu-icon nav-icon">
                        </i>
                        <p>
                            {{ trans('layout.sidebar.reports.customer_satisfaction') }}
                            <i class="far fa-angle-down treeview-arrow">
                            </i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/reports/nps') ? 'active' : '' }} {{ request()->is(app()->getLocale().'/admin/projectsurvey*') ? 'active' : '' }}" href="{!! route('admin.reports.nps') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.reports.project_survey') }}
                                </p>
                            </a>
                        </li>
                    </ul>
                </li>
                @endauth
                @endif
                @endif
                @if (access()->allow('event-management'))
                @if(($role->group == 'zevo' || ($role->group == 'company' && getCompanyPlanAccess($user, 'event')) || ($role->group == 'reseller' && getDTAccessForParentsChildCompany($user, 'event'))) && $role->slug != 'wellbeing_specialist')
                <li class="nav-item has-treeview {{ ((request()->is(app()->getLocale().'/admin/event*') || (request()->is(app()->getLocale().'/admin/marketplace*'))) || request()->is(app()->getLocale().'/admin/bookings*') ? 'menu-open' : '') }}">
                    <a class="nav-link" href="javascript:void(0);">
                        <i class="fal fa-calendar-star menu-icon nav-icon">
                        </i>
                        <p>
                            {{ trans('layout.sidebar.events.title') }}
                            <i class="far fa-angle-down treeview-arrow">
                            </i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @permission('event-management')
                        <li class="nav-item">
                            <a class="nav-link {{ (request()->is(app()->getLocale().'/admin/event*') ? 'active' : '') }}" href="{{ route('admin.event.index') }}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.events.title') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                        @permission('market-place-list')
                        <li class="nav-item">
                            <a class="nav-link {{ (request()->is(app()->getLocale().'/admin/marketplace*') ? 'active' : '') }}" href="{{ route('admin.marketplace.index') }}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.events.marketplace') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                        @permission('bookings')
                        <li class="nav-item">
                            <a class="nav-link {{ (request()->is(app()->getLocale().'/admin/booking*') ? 'active' : '') }}" href="{{ route('admin.bookings.index') }}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.events.bookings') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                    </ul>
                </li>
                @endif
                @endauth
                @if($role->group == 'zevo')
                @if(access()->allow('users-import') || access()->allow('questions-import'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->is(app()->getLocale().'/admin/imports*') ? 'active' : '' }}" href="{!! route('admin.imports.index') !!}">
                        <i class="far fa-file-import menu-icon nav-icon">
                        </i>
                        <p>
                            {{ trans('layout.sidebar.imports') }}
                        </p>
                    </a>
                </li>
                @endauth
                @endif
                <!----Digital therapy menu with client, sessions and services------>
                @if($role->slug != 'counsellor' && $role->slug != 'wellbeing_specialist' && $role->slug != 'wellbeing_team_lead' && $role->group != 'company' && $role->group != 'reseller')
                <?php if (access()->
                allow('manage-clients') || access()->allow('manage-sessions') || access()->allow('manage-services') || access()->allow('manage-service-subcategories') ): ?>
                <li class="nav-item has-treeview {{ ((request()->is(app()->getLocale().'/admin/cronofy/clientlist*') || (request()->is(app()->getLocale().'/admin/cronofy/sessions*') || (request()->is(app()->getLocale().'/admin/services*')))) ? 'menu-open' : '') }}">
                    <a class="nav-link" href="javascript:void(0);">
                        <i class="fal fa-calendar-check menu-icon nav-icon">
                        </i>
                        <p>
                            {{ trans('layout.sidebar.digital_therapy.title') }}
                            <i class="far fa-angle-down treeview-arrow">
                            </i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @if (access()->allow('manage-services') || access()->allow('manage-service-subcategories'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/services*') ? 'active' : '' }}" href="{{ route('admin.services.index') }}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.services.title') }}
                                </p>
                            </a>
                        </li>
                        @endif

                        @permission('manage-clients')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/cronofy/clientlist*') ? 'active' : '' }}" href="{!! route('admin.cronofy.clientlist.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.digital_therapy.client_list') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                        @permission('manage-sessions')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/cronofy/sessions*') ? 'active' : '' }}" href="{!! route('admin.cronofy.sessions.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.digital_therapy.sessions') }}
                                </p>
                            </a>
                        </li>
                        @endauth
                        @permission('manage-consent-form')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/cronofy/consent-form*') ? 'active' : '' }}" href="{!! route('admin.cronofy.consent-form.index') !!}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.digital_therapy.consent_form') }}
                                </p>
                            </a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif
                @endif
                @if($role->slug == 'counsellor')
                @if(access()->allow('manage-clients'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->is(app()->getLocale().'/admin/client-list*') ? 'active' : '' }}" href="{!! route('admin.clientlist.index') !!}">
                        <i class="far fa-address-book menu-icon nav-icon">
                        </i>
                        <p>
                            {{ trans('layout.sidebar.eap.client_list') }}
                        </p>
                    </a>
                </li>
                @endauth
                @if(access()->allow('manage-sessions'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->is(app()->getLocale().'/admin/sessions*') ? 'active' : '' }}" href="{!! route('admin.sessions.index') !!}">
                        <i class="fal fa-calendar-check menu-icon nav-icon">
                        </i>
                        <p>
                            {{ trans('layout.sidebar.eap.calendly') }}
                        </p>
                    </a>
                </li>
                @endauth
                @endif
                <!-- Services menu -->
                @if($role->group == 'zevo')
                
                @if(access()->allow('my-profile') || access()->allow('authenticate') || access()->allow('availability'))
                @if(!empty($wsDetails) && $wsDetails->is_cronofy || !empty($wcDetails) && $wcDetails->is_cronofy)
                <li class="nav-item has-treeview {{ ((request()->is(app()->getLocale().'/admin/users/editProfile*') || (request()->is(app()->getLocale().'/admin/cronofy')) || (request()->is(app()->getLocale().'/admin/cronofy/linkCalendar')) || (request()->is(app()->getLocale().'/admin/cronofy/availability'))) ? 'menu-open' : '') }}">
                    <a class="nav-link " href="javascript:void(0);">
                        <i class="fal fa-id-card-alt menu-icon nav-icon">
                        </i>
                        <p>
                            {{ trans('layout.sidebar.profile.title') }}
                            <i class="far fa-angle-down treeview-arrow">
                            </i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        @if(access()->allow('my-profile'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/users/editProfile*') ? 'active' : '' }}" href="{{ route('admin.users.editProfile') }}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.profile.my-profile') }}
                                </p>
                            </a>
                        </li>
                        @endif
                        @if(access()->allow('authenticate'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/cronofy/index*') ? 'active' : '' }}" href="{{ route('admin.cronofy.index') }}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.profile.authenticate') }}
                                </p>
                            </a>
                        </li>
                        @endif
                        @if(access()->allow('availability'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is(app()->getLocale().'/admin/cronofy/availability*') ? 'active' : '' }}" href="{{ route('admin.cronofy.availability') }}">
                                <i class="fal fa-minus nav-icon">
                                </i>
                                <p>
                                    {{ trans('layout.sidebar.profile.availability') }}
                                </p>
                            </a>
                        </li>
                        @endif

                    </ul>
                </li>
                @if ( $role->slug != 'health_coach' && !empty($wsDetails) && $wsDetails->is_cronofy && $wsDetails->responsibilities != 2)
                <li class="nav-item">
                    <a class="nav-link {{ request()->is(app()->getLocale().'/admin/cronofy/clientlist*') ? 'active' : '' }}" href="{!! route('admin.cronofy.clientlist.index') !!}">
                        <i class="far fa-address-book menu-icon nav-icon">
                        </i>
                        <p>
                            {{ trans('layout.sidebar.digital_therapy.client_list') }}
                        </p>
                    </a>
                </li>
                @elseif ( $role->slug == 'health_coach' )
                <li class="nav-item">
                    <a class="nav-link {{ (request()->is(app()->getLocale().'/admin/booking*') ? 'active' : '') }}" href="{{ route('admin.bookings.index') }}">
                        <i class="fal fa-calendar-star menu-icon nav-icon">
                        </i>
                        <p>
                            {{ trans('layout.sidebar.events.bookings') }}
                        </p>
                    </a>
                </li>
                @endif
                @endif
                @endauth
                @endif
                @if((!empty($wsDetails) && $wsDetails->is_cronofy && $wsDetails->responsibilities != 2 ) ||  (!empty($wcDetails) && $wcDetails->is_cronofy) || ($role->group == 'company' && getCompanyPlanAccess($user, 'eap')) || ($role->group == 'reseller' && !is_null($company->parent_id) && getCompanyPlanAccess($user, 'digital-therapy') && getDTAccessForParentsChildCompany($user, 'digital-therapy')) || ($role->group == 'reseller' && is_null($company->parent_id) && getDTAccessForParentsChildCompany($user, 'digital-therapy')))
                @if(access()->allow('manage-sessions'))
                <li class="nav-item">
                    <a class="nav-link {{ request()->is(app()->getLocale().'/admin/cronofy/sessions*') ? 'menuactive' : '' }}" href="{!! route('admin.cronofy.sessions.index') !!}">
                        <i class="fal fa-calendar-check menu-icon nav-icon">
                        </i>
                        <p>
                            {{ trans('layout.sidebar.digital_therapy.sessions') }}
                        </p>
                    </a>
                </li>
                @endif
                @endauth
                @if(!empty($wsDetails) && $wsDetails->is_cronofy && $wsDetails->responsibilities != 1)
                @permission('bookings')
                    <li class="nav-item">
                        <a class="nav-link {{ (request()->is(app()->getLocale().'/admin/booking*') ? 'active' : '') }}" href="{{ route('admin.bookings.index') }}">
                        <i class="fal fa-calendar-star menu-icon nav-icon">
                            </i>
                            <p>
                                {{ trans('layout.sidebar.events.title') }}
                            </p>
                        </a>
                    </li>
                @endauth
                @endif

            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>