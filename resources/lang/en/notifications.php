<?php

/*
 * Module wise notifications
 */

return [
    'feed'               => [
        'publish' => [
            'title'   => 'New story',
            'message' => 'Hi :first_name, we added a new story - :article_title for you.',
        ],
    ],
    'masterclass'        => [
        'publish' => [
            'title'   => 'New masterclass',
            'message' => 'Hi :first_name, we just added a new Masterclass - :masterclass_name for you.',
        ],
        'csat'    => [
            'title'   => 'Masterclass Feedback',
            'message' => 'Hi :first_name, please rate your experience with the :masterclass_name masterclass and help us make it better!',
        ],
    ],
    'share'              => [
        'title'          => '#module_name# shared',
        'message'        => '#user_name# shared #name# #module_name# with you.',
        'recipe_message' => '#user_name# shared the recipe of #name# with you.',
        'story_message'  => '#user_name# shared #name# story with you. Tap here to #story_type# now.',
    ],
    'recipe'             => [
        'added'    => [
            'title'   => 'Recipe added',
            'message' => 'Hi :first_name, we just added a new Recipe - :recipe_title for you.',
        ],
        'reaction' => [
            'title'   => 'Recipe reaction',
            'message' => 'User #user_name# gave kudos on your recipe.',
        ],
        'deleted'  => [
            'title'   => 'Recipe deleted',
            'message' => 'Your recipe #recipe_title# has been deleted by the admin.',
        ],
        'approved' => [
            'title'   => 'Recipe approved',
            'message' => 'Your recipe #recipe_title# has been approved by the admin.',
        ],
    ],
    'meditation'         => [
        'track-added' => [
            'title'   => 'Meditation added',
            'message' => 'Hi :first_name, we just added a new Meditation track - :track_title for you.',
        ],
    ],
    'challenge'          => [
        'challenge-created'             => [
            'title'   => 'Challenge created',
            'message' => [
                'individual'    => 'Hi :first_name, You have been enrolled in #challenge_name# starting on #challenge_start_date#.',
                'team'          => 'Your team has been enrolled in #challenge_name# starting on #challenge_start_date#.',
                'company_goal'  => 'Your team has been enrolled in #challenge_name# starting on #challenge_start_date#.',
                'inter_company' => 'Your team has been enrolled in #challenge_name# starting on #challenge_start_date#.',
            ],
        ],
        'challenge-invitation'          => [
            'title'   => 'Challenge invitation',
            'message' => 'Hi :first_name, You have received an invitation to join #challenge_title# challenge from #challenge_owner#. Please click here to accept now.',
        ],
        'challenge-invitation-accepted' => [
            'title'   => 'Challenge accepted',
            'message' => '#user_name# accepted your invite to #challenge_name#.',
        ],
        'challenge-cancelled'           => [
            'title'   => 'Challenge cancelled',
            'message' => '#challenge_name# was cancelled by the challenge owner.',
        ],
        'challenge-auto-cancelled'      => [
            'title'   => 'Challenge auto-cancelled',
            'message' => '#challenge_name# was auto-cancelled by the system.',
        ],
        'challenge-start-reminder'      => [
            'title'   => 'Challenge start',
            'message' => 'Don\'t forget #challenge_name# starts tomorrow.',
        ],
        'challenge-end-reminder'        => [
            'title'   => 'Challenge end',
            'message' => '#challenge_name# ends at #end_time#.',
        ],
        'challenge-finished'            => [
            'title'   => 'Challenge completed',
            'message' => '#challenge_name# has ended. Visit the challenge leaderboard to find out more!',
        ],
        'challenge-won'                 => [
            'title'              => 'You Won 🎉',
            'team_title'         => 'Your Team Won 🎉',
            'company_goal_title' => 'Challenge won',
            'intercompany_title' => 'Your Company Won 🎉',
            'message'            => [
                'individual'    => [
                    'non-recurring' => 'Congratulations :first_name! Well done on winning the #challenge_name# challenge.',
                    'recurring'     => 'Congratulations :first_name! Well done on winning the #challenge_name# challenge and achieving level #level_no#.',
                ],
                'team'          => 'Congratulations, your team won the #challenge_name# challenge and achieved level #level_no#.',
                'company_goal'  => 'Congratulations, you succeeded in the #company_name# #challenge_name# challenge.',
                'inter_company' => [
                    'team_level'    => 'Congratulations #user_name# your team were the top performers in #company_name# during the #challenge_name# challenge.',
                    'company_level' => 'Congratulations, your company won the #challenge_name# challenge. Amazing effort!',
                ],
            ],
        ],
        'challenge-loss'                => [
            'title'   => 'Challenge completed',
            'message' => [
                'individual'    => 'Hi :first_name, the #challenge_name# challenge is completed. Take a look at the leaderboard to find out the winner!',
                'team'          => 'Hi :first_name, the #challenge_name# challenge is completed. Take a look at the leaderboard to find out the winner!',
                'company_goal'  => 'Hi :first_name, the #challenge_name# challenge is completed. Take a look at the leaderboard to find out the winner!',
                'inter_company' => 'Hi :first_name, the #challenge_name# challenge is completed. Take a look at the leaderboard to find out the winner!',
            ],
        ],
        'challenge-updated-removed'     => [
            'title'   => 'Team removed',
            'message' => 'Your team has been removed from the #challenge_name#.',
        ],
        'challenge-deleted'             => [
            'title'   => 'Challenge deleted',
            'message' => '#challenge_name# was deleted by the challenge owner.',
        ],
        'challenge-points-added'          => [
            'title'   => ':challenge_name Points upgrade',
            'message' => ':points added to your total by the Zevo Admin. Visit the challenge leaderboard now to check your position!',
        ],
    ],
    'personal-challenge' => [
        'challenge-start'    => [
            'title'   => 'Challenge start',
            'message' => 'Don\'t forget #challenge_name# starts tomorrow.',
        ],
        'challenge-end'      => [
            'title'   => 'Challenge end',
            'message' => '#challenge_name# ends at #end_time#.',
        ],
        'challenge-finished' => [
            'title'   => 'Challenge completed',
            'message' => '#challenge_name# has ended.',
        ],
        'challenge-won'      => [
            'title'   => 'You Won 🎉',
            'message' => 'Well done on completing the #challenge_name# challenge.',
        ],
        'reminder'           => [
            'title'   => 'Reminder',
            'message' => 'Hi #user_name#, don\'t forget to tick off your completed task in the #challenge_name# challenge!',
        ],
        'pfc-reminder'       => [
            'title'   => 'Reminder',
            'message' => 'Hi #user_name#, Please complete the #challenge_name# challenge!',
        ],
        'yesterday-reminder' => [
            'title'   => 'Reminder',
            'message' => 'Hi #user_name#, you haven\'t logged your task for the #challenge_name# challenge yesterday! Would you like to do it now?',
        ],
    ],
    'group'              => [
        'user-assigned-group'         => [
            'title'   => 'Group',
            'message' => 'Let\'s break the ice, :first_name! Drop a "Hi" in the #group_name# group.',
        ],
        'new-group'                   => [
            'title'   => 'Group',
            //'message' => 'Let\'s break the ice, :first_name! Drop a "Hi" in the #group_name# group.',
            'message' => 'You have been added to a #group_name# group by #creator_name#. Click here to respond.',
        ],
        'user-assigned-updated-group' => [
            'title'   => 'Group',
            'message' => 'Let\'s break the ice, :first_name! Drop a "Hi" in the #group_name# group.',
        ],
        'message-in-group'            => [
            'title'   => 'Group message in the group',
            'message' => 'There is an unread message in #group_name#.',
        ],
        'group-deleted'               => [
            'title'   => 'Delete the group',
            'message' => 'Group #group_name# has been deleted.',
        ],
        'broadcast'                   => [
            'title'   => 'New Message',
            'message' => ':message',
        ],
    ],
    'badge'              => [
        'steps'            => [
            'title'   => 'Steps',
            'message' => 'Well done on earning the #badge_name# badge.',
        ],
        'distance'         => [
            'title'   => 'Distance',
            'message' => 'Well done on earning the #badge_name# badge.',
        ],
        'meditations'      => [
            'title'   => 'Meditation',
            'message' => 'Well done on earning the #badge_name# badge.',
        ],
        'course'           => [
            'title'   => 'MasterClass',
            'message' => 'Well done on completing the #course_name# masterclass.',
        ],
        'exercises'        => [
            'title'   => 'Badge earned',
            'message' => 'Well done on earning the exercise #badge_name# badge.',
        ],
        'daily'            => [
            'title'   => 'Steps Target Achieved',
            'message' => 'Congratulations #first_name#, you have achieved your daily target of #daily_step# steps.',
        ],
        'ongoing-steps'    => [
            'title'   => 'Steps',
            'message' => 'Well done on earning the #challenge_badge_name# badge on #challenge_name#.',
        ],
        'ongoing-distance' => [
            'title'   => 'Distance',
            'message' => 'Well done on earning the #challenge_badge_name# badge on #challenge_name#.',
        ],
    ],
    'team'               => [
        'added-to-team' => [
            'title'   => 'Team updated',
            'message' => 'You have been added to #team_name#.',
        ],
    ],
    'greetings'          => [
        'birthday' => [
            'title'   => 'Happy birthday',
            'message' => 'Happy birthday #first_name#, let\'s celebrate 🎉.',
        ],
    ],
    'tracker'            => [
        'synch' => [
            'title'   => 'Tracker sync',
            'message' => 'Don\'t forget to open the app and sync your steps, :first_name. 👟',
        ],
    ],
    'survey'             => [
        'reminder' => [
            'title'          => 'Wellbeing Score',
            'message'        => 'Hi #user_name#! Let\'s get started with your Well-being score today.',
            'second_message' => 'Hi #user_name#, update your Wellbeing score today!',
        ],
        'feedback' => [
            'title'          => 'Feedback',
            'message'        => 'We really value your opinion. Please rate your experiences since you started using the app.',
            'portal_message' => 'We really value your opinion. Please rate your experiences since you started using the portal.',
        ],
        'mood'     => [
            'title'   => 'How are you feeling?',
            'message' => 'Good morning :first_name. It\'s time to track your mood and become aware of your feelings!',

        ],
        'audit'    => [
            'title'   => 'Survey',
            'message' => 'Your latest :survey_name survey is available.',
        ],
    ],
    'profile'            => [
        'picture' => [
            'title'   => 'Set profile picture',
            'message' => 'Your profile picture is important to represent you in company events.',
        ],
    ],
    'webinar'            => [
        'track-added' => [
            'title'   => 'Webinar added',
            'message' => ':webinar_title is now available.',
        ],
    ],
    'eap'                => [
        'eap-added'     => [
            'title'   => 'Added',
            'message' => '#eap_title# was added under employee assistance.',
        ],
        'eap-completed' => [
            'title'   => 'Session Feedback',
            'message' => 'Hi :first_name, please rate your experience with the :eap_title and help us make it better!',
        ],
    ],
    'events'             => [
        'event-added'             => [
            'title'   => 'Added',
            'message' => ':event_name has been added.',
        ],
        'event-updated'           => [
            'title'   => 'Updated',
            'message' => ':event_name has been updated.',
        ],
        'event-registered'        => [
            'title'   => 'Registered',
            'message' => 'You have registered for :event_name.',
        ],
        'event-deleted'           => [
            'title'   => 'Removed',
            'message' => ':event_name is no longer available.',
        ],
        'event-reminder-tomorrow' => [
            'title'   => 'Upcoming',
            'message' => ':event_name is happening at :event_time.',
        ],
        'event-reminder-today'    => [
            'title'   => 'Upcoming',
            'message' => ':event_name will start in 30 minutes.',
        ],
        'csat'                    => [
            'title'   => 'Feedback',
            'message' => 'Please rate your satisfaction with :event_name.',
        ],
    ],
    'new-eap'            => [
        'booked'      => [
            'title'   => 'Booked',
            'message' => 'Your session with :counsellor is now booked!',
        ],
        'cancelled'   => [
            'title'   => 'Cancelled',
            'message' => 'Your session with :counsellor is cancelled!',
        ],
        'rescheduled' => [
            'title'   => 'Cancelled',
            'message' => 'Your session with :counsellor is cancelled!',
        ],
        'assigned'    => [
            'title'   => 'Assigned',
            'message' => ':counsellor has been assigned as your counsellor. Please book a session.',
        ],
        'reminder'    => [
            'title'   => 'Join',
            'message' => ':session_name will start in 15 mins, click to join.',
        ],
    ],
    'digital-therapy'          => [
        'session-start-reminder'      => [
            'title'   => 'Session Reminder',
            'message' => 'Your session with :WS_first_name starts soon',
        ],
        'group-session-invite' => [
            'title'   => 'Session Invite',
            'message' => 'You are invited for :sub_category_name session with :WS_first_name on :date',
        ],
        'group-session-cancel' => [
            'title'   => 'Session Cancel',
            'message' => 'Your session with :WS_first_name has been cancelled.',
        ],
        'group-session-reschedule' => [
            'title'   => 'Session Reschedule',
            'message' => 'Your session with :WS_first_name has been rescheduled.',
        ],
    ],
    'consent-form'          => [
        'consent-form-receive'      => [
            'title'   => 'Consent Form Received',
            'message' => 'Please submit the Consent Form before your :service_name session with :WS_first_name',
        ],
    ],
    'podcast'         => [
        'podcast-added' => [
            'title'   => 'Podcast added',
            'message' => 'Hi :first_name, we just added a new Podcast - :track_title for you.',
        ],
    ],
    'shorts'         => [
        'short-added' => [
            'title'   => 'Short added',
            'message' => 'Hi :first_name, we added a new Short - :short_title for you.',
        ],
    ],
];
