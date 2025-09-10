<?php

return [

    /*
    |--------------------------------------------------------------------------
    | App version management
    |--------------------------------------------------------------------------
    |
     */

    'version'                                  => [
        'andriod_version'      => env('ANDRIOD_VERSION', '1000000'),
        'andriod_force_update' => env('ANDRIOD_FORCE_UPDATE', 0),
        'ios_version'          => env('IOS_VERSION', '1.0.1'),
        'ios_force_update'     => env('IOS_FORCE_UPDATE', 0),
        'api_version'          => 'v43',
    ],

    /*
    |--------------------------------------------------------------------------
    | Emails
    |--------------------------------------------------------------------------
    |
     */

    'emails'                                   => [
        'test@yopmail.com',
    ],

    /*
    |--------------------------------------------------------------------------
    | Expertise Levels
    |--------------------------------------------------------------------------
    |
     */

    'expertise_levels'                         => [
        'beginner'     => 'Beginner',
        'intermediate' => 'Intermediate',
        'expert'       => 'Expert',
    ],

    /*
    |--------------------------------------------------------------------------
    | Datatable Pagination
    |--------------------------------------------------------------------------
    |
     */

    'datatable'                                => [
        'pagination' => [
            'short'             => env('DATATABLE_SHORT_PAGINATION', 10),
            'long'              => env('DATATABLE_LONG_PAGINATION', 25),
            'portal'            => env('DATATABLE_LONG_PAGINATION', 12),
            'clientAttachments' => 5,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Role Groups
    |--------------------------------------------------------------------------
    |
     */

    'role_group'                               => [
        'zevo'     => 'zevo',
        'company'  => 'company',
        'reseller' => 'reseller',
    ],

    /*
    |--------------------------------------------------------------------------
    | APP Settings
    |--------------------------------------------------------------------------
    |
     */

    'app_settings'                             => [
        'privacy_url'      => ['display' => 'Privacy Url', 'type' => 'text', 'validation' => 'required|url|max:150'],
        'terms_url'        => ['display' => 'Terms Url', 'type' => 'text', 'validation' => 'required|url|max:150'],
        'splash_image_url' => ['display' => 'Splash Image', 'type' => 'file', 'validation' => 'sometimes|nullable|image|mimes:jpg,jpeg,png|max:2048'],
        'splash_message'   => ['display' => 'Splash Message', 'type' => 'text', 'validation' => 'required|max:200'],
        'app_theme'        => ['display' => 'App Theme', 'type' => 'list', 'validation' => 'required'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Company Size
    |--------------------------------------------------------------------------
    |
     */

    'company_size'                             => [
        '0-50'      => '0-50',
        '51-100'    => '51-100',
        '101-250'   => '101-250',
        '251-500'   => '251-500',
        '501-1000'  => '501-1000',
        '1001-2500' => '1001-2500',
        '2501-5000' => '2501-5000',
        '5000+'     => '5000+',
    ],

    /*
    |--------------------------------------------------------------------------
    | Group restriction rules
    |--------------------------------------------------------------------------
    |
     */
    'group_restriction_rules'                  => [
        1 => 'Department',
        2 => 'Team',
    ],

    /*
    |--------------------------------------------------------------------------
    | Expertise UOM Types
    |--------------------------------------------------------------------------
    |
     */

    'exercise_type'                            => [
        'meter'   => 'Meter',
        'minutes' => 'Minutes',
        'both'    => 'Both',
    ],

    /*
    |--------------------------------------------------------------------------
    | Label Tags
    |--------------------------------------------------------------------------
    |
     */

    'label_tags'                               => [
        'move'    => 'Move',
        'nourish' => 'Nourish',
        'inspire' => 'Inspire',
    ],

    'image_mimetypes'                          => [
        'image/jpeg',
        'image/jpg',
        'image/png',
    ],

    'video_mimetypes'                          => [
        'video/mp4',
    ],

    'audio_mimetypes'                          => [
        'audio/mp3',
        'audio/mpeg',
    ],

    'recurring_type'                           => [
        'weekly'   => 'Weekly',
        'biweekly' => 'Bi-Weekly',
        'monthly'  => 'Monthly',
    ],

    'recurring_day_value'                      => [
        'weekly'   => 7,
        'biweekly' => 14,
        'monthly'  => 30,
    ],

    'uom'                                      => [
        'Steps'       => array('count' => 'Count'),
        'Distance'    => array('meter' => 'Meter'),
        //'Calories'    => array('kcal' => 'Kcal'),
        'Exercises'   => array('meter' => 'Meter', 'minutes' => 'Minutes'),
        'Meditations' => array('count' => 'Count'),
        'Content'     => array('points' => 'Count (Points)'),
    ],

    'target_uom'                               => [
        //"calories"    => "Kcal",
        "distance"    => "Meter",
        "meditations" => "Count",
        "steps"       => "Count",
        "exercises"   => array("Meter", "Minutes"),
        'content'     => "Count",
    ],

    // 'default_limits'             => [
    //     "steps"              => "10000",
    //     "distance"           => "10000",
    //     //"calories"           => "500",
    //     "exercises_distance" => "10000",
    //     "exercises_duration" => "60",
    //     "meditations"        => "60",
    // ],

    'default_limits'                           => [
        "steps"                       => "1000",
        "distance"                    => "1000",
        "exercises_distance"          => "1000",
        "exercises_duration"          => "15",
        "meditations"                 => "15",
        "daily_meditation_limit"      => 10,
        "daily_track_limit"           => 5,
        "content"                     => 1,
        "daily_webinar_limit"         => 3,
        "guided_meditation_limit"     => 10,
        "recent_meditation_limit"     => 10,
        "most_liked_meditation_limit" => 10,
        "most_liked_webinar_limit"    => 10,
        "recent_webinar_limit"        => 10,
        "daily_podcast_limit"         => 10,
    ],

    'default_limits_message'                   => [
        "steps"                  => "Steps equal to one point",
        "distance"               => "Meters equal to one point",
        "exercises_distance"     => "Exercise meters equal to one point",
        "exercises_duration"     => "Exercise minutes equal to one point",
        "meditations"            => "Meditations equal to one point",
        "daily_meditation_limit" => "Meditations that users earn points for in a day",
        "daily_track_limit"      => "Single track limit that users earn points for in a day",
    ],

    'daily_meditation_limits'                  => 10,

    'badgeTypes'                               => [
        'challenge'   => 'Challenge',
        'general'     => 'General',
        'masterclass' => 'Masterclass',
        'daily'       => 'Daily Target',
        'ongoing'     => 'Ongoing Challenge',
        // 'course'    => 'Course',
    ],

    // Notification settings module
    'notificationModules'                      => [
        'badges'          => true,
        'challenges'      => true,
        'courses'         => false,
        'datasync'        => true,
        'feeds'           => false,
        'general'         => true,
        'groups'          => true,
        'meditations'     => false,
        'nps'             => true,
        'recipes'         => false,
        'moods'           => true,
        'webinars'        => true,
        'events'          => true,
        'digital-therapy' => true,
        'shorts'          => true,
    ],

    'courseBadgeTitle'                         => 'masterclass completed',

    'deeplink_uri'                             => [
        'nps'              => 'zevolife://zevo/nps',
        'self-profile'     => 'zevolife://zevo/profile',
        'audit-survey'     => 'zevolife://zevo/audit-survey/:survey_log_id',
        'badge'            => 'zevolife://zevo/badge/:badge_id',
        'masterclass_csat' => 'zevolife://zevo/nps/masterclass/:id',
        'event_csat'       => 'zevolife://zevo/nps/event/:id',
        'group'            => 'zevolife://zevo/group/:id',
        'eap_completed'    => 'zevolife://zevo/nps/session/:id',
        'consent_form'     => 'zevolife://zevo/dt-sessions-consent/:id/:type',
        'group_invite'     => 'zevolife://zevo/group-invite/:id',
    ],

    'challenge_rule_description'               => [
        'individual'   => [
            1 => 'The winner of the challenge will be the person who achieves the target before the challenge completion date. They must first sync their tracker.',
            2 => 'The winner of the challenge will be the person with the highest points total and is top of the leaderboard.',
            3 => 'Any person who completes the target consistently on a daily basis will be declared as a winner once challenge the completes.',
            4 => 'The winner of the challenge will be the person who reaches both targets and gets max points. They must first sync their tracker.',
            5 => 'The winner of the challenge will be the person who reaches the target in the minimum amount of time during the challenge. All users must sync before the challenge completes.',
        ],
        'team'         => [
            1 => 'The winner of the challenge will be the team who achieves the target before the challenge completion date. They must first sync their tracker.',
            2 => 'The winner of the challenge will be the team with the highest points total and  is top of the leaderboard.',
            4 => 'The winner of the challenge will be the team who reaches both targets and gets max points. They must first sync their tracker.',
        ],
        'company_goal' => [
            1 => 'The challenge will be successfully completed when the target is reached by all teams working together.',
            3 => 'The challenge will be successfully completed if all teams complete the streak.',
            4 => 'The challenge will be successfully completed when both targets are reached.',
            5 => 'The winner of the challenge will be the person who reaches the target in the minimum amount of time during the challenge. All users must sync before the challenge completes.',
        ],
    ],

    'nutritions'                               => [
        1 => [
            'display_name' => 'Energy (KCal)',
            'name'         => 'nutritions[energy]',
        ],
        2 => [
            'display_name' => 'Fat (gm)',
            'name'         => 'nutritions[fat]',
        ],
        3 => [
            'display_name' => 'Carbohydrate (gm)',
            'name'         => 'nutritions[carbohydrate]',
        ],
        4 => [
            'display_name' => 'Protein (gm)',
            'name'         => 'nutritions[protein]',
        ],
        5 => [
            'display_name' => 'Salt (gm)',
            'name'         => 'nutritions[salt]',
        ],
        6 => [
            'display_name' => 'Fiber (gm)',
            'name'         => 'nutritions[fiber]',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Company wise App Settings
    |--------------------------------------------------------------------------
    |
     */
    'company_wise_app_settings'                => [
        'logo_image_url'   => ['display' => 'Logo Image', 'type' => 'file', 'validation' => 'sometimes|nullable|image|mimes:jpg,jpeg,png|max:2048'],
        'splash_image_url' => ['display' => 'Splash Image', 'type' => 'file', 'validation' => 'sometimes|nullable|image|mimes:jpg,jpeg,png|max:2048'],
        'splash_message'   => ['display' => 'Splash Message', 'type' => 'text', 'validation' => 'sometimes|max:200'],
        'app_theme'        => ['display' => 'App Theme', 'type' => 'list', 'validation' => 'required'],
    ],

    'age'                                      => [
        '18_24'  => '18 - 24',
        '25_34'  => '25 - 34',
        '35_44'  => '35 - 44',
        '45_54'  => '45 - 54',
        '55_900' => 'More than 55',
    ],

    'challengeTypes'                           => [
        'individual'    => 'Individual',
        'team'          => 'Team',
        'company_goal'  => 'Company Goal',
        'inter_company' => 'Inter-company',
    ],

    'default_fallback_image_url'               => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/onboard1.png',

    'static_image'                             => [
        'user'               => [
            'super_admin' => env('DO_SPACES_DOMAIN') . '/static_media/avatars/zevo_sa.jpeg',
        ],
        'notification_icons' => [
            'badge'           => 'static_media/notification_icons/badge.png',
            'challenge'       => 'static_media/notification_icons/challenge.png',
            'feed'            => 'static_media/notification_icons/feed.png',
            'feed_audio'      => 'static_media/notification_icons/feed-audio.png',
            'feed_video'      => 'static_media/notification_icons/feed-video.png',
            'feed_youtube'    => 'static_media/notification_icons/feed-video.png',
            'feed_vimeo'      => 'static_media/notification_icons/feed-video.png',
            'general'         => 'static_media/notification_icons/general.png',
            'group'           => 'static_media/notification_icons/group.png',
            'broadcast'       => 'static_media/notification_icons/group.png',
            'masterclass'     => 'static_media/notification_icons/masterclass.png',
            'meditation'      => 'static_media/notification_icons/meditation.png',
            'mood'            => 'static_media/notification_icons/mood.png',
            'recipe'          => 'static_media/notification_icons/recipe.png',
            'sync'            => 'static_media/notification_icons/sync.png',
            'survey'          => 'static_media/notification_icons/survey.png',
            'audit-survey'    => 'static_media/notification_icons/survey.png',
            'team'            => 'static_media/notification_icons/team.png',
            'user'            => 'static_media/notification_icons/user.png',
            'eap'             => 'static_media/notification_icons/eap.png',
            'event'           => 'static_media/notification_icons/event.png',
            'webinar'         => 'static_media/notification_icons/webinar.png',
            'new-eap'         => 'static_media/notification_icons/event.png',
            'digital-therapy' => 'static_media/notification_icons/eap.png',
        ],
        'nps_emoji'          => [
            'very_happy'   => 'static_media/nps_emoji/very_happy.png',
            'happy'        => 'static_media/nps_emoji/happy.png',
            'neutral'      => 'static_media/nps_emoji/neutral.png',
            'unhappy'      => 'static_media/nps_emoji/unhappy.png',
            'very_unhappy' => 'static_media/nps_emoji/very_unhappy.png',
        ],
        'group_icons'        => [
            'challenge'   => 'static_media/group_icons/challenge.png',
            'masterclass' => 'static_media/group_icons/masterclass.png',
            'standard'    => 'static_media/group_icons/standard.png',
        ],
    ],

    'fallback_image_url'                       => [
        'user'                      => [
            'logo'             => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/user1-128x128.jpg',
            'coverImage'       => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/no-cover.png',
            'counsellor_cover' => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/no-cover.png',
            'user-male1'       => env('DO_SPACES_DOMAIN') . '/static_media/avatars/zevo_male1.png',
            'user-female1'     => env('DO_SPACES_DOMAIN') . '/static_media/avatars/zevo_female1.png',
            'user-other1'      => env('DO_SPACES_DOMAIN') . '/static_media/avatars/zevo_other1.png',
            'user-none1'       => env('DO_SPACES_DOMAIN') . '/static_media/avatars/zevo_none1.png',
            'user-male2'       => env('DO_SPACES_DOMAIN') . '/static_media/avatars/zevo_male2.png',
            'user-female2'     => env('DO_SPACES_DOMAIN') . '/static_media/avatars/zevo_female2.png',
            'user-other2'      => env('DO_SPACES_DOMAIN') . '/static_media/avatars/zevo_other2.png',
            'user-none2'       => env('DO_SPACES_DOMAIN') . '/static_media/avatars/zevo_none2.png',
            'user-male3'       => env('DO_SPACES_DOMAIN') . '/static_media/avatars/zevo_male3.png',
            'user-female3'     => env('DO_SPACES_DOMAIN') . '/static_media/avatars/zevo_female3.png',
            'user-other3'      => env('DO_SPACES_DOMAIN') . '/static_media/avatars/zevo_other3.png',
            'user-none3'       => env('DO_SPACES_DOMAIN') . '/static_media/avatars/zevo_none3.png',
            'user-male4'       => env('DO_SPACES_DOMAIN') . '/static_media/avatars/zevo_male4.png',
            'user-female4'     => env('DO_SPACES_DOMAIN') . '/static_media/avatars/zevo_female4.png',
            'user-other4'      => env('DO_SPACES_DOMAIN') . '/static_media/avatars/zevo_other4.png',
            'user-none4'       => env('DO_SPACES_DOMAIN') . '/static_media/avatars/zevo_none4.png',
        ],
        'category'                  => [
            'logo' => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/onboard_large.png',
        ],
        'company'                   => [
            'logo'                       => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/onboard_large.png',
            'branding_logo'              => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/full-logo.png',
            'branding_login_background'  => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/web-login.jpg',
            'location_logo'              => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/onboard_location.png',
            'department_logo'            => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/onboard_department.png',
            'email_header'               => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/onboard_department.png',
            'portal_logo_main'           => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/portal_logo_main.svg',
            'portal_logo_optional'       => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/portal_logo_optional.svg',
            'portal_background_image'    => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/portal_background_image.png',
            'portal_footer_logo'         => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/portal_footer_logo.svg',
            'zevo_favicon_icon'          => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/zevo_favicon.png',
            'prod_favicon_icon'          => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/prod_favicon.png',
            'portal_homepage_logo_left'  => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/portal_logo_main.svg',
            'portal_homepage_logo_right' => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/portal_logo_optional.svg',
            'contact_us_image_local'     => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/contact_us_image_local.png',
            'contact_us_image_tiktok'    => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/contact_us_image_tiktok.png',
            'appointment_image_local'    => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/appointment_default_local.png',
            'appointment_image_tiktok'   => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/appointment_default_tiktok.png',
        ],
        'team'                      => [
            'logo' => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/onboard_large.png',
        ],
        'meditation_category'       => [
            'logo' => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/onboard_large.png',
        ],
        'meditation_tracks'         => [
            'cover'             => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/no-cover.png',
            'background'        => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/no-cover.png',
            'background_portal' => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/no-cover.png',
            'header_image'      => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/no-cover.png',
            'default_image'     => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/default-meditation.png',
        ],
        'user_exercise'             => [
            'logo' => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/onboard_large.png',
        ],
        'exercise'                  => [
            'logo'       => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/onboard_large.png',
            'background' => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/no-cover.png',
        ],
        'badge'                     => [
            'logo'             => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/onboard_large.png',
            'masterclass_logo' => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/masterclass_badge_logo.png',
        ],
        'course'                    => [
            'logo'              => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/onboard_large.png',
            'inrtoduction_logo' => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/onboard_large.png',
            'header_image'      => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/no-cover.png',
            'default_image'     => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/default-course.png',
        ],
        'course_lession'            => [
            'logo'    => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/onboard_large.png',
            'video'   => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/no-cover.png',
            'youtube' => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/no-cover.png',
            'default' => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/default-lession.png',
        ],
        'course_survey_question'    => [
            'logo' => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/onboard_large.png',
        ],
        'feed'                      => [
            'featured_image'   => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/onboard_large.png',
            'audio_background' => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/onboard_large.png',
            'video'            => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/no-cover.png',
            'youtube'          => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/no-cover.png',
            'recently_added'   => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/recently-added.png',
            'most_popular'     => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/most-liked.png',
        ],
        'recipe'                    => [
            'logo'          => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/onboard_large.png',
            'header_image'  => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/no-cover.png',
            'default_image' => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/default-recipe.png',
        ],
        'group'                     => [
            'logo' => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/onboard_large.png',
        ],
        'groupMessage'              => [
            'logo' => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/onboard_large.png',
        ],
        'challenge'                 => [
            'logo' => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/onboard_large.png',
        ],
        'app_setting'               => [
            'logo_image_url'   => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/Zevo_Logo-dark-theme.png',
            'splash_image_url' => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/background.png',
        ],
        'company_wise_app_settings' => [
            'logo_image_url'   => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/zevo_logo_splash.gif',
            'splash_image_url' => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/background.png',
        ],
        'app_slide'                 => [
            'slideImage' => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/onboard_large.png',
        ],
        'user_notification'         => [
            'logo' => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/onboard_large.png',
        ],
        'notification'              => [
            'logo' => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/onboard_large.png',
        ],
        'personalChallenge'         => [
            'logo' => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/onboard_large.png',
        ],
        'eap'                       => [
            'logo' => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/onboard_large.png',
        ],
        'moods'                     => [
            'logo' => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/onboard_large.png',
        ],
        'moodTags'                  => [
            'logo' => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/onboard_large.png',
        ],
        'question'                  => [
            'logo' => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/73a90acaae2b1ccc0e969709665bc62f.png',
        ],
        'questionoption'            => [
            'logo' => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/73a90acaae2b1ccc0e969709665bc62f.png',
        ],
        'nps'                       => [
            'logo' => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/onboard_large.png',
        ],
        'goals'                     => [
            'logo' => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/onboard_large.png',
        ],
        'challenge_library'         => [
            'image' => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/onboard_large.png',
        ],
        'webinar'                   => [
            'cover'         => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/onboard_large.png',
            'background'    => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/no-cover.png',
            'header_image'  => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/no-cover.png',
            'default_image' => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/default-webinar.png',
        ],
        'surveycategory'            => [
            'logo' => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/onboard_large.png',
        ],
        'event'                     => [
            'logo' => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/onboard_large.png',
        ],
        'label_settings'            => [
            'location_logo'   => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/onboard_location.png',
            'department_logo' => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/onboard_department.png',
        ],
        'sub_category'              => [
            //'favorite' => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/favorite.png',
            'view_all' => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/meditation_view_all.png',
            'favorite' => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/my_star_background.png',
        ],
        'services'                  => [
            'counselling'   => [
                'logo' => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/counselling_service.jpeg',
                'icon' => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/counselling_icon.png',
            ],
            'coaching'      => [
                'logo' => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/coaching_service.jpeg',
                'icon' => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/coaching_icon.png',
            ],
            'subcategories' => [
                'counselling' => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/counselling-default-logo.jpeg',
                'coaching'    => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/coaching-default-logo.jpeg',
                'other'       => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/default-subcategory-logo.jpeg',
            ],
        ],
        'cronofy-availability'      => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/dt-error.png',
    ],

    // file size are in kilobyte, here 1MB = 1024 Kilobyte
    'fileSizeValidations'                      => [
        'user'                      => [
            'logo'             => 2048,
            'cover'            => 2048,
            'counsellor_cover' => 2048,
        ],
        'category'                  => [
            'logo' => 2048,
        ],
        'company'                   => [
            'logo'                      => 2048,
            'branding_logo'             => 2048,
            'branding_login_background' => (5 * 1024),
            'email_header'              => 2048,
            'portal_footer_text'        => 2048,
            'portal_favicon_icon'       => 2048,
            'contact_us_image'          => 2048,
            'banner_image'              => 2048,
            'appointment_image'         => 2048,
        ],
        'team'                      => [
            'logo' => 2048,
        ],
        'meditation_category'       => [
            'logo' => 2048,
        ],
        'meditation_tracks'         => [
            'cover'             => 2048,
            'background'        => 2048,
            'background_portal' => 2048,
            'track'             => (100 * 1024),
            'header_image'      => 2048,
        ],
        'exercise'                  => [
            'logo'       => 2048,
            'background' => 2048,
        ],
        'user_exercise'             => [
            'logo' => 2048,
        ],
        'badge'                     => [
            'logo' => 2048,
        ],
        'course'                    => [
            'logo'                     => 2048,
            'trailer_audio'            => (100 * 1024),
            'trailer_audio_background' => 2048,
            'trailer_video'            => (100 * 1024),
            'header_image'             => 2048,
        ],
        'course_lession'            => [
            'audio'                   => (100 * 1024),
            'audio_background'        => 2048,
            'audio_background_portal' => 2048,
            'video'                   => (100 * 1024),
            'mix_content'             => (100 * 1024),
            'logo'                    => 2048,
        ],
        'course_survey_question'    => [
            'logo' => 2048,
        ],
        'feed'                      => [
            'featured_image'          => 2048,
            'audio'                   => (100 * 1024),
            'audio_background'        => 2048,
            'audio_background_portal' => 2048,
            'header_image'            => 2048,
            'video'                   => (100 * 1024),
            'mix_content'             => (100 * 1024),
        ],
        'recipe'                    => [
            'logo'         => (5 * 1024),
            'header_image' => 2048,
        ],
        'group'                     => [
            'logo' => 2048,
        ],
        'challenge'                 => [
            'logo' => 2048,
        ],
        'app_setting'               => [
            'splash_image_url' => 2048,
        ],
        'company_wise_app_settings' => [
            'logo_image_url'   => 2048,
            'splash_image_url' => 2048,
        ],
        'app_slide'                 => [
            'slideImage'       => 2048,
            'slideImagePortal' => 2048,
        ],
        'user_notification'         => [
            'logo' => 2048,
        ],
        'personalChallenge'         => [
            'logo' => (5 * 1024),
        ],
        'eap'                       => [
            'logo' => 2048,
        ],
        'moods'                     => [
            'logo' => 2048,
        ],
        'moodTags'                  => [
            'logo' => 2048,
        ],
        'question'                  => [
            'logo' => 2048,
        ],
        'questionoption'            => [
            'logo' => 2048,
        ],
        'goalTags'                  => [
            'logo' => 2048,
        ],
        'challenge_library'         => [
            'image' => 2048,
        ],
        'webinar'                   => [
            'cover'        => 2048,
            'background'   => 2048,
            'track'        => (100 * 2560),
            'header_image' => 2048,
        ],
        'surveycategory'            => [
            'logo' => 2048,
        ],
        'event'                     => [
            'logo' => 2048,
        ],
        'app-theme'                 => [
            'theme' => 2048,
        ],
        'label_settings'            => [
            'location_logo'   => 2048,
            'department_logo' => 2048,
        ],
        'challenge_map'             => [
            'image' => 2048,
        ],
        'subcategories'             => [
            'logo'       => 2048,
            'background' => 2048,
        ],
        'services'                  => [
            'logo' => 2048,
            'icon' => 2048,
        ],
        'session'                   => [
            'attachment' => (5 * 1024),
        ],
        'podcasts'                  => [
            'logo'  => 2048,
            'track' => (100 * 1024),
        ],
        'shorts'                   => [
            'track'        => (100 * 1024),
            'header_image' => 2048,
        ],
    ],

    'imageConversions'                         => [
        'user'                      => [
            'logo'             => ['width' => 512, 'height' => 512],
            'counsellor_cover' => ['width' => 1280, 'height' => 640],
        ],
        'company'                   => [
            'logo'                       => ['width' => 320, 'height' => 320],
            'branding_logo'              => ['width' => 250, 'height' => 100],
            'branding_login_background'  => ['width' => 1920, 'height' => 1280],
            'email_header'               => ['width' => 600, 'height' => 157],
            'portal_logo_main'           => ['width' => 200, 'height' => 100],
            'portal_logo_optional'       => ['width' => 250, 'height' => 100],
            'portal_background_image'    => ['width' => 1350, 'height' => 900],
            'portal_footer_logo'         => ['width' => 180, 'height' => 60],
            'portal_favicon_icon'        => ['width' => 40, 'height' => 40],
            'portal_homepage_logo_right' => ['width' => 250, 'height' => 100],
            'portal_homepage_logo_left'  => ['width' => 200, 'height' => 100],
            'contact_us_image'           => ['width' => 800, 'height' => 800],
            'banner_image'               => ['width' => 640, 'height' => 640],
            'appointment_image'          => ['width' => 800, 'height' => 800],
        ],
        'team'                      => [
            'logo' => ['width' => 512, 'height' => 512],
        ],
        'meditation_tracks'         => [
            'cover'             => ['width' => 640, 'height' => 1280],
            'background'        => ['width' => 640, 'height' => 1280],
            'background_portal' => ['width' => 1280, 'height' => 640],
            'header_image'      => ['width' => 800, 'height' => 800],
        ],
        'user_exercise'             => [
            'logo' => ['width' => 320, 'height' => 160],
        ],
        'exercise'                  => [
            'logo'       => ['width' => 320, 'height' => 320],
            'background' => ['width' => 2560, 'height' => 1280],
        ],
        'badge'                     => [
            'logo' => ['width' => 320, 'height' => 320],
        ],
        'course'                    => [
            'logo'                      => ['width' => 1280, 'height' => 640],
            'trailer_background'        => ['width' => 640, 'height' => 1280],
            'trailer_background_portal' => ['width' => 1280, 'height' => 640],
            'track_thumbnail'           => ['width' => 1280, 'height' => 640],
            'header_image'              => ['width' => 800, 'height' => 800],
        ],
        'course_lession'            => [
            'audio_background'        => ['width' => 640, 'height' => 1280],
            'audio_background_portal' => ['width' => 1280, 'height' => 640],
            'logo'                    => ['width' => 800, 'height' => 800],
        ],
        'course_survey_question'    => [
            'logo' => ['width' => 1280, 'height' => 640],
        ],
        'feed'                      => [
            'featured_image'          => ['width' => 1280, 'height' => 640],
            'audio_background'        => ['width' => 640, 'height' => 1280],
            'audio_background_portal' => ['width' => 1280, 'height' => 640],
            'header_image'            => ['width' => 800, 'height' => 800],
        ],
        'recipe'                    => [
            'logo'         => ['width' => 1280, 'height' => 640],
            'header_image' => ['width' => 800, 'height' => 800],
        ],
        'group'                     => [
            'logo' => ['width' => 512, 'height' => 512],
        ],
        'challenge'                 => [
            'logo' => ['width' => 1280, 'height' => 640],
        ],
        'app_setting'               => [
            'splash_image_url' => ['width' => 640, 'height' => 1280],
        ],
        'company_wise_app_settings' => [
            'logo_image_url'   => ['width' => 512, 'height' => 512],
            'splash_image_url' => ['width' => 640, 'height' => 1280],
        ],
        'app_slide'                 => [
            'slideImage'       => ['width' => 640, 'height' => 640],
            'slideImagePortal' => ['width' => 1410, 'height' => 588],
        ],
        'notification'              => [
            'th_sm' => 64,
        ],
        'user_notification'         => [
            'logo' => ['width' => 512, 'height' => 512],
        ],
        'personalChallenge'         => [
            'logo' => ['width' => 1280, 'height' => 640],
        ],
        'eap'                       => [
            'logo' => ['width' => 512, 'height' => 512],
        ],
        'moods'                     => [
            'logo' => ['width' => 320, 'height' => 320],
        ],
        'question'                  => [
            'logo' => ['width' => 900, 'height' => 450],
        ],
        'questionoption'            => [
            'logo' => ['width' => 400, 'height' => 400],
        ],
        'goals'                     => [
            'logo' => ['width' => 320, 'height' => 320],
        ],
        'challenge_library'         => [
            'image' => ['width' => 1280, 'height' => 640],
        ],
        'webinar'                   => [
            'cover'        => ['width' => 1280, 'height' => 640],
            'header_image' => ['width' => 800, 'height' => 800],
        ],
        'surveycategory'            => [
            'logo' => ['width' => 200, 'height' => 200],
        ],
        'event'                     => [
            'logo' => ['width' => 1024, 'height' => 1024],
        ],
        'label_settings'            => [
            'location_logo'   => ['width' => 60, 'height' => 60],
            'department_logo' => ['width' => 60, 'height' => 60],
        ],
        'challenge_map'             => [
            'image'    => ['width' => 1280, 'height' => 640],
            'property' => ['width' => 512, 'height' => 512],
        ],
        'subcategories'             => [
            'logo'       => ['width' => 256, 'height' => 256],
            'background' => ['width' => 320, 'height' => 320],
        ],
        'services'                  => [
            'logo' => ['width' => 512, 'height' => 512],
            'icon' => ['width' => 256, 'height' => 256],
        ],
        'podcasts'                  => [
            'logo' => ['width' => 800, 'height' => 800],
        ],
        'shorts'                   => [
            'header_image' => ['width' => 1080, 'height' => 1920],
        ],
    ],

    'imageAspectRatio'                         => [
        'user'                      => [
            'logo'             => "1:1",
            'counsellor_cover' => "2:1",
        ],
        'feed'                      => [
            'featured_image'          => "2:1",
            'audio_background'        => "1:2",
            'audio_background_portal' => "2:1",
            'header_image'            => "1:1",
        ],
        'company'                   => [
            'logo'                       => "1:1",
            'branding_logo'              => "2.5:1",
            'branding_login_background'  => "1.5:1",
            'email_header'               => "3.82:1",
            'portal_logo_main'           => '2:1',
            'portal_logo_optional'       => '2.5:1',
            'portal_background_image'    => '1.5:1',
            'portal_footer_logo'         => '3:1',
            'portal_favicon_icon'        => '1:1',
            'portal_homepage_logo_right' => '2.5:1',
            'portal_homepage_logo_left'  => '2:1',
            'contact_us_image'           => '1:1',
            'banner_image'               => '1:1',
            'appointment_image'          => '1:1',
        ],
        'team'                      => [
            'logo' => "1:1",
        ],
        'meditation_tracks'         => [
            'cover'             => "1:2",
            'background'        => "1:2",
            'background_portal' => "2:1",
            'header_image'      => "1:1",
        ],
        'exercise'                  => [
            'logo'       => "1:1",
            'background' => "2:1",
        ],
        'badge'                     => [
            'logo' => "1:1",
        ],
        'course'                    => [
            'logo'                      => "2:1",
            'trailer_background'        => "1:2",
            'trailer_background_portal' => "2:1",
            'track_thumbnail'           => "2:1",
            'header_image'              => "1:1",
        ],
        'course_lession'            => [
            'audio_background'        => "1:2",
            'audio_background_portal' => "2:1",
            'logo'                    => "1:1",
        ],
        'course_survey_question'    => [
            'logo' => "2:1",
        ],
        'group'                     => [
            'logo' => "1:1",
        ],
        'recipe'                    => [
            'logo'         => "2:1",
            'header_image' => "1:1",
        ],
        'eap'                       => [
            'logo' => "1:1",
        ],
        'goals'                     => [
            'logo' => "1:1",
        ],
        'webinar'                   => [
            'cover'        => "2:1",
            'header_image' => "1:1",
        ],
        'challenge'                 => [
            'logo' => "2:1",
        ],
        'personalChallenge'         => [
            'logo' => "2:1",
        ],
        'challenge_library'         => [
            'image' => "2:1",
        ],
        'moods'                     => [
            'logo' => "1:1",
        ],
        'surveycategory'            => [
            'logo' => "1:1",
        ],
        'event'                     => [
            'logo' => "1:1",
        ],
        'label_settings'            => [
            'location_logo'   => "1:1",
            'department_logo' => "1:1",
        ],
        'company_wise_app_settings' => [
            'logo_image_url'   => "1:1",
            'splash_image_url' => "1:2",
        ],
        'app_setting'               => [
            'splash_image_url' => "1:2",
        ],
        'app_slide'                 => [
            'slideImage'       => "1:1",
            'slideImagePortal' => "2.40:1",
        ],
        'user_notification'         => [
            'logo' => "1:1",
        ],
        'question'                  => [
            'logo' => "2:1",
        ],
        'questionoption'            => [
            'logo' => "1:1",
        ],
        'challenge_map'             => [
            'image'    => "2:1",
            'property' => '1:1',
        ],
        'subcategories'             => [
            'logo'       => "1:1",
            'background' => "1:1",
        ],
        'services'                  => [
            'logo' => "1:1",
            'icon' => "1:1",
        ],
        'podcasts'                  => [
            'logo' => "1:1",
        ],
        'shorts'                   => [
            'header_image' => "9:16",
        ],
    ],

    'personalChallengeTypes'                   => [
        'routine'   => 'Routine Plan',
        'challenge' => 'Personal Fitness Challenge',
        'habit'     => 'Habit Plan',
    ],

    'personalRoutineChallengeSubType'          => [
        'to-do'  => 'To-do',
        'streak' => 'Streak',
    ],

    'personalFitnessChallengeSubType'          => [
        'steps'       => 'Steps',
        'distance'    => 'Distance',
        'meditations' => 'Meditation',
    ],

    'personalHabitChallengeSubType'            => [
        'to-do' => 'To-do',
    ],

    'max_limit_counts'                         => [
        'recipe' => [
            'logo' => 3,
        ],
    ],

    "challenge"                                => [
        'individual'    => 'Individual',
        'team'          => 'Team',
        'company_goal'  => 'Company Goal',
        'inter_company' => 'Inter Company',
        'personal'      => 'Personal',
    ],

    // App store Link
    'app_store_link'                           => [
        'android' => 'https://play.google.com/store/apps/details?id=com.zevolife.app',
        'ios'     => 'https://apps.apple.com/us/app/zevo-health/id1490234528',
    ],

    'meditationTrackType'                      => [
        1 => 'Audio',
        2 => 'Video',
        3 => 'Youtube',
        4 => 'Vimeo',
    ],

    'webinarTrackType'                         => [
        1 => 'Video',
        2 => 'Youtube',
        3 => 'Vimeo',
    ],

    'shortsTrackType'                         => [
        //1 => 'Video',
        //2 => 'Youtube',
        3 => 'Vimeo',
    ],

    'ICReportChallengeOptGroupLable'           => [
        'ongoing'   => 'Ongoing',
        'completed' => 'Completed',
    ],

    'EAPLimits'                                => [
        'SA'  => 20,
        'CA'  => 10,
        'RSA' => 20,
        'RCA' => 10,
    ],

    'date_format'                              => [
        'moment_default_datetime'                => 'MMM DD, YYYY, HH:mm',
        'moment_default_date'                    => 'MMM DD, YYYY',
        'default_datetime'                       => 'M d, Y, H:i',
        'default_date'                           => 'M d, Y',
        'default_time'                           => 'H:i',
        'default_time_24_hours'                  => 'H:i A',
        'default_datetime_24hours'               => 'M d, Y, H:i A',
        'moment_default_datetimesecond'          => 'M d, Y, H:i:s',
        'date_time_12_hours'                     => 'M d, Y, h:i A',
        'meditation_recepie_support_createdtime' => 'DD/MM/YY - HH:mm',
        'date_format_for_client_notes'           => 'M d, Y h:i A',
        'digital_therapy_datetime'               => 'MMM DD, YYYY, hh:mm A',
    ],

    'masterclass_trailer_type'                 => [
        1 => 'Audio',
        2 => 'Video',
        3 => 'Youtube',
        4 => 'Vimeo',
    ],

    'masterclass_lesson_type'                  => [
        1 => 'Audio',
        2 => 'Video',
        3 => 'Youtube',
        4 => 'Content',
        5 => 'Vimeo',
    ],

    'masterclass_survey_type'                  => [
        'pre'  => 'Pre',
        'post' => 'Post',
    ],

    'max_survey_question'                      => 5,

    'max_survey_question_option'               => 5,

    'masterclass_survey_question_type'         => [
        'single_choice' => 'Single choice',
        // 'multiple_choice' => 'Multiple choice',
    ],

    'masterclass_ckeditor_content_foldername'  => "masterclass_mix_content",

    'feed_type'                                => [
        1 => 'Audio',
        2 => 'Video',
        3 => 'Youtube',
        4 => 'Content',
        5 => 'Vimeo',
    ],

    'feed_ckeditor_content_foldername'         => "feed_mix_content",

    'tracker_list'                             => [
        "fitbit"    => "FitBit",
        "garmin"    => "Garmin",
        "googlefit" => "GoogleFit",
        "healthkit" => "HealthKit",
        "shealth"   => "Samsung Health",
        "strava"    => "Strava",
        "polar"     => "Polar",
    ],

    'zc_survey'                                => [
        'survey_category_max_count'     => 12,
        'survey_sub_category_max_count' => 100,
        'max_survey_question'           => 120,
    ],

    'domain_branding'                          => [
        "default_branding_title"       => "Join the Wellness Programme",
        "default_branding_description" => "Your employer has selected a great Workplace Wellness program for you. By signing up for the program you will get access to the Zevo Health app, competitions, meditation exercises, healthy recipes and nutrition tips, and more. A team of health experts is ready to talk to you about all questions you may have. They can create a personal exercise program for you, help you with a meal plan or get through a tough period in your life.",
        "PLATFORM_DOMAIN"              => ['zevolife', 'dev', 'qa', 'test', 'uat', 'zevo', 'preprod', 'performance'],
    ],

    // for reference survey frequency in survey settings section for create/update company page
    'survey_frequency'                         => [
        1 => 'Weekly',
        2 => 'Bi-weekly',
        3 => 'Monthly',
        4 => 'Quarterly',
        5 => 'Half Yearly',
    ],

    'survey_days'                              => [
        "sunday"    => 'Sunday',
        "monday"    => 'Monday',
        "tuesday"   => 'Tuesday',
        "wednesday" => 'Wednesday',
        "thursday"  => 'Thursday',
        "friday"    => 'Friday',
        "saturday"  => 'Saturday',
    ],

    'nps_feedback_type'                        => [
        'very_happy'   => 'Very Happy',
        'happy'        => 'Happy',
        'neutral'      => 'Neutral',
        'unhappy'      => 'Unhappy',
        'very_unhappy' => 'Very Unhappy',
    ],

    'survey_frequency_day'                     => [
        // for weekly
        '1' => 7,
        // for bi-weekly
        '2' => 14,
        // for monthly
        '3' => 30,
        // for quarterly
        '4' => 90,
        // for Half Yearly
        '5' => 180,
    ],

    'zc_survey_max_score_value'                => 7,

    'zc_survey_score_color_code'               => [
        'yellow' => '#ffab00',
        'green'  => '#21c393',
        'red'    => '#f44436',
        'grey'   => '#eeeeee',
    ],

    'challenge_image_library_max_images_limit' => 20,

    'challenge_image_library_target_type'      => [
        'step'       => 'Steps',
        'distance'   => 'Distance',
        'meditation' => 'Meditation',
        'exercise'   => 'Exercise',
        'general'    => 'General',
        'map'        => 'Map',
    ],

    'ga_env_enabled'                           => ['production'],

    'predefined_sub_domains'                   => [
        'dev',
        'qa',
        'uat',
        'preprod',
        'performance',
        'test',
        'zevo',
        'socket',
        'chat',
        'assets',
    ],

    /*
    |--------------------------------------------------------------------------
    | Terms for protal login wording
    |--------------------------------------------------------------------------
     */
    'PORTAL'                                   => 'portal',
    'IOS'                                      => 'ios',
    'ANDROID'                                  => 'android',

    /*
    |--------------------------------------------------------------------------
    | Terms for company wording
    |--------------------------------------------------------------------------
    |
     */
    'company_types'                            => [
        'parent' => 'Parent',
        'child'  => 'Child',
        'zevo'   => 'Zevo',
    ],

    /*
    |--------------------------------------------------------------------------
    | Wellbeing specialist availability status
    |--------------------------------------------------------------------------
    |
     */
    'hc_availability_status'                   => [
        '1' => 'Available',
        '0' => 'Unavailable',
        '2' => 'Custom leave',
    ],

    /*
    |--------------------------------------------------------------------------
    | Wellbeing specialist availability days with short name
    |--------------------------------------------------------------------------
    |
     */
    'hc_availability_days'                     => [
        'mon' => 'Monday',
        'tue' => 'Tuesday',
        'wed' => 'Wednesday',
        'thu' => 'Thursday',
        'fri' => 'Friday',
        'sat' => 'Saturday',
        'sun' => 'Sunday',
    ],

    'portal_static_urls'                       => [
        'login'          => '/login',
        'reset_password' => '/reset-password/',
        'survey'         => '/survey',
    ],

    /*
    |--------------------------------------------------------------------------
    | Company app label string ( default )
    |--------------------------------------------------------------------------
    |
     */
    'company_label_string'                     => [
        'home'       => [
            'recent_stories' => [
                'default_value' => 'Recent Stories',
                'length'        => 15,
            ],
            'lbl_company'    => [
                'default_value' => 'Company',
                'length'        => 15,
            ],
        ],
        'support'    => [
            'get_support'         => [
                'default_value' => 'Get Support',
                'length'        => 20,
            ],
            'employee_assistance' => [
                'default_value' => 'Employee Assistance',
                'length'        => 20,
            ],
            'lbl_faq'             => [
                'default_value' => 'FAQ',
                'length'        => 20,
            ],
        ],
        'onboarding' => [
            'lbl_location'    => [
                'default_value' => 'Location',
                'length'        => 50,
            ],
            'location_logo'   => [
                'type'              => 'logo',
                'default_value'     => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/onboard_location.png',
                'length'            => 2048,
                'support_file_type' => 'Supported file type: PNG',
            ],
            'lbl_department'  => [
                'default_value' => 'Department',
                'length'        => 50,
            ],
            'department_logo' => [
                'type'              => 'logo',
                'default_value'     => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/onboard_department.png',
                'length'            => 2048,
                'support_file_type' => 'Supported file type: PNG',
            ],
        ],
    ],

    'gender'                                   => [
        'female' => 'Female',
        'male'   => 'Male',
        'other'  => 'Other',
        'none'   => 'Prefer not to say',
    ],

    'defaultAuthor'                            => [
        1 => 'Zevo Admin',
    ],

    'event-status-listing'                     => [
        1 => 'Draft',
        2 => 'Published',
    ],

    'event-status-master'                      => collect([
        1 => ['id' => 1, 'text' => 'Paused', 'class' => 'text-secondary'],
        2 => ['id' => 2, 'text' => 'Published', 'class' => 'text-info'],
        3 => ['id' => 3, 'text' => 'Cancelled', 'class' => 'text-danger'],
        4 => ['id' => 4, 'text' => 'Booked', 'class' => 'text-warning'],
        5 => ['id' => 5, 'text' => 'Completed', 'class' => 'text-success'],
        6 => ['id' => 6, 'text' => 'Paused', 'class' => 'text-warning'],
        7 => ['id' => 7, 'text' => 'Cancelled', 'class' => 'text-danger'],
        8 => ['id' => 8, 'text' => 'Cancelled', 'class' => 'text-danger'],
    ]),

    /*
    |--------------------------------------------------------------------------
    | Event location type
    |--------------------------------------------------------------------------
    |
     */
    'event-location-type'                      => [
        1 => 'Online',
        2 => 'Onsite',
    ],
    /*
    |--------------------------------------------------------------------------
    | Company Type
    |--------------------------------------------------------------------------
    |
     */
    'company-type'                             => [
        'Zevo'    => 'Zevo',
        'Company' => 'Company',
    ],

    /*
    |--------------------------------------------------------------------------
    | Youtube Embeded url for portal
    |--------------------------------------------------------------------------
    |
     */
    'youtubeembedurl'                          => 'https://www.youtube.com/embed/',

    /*
    |--------------------------------------------------------------------------
    | EAP Get Help default value
    |--------------------------------------------------------------------------
    |
     */
    'eapSetting'                               => [
        'support' => true,
        'faq'     => true,
        'eap'     => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | ZC Question Status
    |--------------------------------------------------------------------------
     */
    'zcQuestionStatus'                         => [
        0 => 'Draft',
        2 => 'Review',
        1 => 'Published',
    ],

    /*
    |--------------------------------------------------------------------------
    | Event result pagination
    |--------------------------------------------------------------------------
    |
     */

    'event_result_pagination'                  => 20,

    /*
    |--------------------------------------------------------------------------
    | Challenge export date
    |--------------------------------------------------------------------------
    |
     */
    'challenge_set_date'                       => [
        'before' => 15,
        'after'  => 15,
    ],

    /*
    |--------------------------------------------------------------------------
    | Report export folder path on s3 bucket
    |--------------------------------------------------------------------------
    |
     */
    'report-export'                            => [
        'intercomapnychallenge' => 'intercomapnychallenge/',
        'survey'                => 'survey-export-report/',
        'masterclass'           => 'mc-survey-export-report/',
    ],

    /*
    |--------------------------------------------------------------------------
    | Portal notification redirection
    |--------------------------------------------------------------------------
    |
     */
    'portal_notification'                      => [
        'recipe'              => '/content/recipes/details/{id}',
        'masterclass'         => '/masterclass/details/{id}',
        'meditation'          => '/content/meditation/details/{id}',
        'webinar'             => '/content/webinars/details/{id}',
        'feed'                => '/content/stories/details/{id}',
        'event'               => '/talks/details/{id}',
        'event_registered'    => '/talks/details/{id}',
        'eap'                 => '/supports/',
        'csat'                => '/csat',
        'csat_event'          => '/csat/event/{id}',
        'csat_masterclass'    => '/csat/masterclass/:id',
        'audit_survey'        => '/survey/:id',
        'new_eap'             => '/eap/:id',
        'eap_feedback'        => '/csat/eap/:id',
        'digital_therapy'     => '/book/appointments/details/{id}',
        'consent_form'        => '/consent-form/:id/:type',
        'consent_form_online' => '/consent-form-online/:id/:type',
    ],

    /*
    |--------------------------------------------------------------------------
    | Portal survey color code
    |--------------------------------------------------------------------------
    |
     */
    'portal_survey_color_code'                 => [
        '80-100' => '#30C206',
        '60-80'  => '#FFAB00',
        '0-60'   => '#F61100',
    ],

    /*
    |--------------------------------------------------------------------------
    | AppTheme json for mobile
    |--------------------------------------------------------------------------
    |
     */
    'app_theme'                                => [
        'dark'  => 'Dark',
        'light' => 'Light',
        'esb'   => 'ESB',
    ],

    /*
    |--------------------------------------------------------------------------
    | AppTheme file path for mobile
    |--------------------------------------------------------------------------
    |
     */
    'app_theme_path'                           => [
        'dark'  => 'static_media/theme_json/dark_theme.json',
        'light' => 'static_media/theme_json/light_theme.json',
        'esb'   => 'static_media/theme_json/esb_theme.json',
    ],

    /*
    |--------------------------------------------------------------------------
    | Role wise chars. limit of about me section of users.
    |--------------------------------------------------------------------------
    |
     */
    'user_about_me_role_wise_limit'            => [
        'user'                 => 6000,
        'health_coach'         => 6000,
        'counsellor'           => 6000,
        'wellbeing_specialist' => 6000,
        'wellbeing_team_lead'  => 6000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Feed type Icon as per media
    |--------------------------------------------------------------------------
    |
     */
    'type_icon'                                => [
        1 => 'feed_audio',
        2 => 'feed_video',
        3 => 'feed_youtube',
        4 => 'feed',
        5 => 'feed_vimeo',
    ],

    /*
    |--------------------------------------------------------------------------
    | Feed type array as per media
    |--------------------------------------------------------------------------
    |
     */
    'type_array'                               => [
        1 => "AUDIO",
        2 => "VIDEO",
        3 => "YOUTUBE",
        4 => "CONTENT",
        5 => "VIMEO",
    ],

    /*
    |--------------------------------------------------------------------------
    | Vimeo Embeded url for portal
    |--------------------------------------------------------------------------
    |
     */
    'vimeoembedurl'                            => 'https://player.vimeo.com/video/',
    'vimeoappurl'                              => 'https://player.vimeo.com/video/',
    'vimeoshorsurl'                            => 'https://player.vimeo.com/progressive_redirect/playback/',

    /*
    |--------------------------------------------------------------------------
    | Youtube Embeded url for portal
    |--------------------------------------------------------------------------
    |
     */
    'youtubeembedurl'                          => 'https://www.youtube.com/embed/',
    'youtubeappurl'                            => 'https://www.youtube.com/watch?v=',

    /*
    |--------------------------------------------------------------------------
    | Module wise char. limit of subcategory
    |--------------------------------------------------------------------------
    |
     */
    'subcategoryModulesCharLimit'              => [
        1 => 20, // masterclass
        2 => 20, // feed
        3 => 20, // group
        4 => 20, // meditation
        5 => 20, // recipe
        6 => 50, // expertise
        7 => 20, // webinar
        8 => 50, // skills,
        9 => 20, // Podcast
        10 => 20 // Shorts
    ],

    /*
    |--------------------------------------------------------------------------
    | Feedback color class for NPS and Event graph
    |--------------------------------------------------------------------------
    |
     */
    'feedback_class_color'                     => [
        'very_unhappy' => 'red',
        'unhappy'      => 'orange-dark',
        'neutral'      => 'orange',
        'happy'        => 'lime',
        'very_happy'   => 'teal',
    ],

    /*
    |--------------------------------------------------------------------------
    | Portal domain
    |--------------------------------------------------------------------------
    |
     */
    'portal_domain'                            => [
        'local'      => [
            'localhost'              => 'localhost',
            'dev.zevowork.com'       => 'dev.zevowork.com',
            'qa.zevowork.com'        => 'qa.zevowork.com',
            'test.dev.zevowork.com'  => 'test.dev.zevowork.com',
            'test1.dev.zevowork.com' => 'test1.dev.zevowork.com',
            'test2.dev.zevowork.com' => 'test2.dev.zevowork.com',
            'test3.dev.zevowork.com' => 'test3.dev.zevowork.com',
            'test4.dev.zevowork.com' => 'test4.dev.zevowork.com',
            'test5.dev.zevowork.com' => 'test5.dev.zevowork.com',
            'test6.dev.zevowork.com' => 'test6.dev.zevowork.com',
            'test7.dev.zevowork.com' => 'test7.dev.zevowork.com',
            'test8.dev.zevowork.com' => 'test8.dev.zevowork.com',
            'test9.dev.zevowork.com' => 'test9.dev.zevowork.com',
        ],
        'dev'        => [
            'localhost'                  => 'localhost',
            'dev.zevowork.com'           => 'dev.zevowork.com',
            'test.dev.zevowork.com'      => 'test.dev.zevowork.com',
            'test1.dev.zevowork.com'     => 'test1.dev.zevowork.com',
            'test2.dev.zevowork.com'     => 'test2.dev.zevowork.com',
            'test3.dev.zevowork.com'     => 'test3.dev.zevowork.com',
            'test4.dev.zevowork.com'     => 'test4.dev.zevowork.com',
            'test5.dev.zevowork.com'     => 'test5.dev.zevowork.com',
            'test6.dev.zevowork.com'     => 'test6.dev.zevowork.com',
            'test7.dev.zevowork.com'     => 'test7.dev.zevowork.com',
            'test8.dev.zevowork.com'     => 'test8.dev.zevowork.com',
            'test9.dev.zevowork.com'     => 'test9.dev.zevowork.com',
            'developer.dev.zevowork.com' => 'developer.dev.zevowork.com',
        ],
        'qa'         => [
            'localhost'             => 'localhost',
            'qa.zevowork.com'       => 'qa.zevowork.com',
            'test.qa.zevowork.com'  => 'test.qa.zevowork.com',
            'test1.qa.zevowork.com' => 'test1.qa.zevowork.com',
            'test2.qa.zevowork.com' => 'test2.qa.zevowork.com',
            'test3.qa.zevowork.com' => 'test3.qa.zevowork.com',
            'test4.qa.zevowork.com' => 'test4.qa.zevowork.com',
            'test5.qa.zevowork.com' => 'test5.qa.zevowork.com',
            'test6.qa.zevowork.com' => 'test6.qa.zevowork.com',
            'test7.qa.zevowork.com' => 'test7.qa.zevowork.com',
            'test8.qa.zevowork.com' => 'test8.qa.zevowork.com',
            'test9.qa.zevowork.com' => 'test9.qa.zevowork.com',
        ],
        'uat'        => [
            'uat.zevowork.com'       => 'uat.zevowork.com',
            'test.uat.zevowork.com'  => 'test.uat.zevowork.com',
            'test1.uat.zevowork.com' => 'test1.uat.zevowork.com',
            'test2.uat.zevowork.com' => 'test2.uat.zevowork.com',
            'test3.uat.zevowork.com' => 'test3.uat.zevowork.com',
            'test4.uat.zevowork.com' => 'test4.uat.zevowork.com',
            'test5.uat.zevowork.com' => 'test5.uat.zevowork.com',
            'test6.uat.zevowork.com' => 'test6.uat.zevowork.com',
            'test7.uat.zevowork.com' => 'test7.uat.zevowork.com',
            'test8.uat.zevowork.com' => 'test8.uat.zevowork.com',
            'test9.uat.zevowork.com' => 'test9.uat.zevowork.com',
        ],
        'production' => [
            'live.zevowork.com'      => 'live.zevowork.com',
            'irishlifeworklife.ie'   => 'irishlifeworklife.ie',
            'demo.zevowork.com'      => 'demo.zevowork.com',
            'zevotherapy.com'        => 'zevotherapy.com',
            'zevowork.com'           => 'zevowork.com',
            'therapy.zevowork.com'   => 'therapy.zevowork.com',
            'tiktok.zevotherapy.com' => 'tiktok.zevotherapy.com',
            'tp.zevotherapy.com'     => 'tp.zevotherapy.com',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Zevohealth Mail Footer URL
    |--------------------------------------------------------------------------
    |
     */
    'mail-footer-url'                          => "https://zevohealth.zendesk.com/hc/en-gb/requests/new",
    'mail-front-email-address'                 => "admin@",
    'mail-zendesk-admin'                       => [
        'uat'         => [
            'email' => "irishlifesupport@zevohealth.zendesk.com",
        ],
        'zevotherapy' => [
            'email' => "support@zevotherapy.zendesk.com",
        ],
        'local'       => [
            'email' => "demo@yopmail.com",
        ],
    ],
    /*
    |--------------------------------------------------------------------------
    | Zevo Portal realted Limits
    |--------------------------------------------------------------------------
    |
     */

    'portal_limits'                            => [
        'audit_survey' => "Audit Survey",
        'masterclass'  => "Masterclass",
        'meditation'   => "Meditation",
        'feed'         => "Feed",
        'webinar'      => "Webinar",
        'recipe'       => "Recipe",
    ],

    'default_portal_limits'                    => [
        'audit_survey' => 100,
        'masterclass'  => 50,
        'meditation'   => 10,
        'feed'         => 5,
        'webinar'      => 10,
        'recipe'       => 5,
    ],

    'default_portal_limits_message'            => [
        'audit_survey' => "Points will get per survey completion",
        'masterclass'  => "Points will get per masterclas completion",
        'meditation'   => "points will get per meditation completion",
        'feed'         => "Points will get per view",
        'webinar'      => "Points will get per view",
        'recipe'       => "Points will get per view",
    ],

    /*
    |--------------------------------------------------------------------------
    | Rewards point daily limit
    |--------------------------------------------------------------------------
    |
     */

    'reward_point_labels'                      => [
        'meditation' => "Meditation",
        'feed'       => "Feed",
        'webinar'    => "Webinar",
        'recipe'     => "Recipe",
    ],

    'reward_point_daily_limit'                 => [
        'audit_survey' => 0,
        'masterclass'  => 0,
        'meditation'   => 5,
        'feed'         => 5,
        'webinar'      => 5,
        'recipe'       => 5,
    ],

    'reward_point_daily_limit_message'         => [
        'meditation' => "Meditations that users earn points for in a day",
        'feed'       => "Feed that users earn points for in a day",
        'webinar'    => "Webinar that users earn points for in a day",
        'recipe'     => "Recipe that users earn points for in a day",
    ],

    /*
    |--------------------------------------------------------------------------
    | Medidation Track Type For listing
    |--------------------------------------------------------------------------
    |
     */
    'meditation_track_list'                    => [
        1 => "AUDIO",
        2 => "VIDEO",
        3 => "YOUTUBE",
        4 => "VIMEO",
    ],

    /*
    |--------------------------------------------------------------------------
    | Company content master type
    |--------------------------------------------------------------------------
    |
     */
    'company_content_master_type'              => [
        1 => 'Masterclass',
        4 => 'Meditation',
        7 => 'Webinar',
        2 => 'Feed',
        5 => 'Recipe',
    ],
    //Zevo Company content master type
    'company_content_master_type_zevo'         => [
        1 => 'Masterclass',
        4 => 'Meditation',
        7 => 'Webinar',
        2 => 'Feed',
        5 => 'Recipe',
        9 => 'Podcast',
    ],

    /*
    |--------------------------------------------------------------------------
    | Event Export Temp Folder name
    |--------------------------------------------------------------------------
    |
     */
    'event_export_temp_folder_name'            => 'certs',

    /*
    |--------------------------------------------------------------------------
    | Masterclass pre/pose survey score
    |--------------------------------------------------------------------------
    |
     */
    'masterclass_surveys_score'                => [
        0 => 0,
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
        5 => 5,
        6 => 6,
        7 => 7,
    ],

    /*
    |--------------------------------------------------------------------------
    | Hint/Tooltip messages for youtube and vimeo
    |--------------------------------------------------------------------------
    |
     */
    'youtube_hint_message'                     => [
        'placeholder' => 'https://www.youtube.com/watch?v=XXXXXXXXXXX',
        'message'     => 'Please enter the Youtube video URL in this https://www.youtube.com/watch?v=XXXXXXXXXXX format.',
    ],

    'vimeo_hint_message'                       => [
        'placeholder' => 'https://vimeo.com/XXXXXXXXX',
        'message'     => 'Please enter the Vimeo video URL in this https://vimeo.com/XXXXXXXXX format.',
    ],

    'video_link_message'                       => [
        'placeholder' => 'https://xyz.com/XXXXXXXXX',
        'message'     => 'Please enter the video URL in this https://xyz.com/XXXXXXXXX format.',
    ],

    'vimeo_hint_message_shorts' => [
        'placeholder' => 'https://player.vimeo.com/progressive_redirect/playback/XXXXXXXXX',
        'message'     => 'Please enter the Vimeo video URL in this https://player.vimeo.com/progressive_redirect/playback/XXXXXXXXX format.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Broadcast status type / group type
    |--------------------------------------------------------------------------
    |
     */
    'broadcast_status_type'                    => [
        1 => 'Pending',
        2 => 'Broadcasted',
        3 => 'Cancelled',
    ],

    'broadcast_group_type'                     => [
        'inter_company' => 'Intercompany Challenge',
        'team'          => 'Team challenge',
        'company_goal'  => 'Company Goal',
        'individual'    => 'Individual challenge',
        'masterclass'   => 'Masterclass',
        'public'        => 'Public Group',
        'private'       => 'Private Group',
    ],

    /*
    |--------------------------------------------------------------------------
    | Get All Companies Records for All Content Group type
    |--------------------------------------------------------------------------
    |
     */
    'content_company_group_type'               => [
        1 => 'Zevo',
        2 => 'Parent',
        3 => 'Child',
    ],

     /*
    |--------------------------------------------------------------------------
    | Get All Companies Records for All Shorts
    |--------------------------------------------------------------------------
    |
     */
    'shorts_company_group_type'               => [
        1 => 'Zevo',
    ],

    /*
    |--------------------------------------------------------------------------
    | Select Registration Restriction(domain_verification_types) of company
    |--------------------------------------------------------------------------
    |
     */
    'domain_verification_types'                => [
        0 => 'Do not verify email domains',
        1 => 'Verify email domains',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default fallback white background image
    |--------------------------------------------------------------------------
    |
     */
    'default_fallback_white_image_url'         => env('APP_URL') . 'assets/dist/img/boxed-bg.png',

    /*
    |--------------------------------------------------------------------------
    | Content Type for Content Report
    |--------------------------------------------------------------------------
    |
     */
    'company_content_content_report'           => [
        1 => 'Masterclass',
        4 => 'Meditation',
        7 => 'Webinar',
        2 => 'Feed',
        5 => 'Recipe',
        8 => 'Supports (EAP)',
        9 => 'Podcast',
    ],

    /*
    |--------------------------------------------------------------------------
    | Feed type array filter
    |--------------------------------------------------------------------------
    |
     */
    'type_array_filter'                        => [
        1 => "Audio",
        2 => "Video",
        3 => "Youtube",
        4 => "Content",
        5 => "Vimeo",
    ],

    /*
    |--------------------------------------------------------------------------
    | calendly session status
    |--------------------------------------------------------------------------
    |
     */
    'calendly_session_status'                  => [
        'upcoming'       => 'Upcoming',
        'ongoing'        => 'Ongoing',
        'completed'      => 'Completed',
        'cancelled'      => 'Cancelled',
        'rescheduled'    => 'Rescheduled',
        'no_show'        => 'No Show',
        'short_canceled' => 'Short Cancel',
    ],

    /*
    |--------------------------------------------------------------------------
    | Company plan group type
    |--------------------------------------------------------------------------
    |
     */
    'company_plan_group_type'                  => [
        1 => 'Zevo',
        2 => 'Reseller',
    ],

    /*
    |--------------------------------------------------------------------------
    | Badge type when create new badge.
    |--------------------------------------------------------------------------
    |
     */
    'createBadgeTypes'                         => [
        'general' => 'General',
        'ongoing' => 'Ongoing Challenge',
    ],

    /*
    |--------------------------------------------------------------------------
    | Ongoing challenge target when type is selected ongoing
    |--------------------------------------------------------------------------
    |
     */
    'ongoingChallengeTarget'                   => [
        1 => 'Steps',
        2 => 'Distance',
    ],

    /*
    |--------------------------------------------------------------------------
    | Sync notification timings
    |--------------------------------------------------------------------------
    |
     */
    'sync_notification_timings'                => [
        date('Y-m-d 19:29:00'), // 730
        date('Y-m-d 19:59:00'), // 800
        date('Y-m-d 20:29:00'), // 830
        date('Y-m-d 20:59:00'), // 900
        date('Y-m-d 21:29:00'), // 930
    ],

    /*
    |--------------------------------------------------------------------------
    | User default goal steps - 6000 ( For all gender )
    |--------------------------------------------------------------------------
    |
     */
    'goalSteps'                                => 6000,

    /*
    |--------------------------------------------------------------------------
    | Individual and team challenge for meditations
    |--------------------------------------------------------------------------
    |
     */
    'challenge_rule_description_meditation'    => [
        'individual'   => [
            1 => 'The winner of the challenge will be the person who achieves the target before the challenge completion date.',
            2 => 'The winner of the challenge will be the person with the highest points total and is top of the leaderboard.',
            3 => 'Any person who completes the target consistently on a daily basis will be declared as a winner once challenge the completes.',
            4 => 'The winner of the challenge will be the person who reaches both targets and gets max points.',
            5 => 'The winner of the challenge will be the person who reaches the target in the minimum amount of time during the challenge. All users must sync before the challenge completes.',
        ],
        'team'         => [
            1 => 'The winner of the challenge will be the team who achieves the target before the challenge completion date.',
            2 => 'The winner of the challenge will be the team with the highest points total and  is top of the leaderboard.',
            4 => 'The winner of the challenge will be the team who reaches both targets and gets max points.',
        ],
        'company_goal' => [
            1 => 'The challenge will be successfully completed when the target is reached by all teams working together.',
            3 => 'The challenge will be successfully completed if all teams complete the streak.',
            4 => 'The challenge will be successfully completed when both targets are reached.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Recipes filter slider values step
    |--------------------------------------------------------------------------
    |
     */
    'recipe_filter_step'                       => [
        'cookingTime' => 20,
        'calories'    => 100,
        'protein'     => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | SP Cron job schedule time
    |--------------------------------------------------------------------------
    |
     */
    // 'SPCronScheduleTime' => 600  // In Second ( every 10 minutes )
    'SPCronScheduleTime'                       => 420, // In Second ( every 7 minutes )

    /*
    |--------------------------------------------------------------------------
    | Portal footer default values
    |--------------------------------------------------------------------------
    |
     */
    'portalFooter'                             => [
        'footerText'   => '© 2022 Irish Life Wellbeing Limited. A private company limited by shares. Registered in Ireland No.686621. Registered Office: Irish Life Centre, Lower Abbey Street, Dublin 1.',
        'footerHeader' => "<p><strong>Got any questions?</strong></p><p><strong>Click on the contact button below.</strong></p>",
        'header1'      => 'TERMS & PRIVACY',
        'header2'      => 'DATA CONSENT',
        'header3'      => 'WORKLIFE SUPPORT',
        'col1key'      => [
            'Data Privacy Notice',
            'Terms of Use',
            'Complaints Policy',
        ],
        'col2key'      => [
            'Cookies Policy',
        ],
        'col3key'      => [
            'General Enquires',
            'FAQs',
            'Contact Us',
        ],
        'col1value'    => [
            'privacy-notices',
            'terms',
            'complaints-policy',
        ],
        'col2value'    => [
            'cookies-policy',
        ],
        'col3value'    => [
            'contact-us',
            'faq',
            'contact-us',
        ],
    ],
    /*
    |--------------------------------------------------------------------------
    | Event booking email default
    |--------------------------------------------------------------------------
    |
     */
    'mail-zendesk-event'                       => [
        'uat'        => [
            'email' => "bookings@yopmail.com",
        ],
        'production' => [
            'email' => "support@zevohealth.zendesk.com",
        ],
        'local'      => [
            'email' => "demozevo@yopmail.com",
        ],
        'dev'        => [
            'email' => "demo@yopmail.com",
        ],
        'qa'         => [
            'email' => "demo@yopmail.com",
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Google map key for map library
    |--------------------------------------------------------------------------
    |
     */
    'googleMapKey'                             => env('GOOGLE_MAP_KEY', 'AIzaSyC29oNS5-BU1D81GjsZ2JTxm_gfiGe_soA'),

    /*
    |--------------------------------------------------------------------------
    | Steps Calculate for map chalenge
    | 1300 = 1 KM
    |--------------------------------------------------------------------------
    |
     */
    'steps'                                    => 1300,

    /*
    |--------------------------------------------------------------------------
    | Steps Authenticator calculation Start Date
    |--------------------------------------------------------------------------
    |
     */
    'stepAuthenticatorDate'                    => date('2022-04-01 00:00:00'),
    'stepAuthenticatorPrevDay'                 => 14,

    /*
    |--------------------------------------------------------------------------
    | User Type Declaration for user type roles
    |--------------------------------------------------------------------------
    |
     */
    'userTypes'                                => [
        'user'                 => 'User',
        'health_coach'         => 'Wellbeing Consultant',
        'counsellor'           => 'Counsellor',
        'wellbeing_specialist' => 'Wellbeing Specialist',
        'wellbeing_team_lead'  => 'Clinical Lead',
    ],

    /*
    |--------------------------------------------------------------------------
    | User language it's should for Wellbeing Specialist
    |--------------------------------------------------------------------------
    |
     */
    'userLanguage'                             => [
        1  => 'English',
        2  => 'Polish',
        3  => 'Portuguese',
        4  => 'Spanish',
        5  => 'French',
        6  => 'German',
        7  => 'Italian',
        8  => 'Arabic',
        9  => 'Hebrew',
        10 => 'Korean',
        11 => 'Turkish',
        12 => 'Mandarin',
        13 => 'Hindi',
        14 => 'Bengali',
        15 => 'Indonesian',
        16 => 'Dutch',
    ],

    /*
    |--------------------------------------------------------------------------
    | Video Conferencing Mode it's should for Wellbeing Specialist
    |--------------------------------------------------------------------------
    |
     */
    'video_conferencing_mode'                  => [
        1 => 'WhereBy',
        2 => 'Custom',
    ],

    /*
    |--------------------------------------------------------------------------
    | Shift it's should for Wellbeing Specialist
    |--------------------------------------------------------------------------
    |
     */
    'shift'                                    => [
        1 => 'Morning',
        2 => 'Evening',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default plan ( When no plan is added to company at that time select default plan which have all access )
    |--------------------------------------------------------------------------
    |
     */
    'default_plan'                             => 1,

    /*
    |--------------------------------------------------------------------------
    | Meditation sub categories ( Backgroud image / Icon )
    |--------------------------------------------------------------------------
    |
     */
    'meditation_images'                        => [
        'icons' => [
            //'view_all'  => env('DO_SPACES_DOMAIN') . '/static_media/meditation_images/icons/view_all.png',
            //'favorite'  => env('DO_SPACES_DOMAIN') . '/static_media/meditation_images/icons/favorite.png',
            'view_all'  => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/view_all_logo.png',
            'favorite'  => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/my_star_logo.png',
            'move'      => env('DO_SPACES_DOMAIN') . '/static_media/meditation_images/icons/scenes.png',
            'nourish'   => env('DO_SPACES_DOMAIN') . '/static_media/meditation_images/icons/sound.png',
            'inspire'   => env('DO_SPACES_DOMAIN') . 'static_media/meditation_images/icons/stress.png',
            'kids'      => env('DO_SPACES_DOMAIN') . '/static_media/meditation_images/icons/kids.png',
            'morning'   => env('DO_SPACES_DOMAIN') . '/static_media/meditation_images/icons/morning.png',
            'breathing' => env('DO_SPACES_DOMAIN') . '/static_media/meditation_images/icons/breathing.png',
            'sleep'     => env('DO_SPACES_DOMAIN') . '/static_media/meditation_images/icons/sleep.png',
            'stress'    => env('DO_SPACES_DOMAIN') . '/static_media/meditation_images/icons/stress.png',
            'anxiety'   => env('DO_SPACES_DOMAIN') . '/static_media/meditation_images/icons/anxiety.png',
            'focus'     => env('DO_SPACES_DOMAIN') . '/static_media/meditation_images/icons/focus.png',
            'scenes'    => env('DO_SPACES_DOMAIN') . '/static_media/meditation_images/icons/scenes.png',
            'sounds'    => env('DO_SPACES_DOMAIN') . '/static_media/meditation_images/icons/sound.png',
        ],
    ],

    'event_redirection_url'                    => 'https://www.yopmail.com/',
    'default_timezone'                         => 'Asia/Calcutta',

    'story_content_fallback_image_url'         => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/content-image.png',

    'stories'                                  => [
        'zsa' => 4,
        'all' => 2,
    ],

    'hide_target_types_company_settings'       => [
        'recent_webinar_limit',
        'recent_meditation_limit',
        'most_liked_webinar_limit',
        'most_liked_meditation_limit',
        'guided_meditation_limit',
    ],

    'portal_branding_terms_url'                => '/terms',
    'portal_branding_policy_url'               => '/privacy-notices',

    // Zendesk Key
    'zendesk_key'                              => env('zendesk_key', '66fd7b3f-58b7-4ccc-be5f-aa03e7952717'),

    // Digital therapy exception emails
    'mail-digital-therapy-exception'           => [
        'uat'   => [
            'email' => "test@yopmail.com",
        ],
        'local' => [
            'email' => 'test@yopmail.com',
        ],
    ],

    'session_email_reasons'                    => [
        'cancel_request'     => 'Cancellation Request',
        'reschedule_request' => 'Reschedule Request',
        'other'              => 'Other',
    ],

    'company_branding'                         => [
        'portal_sub_description' => 'Through the WorkLife portal, you can seamlessly access expert content & training resources on the Wellbeing themes you care most about.',
    ],

    'digital_therapy'                          => [
        'title'       => 'Digital Therapy',
        'description' => 'An employee assistance programme that assists employees with personal or work-related issues. Tap into our network of accredited and qualified online counsellors who can start helping you today.',
    ],

    // Content Challenge Points
    'content_challenge_points'                 => [
        'feed'                  => [
            'open'  => 2,
            'like'  => 2,
            'share' => 3,
        ],
        'masterclass'           => [
            'like'         => 5,
            'share'        => 5,
            'begin_survey' => 5,
            'end_survey'   => 5,
        ],
        'meditation'            => [
            'like'      => 2,
            'share'     => 3,
            'completed' => 5,
        ],
        'webinar'               => [
            'like'      => 5,
            'share'     => 5,
            'completed' => 5,
        ],
        'recipe'                => [
            'open'  => 2,
            'like'  => 2,
            'share' => 3,
        ],
        'group'                 => [
            'sending_message' => 5,
        ],
        'moods'                 => [
            'mood' => 2,
        ],
        'wellbeing_score'       => [
            'completing_wellbeing_survey' => 20,
        ],
        'default_limit_per_day' => [
            'feed'                     => [
                'limit'      => 5,
                'activities' => [
                    'open',
                    'like',
                    'share',
                ],
                'extraPoint' => 1.5,
                'id'         => 1,
            ],
            'masterclass'              => [
                'limit'      => 2,
                'activities' => [
                    'like',
                    'share',
                ],
                'extraPoint' => 1.5,
                'id'         => 2,
            ],
            'masterclass_begin_survey' => [
                'limit'      => 2,
                'activities' => [
                    'begin_survey',
                ],
                'extraPoint' => 0,
                'id'         => 2,
            ],
            'masterclass_end_survey'   => [
                'limit'      => 2,
                'activities' => [
                    'end_survey',
                ],
                'extraPoint' => 0,
                'id'         => 2,
            ],
            'meditation'               => [
                'limit'      => 5,
                'activities' => [
                    'like',
                    'share',
                ],
                'extraPoint' => 1.5,
                'id'         => 3,
            ],
            'meditation_completed'     => [
                'limit'      => 5,
                'activities' => [
                    'completed',
                ],
                'extraPoint' => 0,
                'id'         => 3,
            ],
            'webinar'                  => [
                'limit'      => 5,
                'activities' => [
                    'like',
                    'share',
                ],
                'extraPoint' => 1.5,
                'id'         => 4,
            ],
            'webinar_completed'        => [
                'limit'      => 3,
                'activities' => [
                    'completed',
                ],
                'extraPoint' => 0,
                'id'         => 4,
            ],
            'recipe'                   => [
                'limit'      => 5,
                'activities' => [
                    'open',
                    'like',
                    'share',
                ],
                'extraPoint' => 0,
                'id'         => 5,
            ],
            'group'                    => [
                'limit'      => 20,
                'activities' => [
                    'sending_message',
                ],
                'extraPoint' => 0,
                'id'         => 6,
            ],
            'moods'                    => [
                'limit'      => 1,
                'activities' => [
                    'mood',
                ],
                'extraPoint' => 0,
                'id'         => 7,
            ],
            'wellbeing_score'          => [
                'limit'      => 1,
                'activities' => [
                    'completing_wellbeing_survey',
                ],
                'extraPoint' => 0,
                'id'         => 8,
            ],
        ],
        'contents'              => [
            'feed'            => 'Stories',
            'meditation'      => 'Meditation',
            'masterclass'     => 'Masterclass',
            'recipe'          => 'Recipe',
            'group'           => 'Group',
            'moods'           => 'Moods',
            'webinar'         => 'Webinars',
            'wellbeing_score' => 'Wellbeing score',
        ],
    ],

    'session_email_reasons'                    => [
        'cancel_request'     => 'Cancellation Request',
        'reschedule_request' => 'Reschedule Request',
        'other'              => 'Other',
    ],

    'company_branding'                         => [
        'portal_sub_description' => 'Through the WorkLife portal, you can seamlessly access expert content & training resources on the Wellbeing themes you care most about.',
    ],

    'session_attachment_max_upload_limit'      => 3,

    'eap'                                      => [
        'all' => 3,
    ],

    // Portal popup company code.
    'portal_company_code'                      => [
        'local'      => [
            '327693',
        ],
        'dev'        => [
            '215477',
        ],
        'qa'         => [
            '319265',
        ],
        'uat'        => [
            '105849',
        ],
        'production' => [
            '215067',
        ],
    ],

    // Company Digital Therapy Set Hours By
    'setHoursBy'                               => [
        1 => 'Company',
        2 => 'Locations',
    ],

    // Company Digital Therapy Set Business By
    'setAvailabilityBy'                        => [
        1 => 'General',
        2 => 'Specific',
    ],

    /*
    |--------------------------------------------------------------------------
    | Get Podcast Companies Records
    |--------------------------------------------------------------------------
    |
     */
    'podcast_company_group_type'               => [
        1 => 'Zevo',
    ],

    /*
    |--------------------------------------------------------------------------
    | Portal Theme - it's use for portal
    |--------------------------------------------------------------------------
     */
    'portal_theme'                             => [
        'blue'          => 'Blue',
        'darkblue'      => 'Dark Blue',
        'darkgrey'      => 'Dark Grey',
        'green'         => 'Green',
        'pink'          => 'Pink',
        'purple'        => 'Purple',
        'yellow'        => 'Yellow',
        'darkgreywhite' => 'Black White',
    ],

    // Tiktok Company Code
    'tiktok_company_code'                      => [
        'local'      => [
            '293132',
        ],
        'dev'        => [
            '161106',
        ],
        'qa'         => [
            '297396',
        ],
        'uat'        => [
            '245076',
        ],
        'production' => [
            '344873',
        ],
    ],

    'sign_off_signature'                       => "The Zevo Health Team",

    'footer_copyright_text_domain'             => [
        'local'      => 'http://abc.zevolife.local',
        'dev'        => 'https://sigma.dev.zevolife.com',
        'qa'         => 'https://irishlife.qa.zevolife.com',
        'uat'        => 'https://irish.uat.zevolife.com',
        'production' => 'https://irishlifewellbeing.zevo.app',
    ],

    'hide_sitemap_company_code'                => [
        'local'      => [
            '293132',
        ],
        'dev'        => [
            '215477',
        ],
        'qa'         => [
            '319265',
        ],
        'uat'        => [
            '210955',
            '115786',
        ],
        'production' => [
            '279751',
            '206530',
            '209366',
            '270825',
        ],
    ],

    'is_group_menu_for_portal'                 => [
        'local'      => [
            '293132',
        ],
        'dev'        => [
            '161106',
            '215477',
            '134959',
        ],
        'qa'         => [
            '297396',
            '319265',
            '335440',
        ],
        'uat'        => [
            '245076',
            '200583',
            '349880',
        ],
        'production' => [
            '184428',
            '344873',
            '149896',
        ],
    ],

    'is_group_menu_for_portal_link'            => [
        'local'      => [
            '293132' => 'https://share.hsforms.com/1cyh88pE_RNKUgFtOvyBt8Q19a8o',
        ],
        'dev'        => [
            '161106' => 'https://share.hsforms.com/19x3loexeTj2ml-go7EhYtg19a8o',
            '215477' => 'https://share.hsforms.com/1cyh88pE_RNKUgFtOvyBt8Q19a8o',
            '134959' => 'https://share.hsforms.com/1cyh88pE_RNKUgFtOvyBt8Q19a8o',
        ],
        'qa'         => [
            '297396' => 'https://share.hsforms.com/19x3loexeTj2ml-go7EhYtg19a8o',
            '319265' => 'https://share.hsforms.com/1cyh88pE_RNKUgFtOvyBt8Q19a8o',
            '335440' => 'https://share.hsforms.com/1cyh88pE_RNKUgFtOvyBt8Q19a8o',
        ],
        'uat'        => [
            '245076' => 'https://share.hsforms.com/19x3loexeTj2ml-go7EhYtg19a8o',
            '200583' => 'https://share.hsforms.com/1cyh88pE_RNKUgFtOvyBt8Q19a8o',
            '349880' => 'https://share.hsforms.com/1cyh88pE_RNKUgFtOvyBt8Q19a8o',
        ],
        'production' => [
            '184428' => 'https://share.hsforms.com/19x3loexeTj2ml-go7EhYtg19a8o',
            '344873' => 'https://share.hsforms.com/19x3loexeTj2ml-go7EhYtg19a8o',
            '149896' => 'https://share.hsforms.com/1cyh88pE_RNKUgFtOvyBt8Q19a8o',
        ],
    ],

    'is_google_analytics_for_portal_tiktok'    => [
        'local'      => [
        ],
        'dev'        => [
        ],
        'qa'         => [
        ],
        'uat'        => [
        ],
        'production' => [
            '184428',
            '344873',
            '149896',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Branding Contact Us
    |--------------------------------------------------------------------------
    |
     */
    'branding_contact_details'                 => [
        'company_codes'                 => [
            'local'      => [
                '293132',
            ],
            'dev'        => [
                '215477',
            ],
            'qa'         => [
                '319265',
            ],
            'uat'        => [
                '245076',
            ],
            'production' => [
                '184428',
                '344873',
                '149896',
            ],
        ],
        'contact_us_header'             => 'Contact Us',
        'contact_us_description_tiktok' => 'This contact form is not appropriate for crisis situations (e.g., active suicidality or self-harm, hearing voices/seeing unusual visions, severe emotional distress, etc.). If you require immediate crisis assistance, phone your local emergency services.',
        'contact_us_description_local'  => 'For help or assistance please complete the form below.',
        'clinical'                      => 'clinical',
        'technical'                     => 'technical',
        'contact_us_request'            => [
            'clinical'  => 'Clinical Support',
            'technical' => 'Technical Support',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Company Digital Therapy Default Banners
    |--------------------------------------------------------------------------
    |
     */
    'company_dt_banners_max_limit'             => 5,
    'company_dt_banners_min_limit'             => 1,
    'zevo_banners'                             => [
        [
            'id'          => 1,
            'description' => 'We are here to listen when you are ready to talk. Speak with a professional counsellor quickly and privately.',
            'image'       => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/zevo_banner_1.png',
            'order'       => 1,
        ],
        [
            'id'          => 2,
            'description' => 'Choose a conversation topic and book your appointment in seconds.',
            'image'       => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/zevo_banner_2.png',
            'order'       => 2,
        ],
        [
            'id'          => 3,
            'description' => 'All of your conversations are confidential and never shared with your company.',
            'image'       => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/zevo_banner_3.png',
            'order'       => 3,
        ],
        [
            'id'          => 4,
            'description' => 'If you require immediate crisis assistance phone emergency services. Local numbers can be found below.',
            'image'       => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/zevo_banner_4.png',
            'order'       => 4,
        ],
    ],
    'portal_banners'                           => [
        [
            'id'          => 1,
            'description' => 'We are here to support you and to listen, whenever you need it. Speak with a professional clinician confidentially and quicky through our easy-to-use digital platform.',
            'image'       => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/parent_child_banner_1.png',
            'order'       => 1,
        ],
        [
            'id'          => 2,
            'description' => 'Select a topic or issue that you wish to speak about, choose from our network of experienced professionals, and book a session within seconds.',
            'image'       => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/parent_child_banner_2.png',
            'order'       => 2,
        ],
        [
            'id'          => 3,
            'description' => 'All of your sessions are completely confidential and never shared with your company. This is a private space for you.',
            'image'       => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/parent_child_banner_3.png',
            'order'       => 3,
        ],
        [
            'id'          => 4,
            'description' => 'Zevo Health T&S Specialised Care services are not a crisis intervention service. If you require immediate crisis assistance phone emergency services. Local numbers can be found below.',
            'image'       => env('DO_SPACES_DOMAIN') . '/static_media/fallback_images/parent_child_banner_4.png',
            'order'       => 4,
        ],
    ],
    'attachments_tooltip'                      => "Maximum 3 attachment can be upload per session.<br/>Only JPG, PNG, PDF, Doc, Docx or txt allowed.<br/>File size: 5 MB max.",
    'default_seeder_images'                    => [
        'company' => [
            'contactus_default'   => 'contactus-default.png',
            'contactus_tiktok'    => 'contactus-tiktok.png',
            'appointment_default' => 'appointment-default.png',
            'appointment_tiktok'  => 'appointment-tiktok.png',
        ],
    ],

    /**
     * Timezone change for America/Mexico_City
     * Before DST it's work UTC -5 now it's working with UTC -6
     * Now we change timezone to America/Lima, it's work with UTC -5
     */
    'mexico_city_timezone'                     => [
        'old_timezone' => 'America/Mexico_City',
        'new_timezone' => 'America/Regina',
    ],

    /**
     * Responsibilities dropdown for WBS users
     */
    'responsibilitiesList'                     => [
        1 => 'Wellbeing Specialist',
        2 => 'Event Presenter',
        3 => 'Both',
    ],

    'admin_alerts'                             => [
        'next_to_kin_info_title' => 'Access Next to Kin Info',
        'next_to_kin_info_desc'  => '<p>Hi,</p>
        <p>This email is to notify you that the next of kin information of a client was accessed by a wellbeing specialist from the Zevo backend.</p>
        <p>Wellbeing Specialist Name: #wellbeing_specialist_name#<br />
        Wellbeing Specialist Email:&nbsp;#wellbeing_specialist_email#<br />
        Client Name: #client_name#<br />
        Client Email: #client_email#</p>',
        'digital_therapy_title'                   => 'Digital Therapy Emails',
        'digital_therapy_desc'                    => '<p>BCC all the Digital therapy emails that set to the Clients and WBSs</p>',
        'digital_therapy_company_deletion_title'  => 'Digital Therapy Record Deletion',
        'digital_therapy_company_deletion_desc'   => '<p>Hi #user_name#,</p>
        <p>The #company_name# Company has been removed from the platform. Please find the exported Digital Therapy session records attached to this email.</p>',
        'wbs_profile_verification_title'          => 'Wellbeing Specialist Profile Completion',
        'wbs_profile_verification_desc'           => '<p>Hi #user_name#,</p>
        <p>This email is to notify you that a Wellbeing Specialist has successfully verified their profile.</p>
        <p>Name: #wellbeing_specialist_name#<br />
        Email:&nbsp;#wellbeing_specialist_email#</p>'
    ],

    /**
     * Digital therapy Report status
     */
    'dt_status'                                => [
        'upcoming'       => 'Upcoming',
        'ongoing'        => 'Ongoing',
        'completed'      => 'Completed',
        'cancelled'      => 'Cancelled',
        'rescheduled'    => 'Rescheduled',
        'no_show'        => 'No Show',
        'short_canceled' => 'Short Cancel',
    ],

    /**
     * Digital therapy Duration
     */
    'dt_duration'                              => [
        'next_24' => 'Next 24 Hours',
        'next_7'  => 'Next 7 Days',
        'next_30' => 'Next 30 Days',
        'next_60' => 'Next 60 Days',
    ],

    /**
     * Event Listing Status
     */
    'event_listing_status'                     => [
        6 => 'Paused',
        4 => 'Booked',
        3 => 'Cancelled',
        5 => 'Completed',
    ],

    'admin_alert_emails'                       => [
        'next_to_access_kin_info' => [
            'local'      => 'productteam@yopmail.com',
            'dev'        => 'productteam@yopmail.com',
            'qa'         => 'productteam@yopmail.com',
            'uat'        => 'productteam@yopmail.com',
            'production' => 'productteam@yopmail.com',
        ],
        'dt_exception_emails'     => [
            'local'      => 'dt.emails@yopmail.com',
            'dev'        => 'dt.emails@yopmail.com',
            'qa'         => 'dt.emails@yopmail.com',
            'uat'        => 'dt.emails@yopmail.com',
            'production' => 'dt.emails@yopmail.com',
        ],
        'dt_deletion_emails'     => [
            'local'      => 'dt.emails@yopmail.com',
            'dev'        => 'dt.emails@yopmail.com',
            'qa'         => 'dt.emails@yopmail.com',
            'uat'        => 'dt.emails@yopmail.com',
            'production' => 'dt.emails@yopmail.com',
        ],
    ],

    // Case manager access to tiktok company
    'is_case_manager_access_for_portal'        => [
        'local'      => [
            '293132',
        ],
        'dev'        => [
            '161106',
            '293011',
        ],
        'qa'         => [
            '319265',
            '335440',
        ],
        'uat'        => [
            '245076',
        ],
        'production' => [
            '149896',
        ],
    ],

    'case_manager_access_link_for_portal'      => [
        'local'      => [
            '293132' => 'https://share.hsforms.com/1Dm3B-d2YSJqzgxdJPvYKIA19a8o',
        ],
        'dev'        => [
            '161106' => 'https://share.hsforms.com/1Dm3B-d2YSJqzgxdJPvYKIA19a8o',
            '293011' => 'https://share.hsforms.com/1Dm3B-d2YSJqzgxdJPvYKIA19a8o',
        ],
        'qa'         => [
            '319265' => 'https://share.hsforms.com/1Dm3B-d2YSJqzgxdJPvYKIA19a8o',
            '335440' => 'https://share.hsforms.com/1Dm3B-d2YSJqzgxdJPvYKIA19a8o',
        ],
        'uat'        => [
            '245076' => 'https://share.hsforms.com/1Dm3B-d2YSJqzgxdJPvYKIA19a8o',
        ],
        'production' => [
            '149896' => 'https://share.hsforms.com/1Dm3B-d2YSJqzgxdJPvYKIA19a8o',
        ],
    ],

    // Survicate tool access to tiktok company
    'is_survicate_access_for_portal'           => [
        'local'      => [
            '293132',
        ],
        'dev'        => [
            '161106',
            '293011',
            '215477',
        ],
        'qa'         => [
            '297396',
        ],
        'uat'        => [
            '245076',
        ],
        'production' => [
            '184428',
            '149896',
        ],
    ],

    // Added start_interval for force fully start availability - At starting of the Point,
    // Like Availability start at 12 AM and you show in cronofy 12:30
    // Adding this forcefully start from 12 AM
    'start_interval' => 30, // Minutes

    /*
    |--------------------------------------------------------------------------
    | Wellbeing specialist event presenter default availability days with short name
    |--------------------------------------------------------------------------
    |
     */
    'hc_event_presenter_availability_days' => [
        'mon' => 'Monday',
        'tue' => 'Tuesday',
        'wed' => 'Wednesday',
        'thu' => 'Thursday',
        'fri' => 'Friday'
    ],

    /* Generate Real time availability Folder Name */
    'realtimefolderpath' => 'realtimeavailability',

    /* Admin alerts subject which goes to admin alert emails */
    'admin_alert_subject' => [
        'wbs_profiled_verified' => 'Wellbeing Specialist profile verification completed'
    ],

    'notification_project_id' => env('NOTIFICATION_PROJECT_ID', 'zevoheals'),
];
