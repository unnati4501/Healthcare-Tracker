<?php

/*
|--------------------------------------------------------------------------
| Notification Enable / Disable for Portal / Mobile
|--------------------------------------------------------------------------
|
 */
return [
    'home'                        => [
        'new_story'    => [
            'is_mobile' => true,
            'is_portal' => true,
        ],
        'story_shared' => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
    ],
    'academy'                     => [
        'new_masterclass'    => [
            'is_mobile' => true,
            'is_portal' => true,
        ],
        'masterclass_shared' => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
    ],
    'recipe'                      => [
        'added'    => [
            'is_mobile' => true,
            'is_portal' => true,
        ],
        'reaction' => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
        'shared'   => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
        'deleted'  => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
        'approved' => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
    ],
    'meditation'                  => [
        'added'  => [
            'is_mobile' => true,
            'is_portal' => true,
        ],
        'shared' => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
    ],
    'individual_challenge'        => [
        'created'    => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
        'invitation' => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
        'accepted'   => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
        'cancelled'  => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
        'won'        => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
    ],
    'challenge'                   => [
        'deleted'        => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
        'auto_cancelled' => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
        'start'          => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
        'end'            => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
        'finished'       => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
    ],
    'team_company_goal_challenge' => [
        'created'  => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
        'canceled' => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
        'finished' => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
        'won'      => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
    ],
    'team_challenge'              => [
        'won' => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
    ],
    'intercompany_challenge'      => [
        'created'   => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
        'cancelled' => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
        'start'     => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
        'end'       => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
        'finished'  => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
        'won'       => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
    ],
    'personal_challenge'          => [
        'start'          => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
        'end'            => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
        'finished'       => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
        'won'            => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
        'reminder'       => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
        'today_reminder' => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
    ],
    'group'                       => [
        'enrolled'  => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
        'deleted'   => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
        'broadcast' => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
    ],
    'general_badges'              => [
        'steps'            => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
        'distance'         => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
        'meditation'       => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
        'masterclass'      => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
        'earned'           => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
        'shared'           => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
        'daily'            => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
        'ongoing_steps'    => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
        'ongoing_distance' => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
    ],
    'team'                        => [
        'removed' => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
        'updated' => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
    ],
    'general'                     => [
        'happy_birth' => [
            'is_mobile' => true,
            'is_portal' => true,
        ],
    ],
    'sync'                        => [
        'is_mobile' => true,
        'is_portal' => false,
    ],
    'health-score'                => [
        'is_mobile' => true,
        'is_portal' => false,
    ],
    'CSAT'                        => [
        'feedback' => [
            'is_mobile' => true,
            'is_portal' => true,
        ],
    ],
    'moods'                       => [
        'is_mobile' => true,
        'is_portal' => false,
    ],
    'users'                       => [
        'set_profile_picture' => [
            'is_mobile' => true,
            'is_portal' => true,
        ],
    ],
    'events'                      => [
        'added'             => [
            'is_mobile' => true,
            'is_portal' => true,
        ],
        'updated'           => [
            'is_mobile' => true,
            'is_portal' => true,
        ],
        'removed'           => [
            'is_mobile' => true,
            'is_portal' => true,
        ],
        'registered'        => [
            'is_mobile' => true,
            'is_portal' => true,
        ],
        'feedback'          => [
            'is_mobile' => true,
            'is_portal' => true,
        ],
        'reminder-today'    => [
            'is_mobile' => true,
            'is_portal' => true,
        ],
        'reminder-tomorrow' => [
            'is_mobile' => true,
            'is_portal' => true,
        ],
        'csat'              => [
            'is_mobile' => true,
            'is_portal' => true,
        ],
    ],
    'eap'                         => [
        'added' => [
            'is_mobile' => true,
            'is_portal' => true,
        ],
    ],
    'profile'                     => [
        'promt'   => [
            'is_mobile' => true,
            'is_portal' => true,
        ],
        'updated' => [
            'is_mobile' => true,
            'is_portal' => true,
        ],
    ],
    'workshop'                    => [
        'added'  => [
            'is_mobile' => true,
            'is_portal' => true,
        ],
        'shared' => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
    ],
    'survey'                      => [
        'available' => [
            'is_mobile' => true,
            'is_portal' => true,
        ],
        'expried'   => [
            'is_mobile' => false,
            'is_portal' => true,
        ],
    ],
    'new-eap'                     => [
        'booked'      => [
            'is_mobile' => false,
            'is_portal' => true,
        ],
        'cancelled'   => [
            'is_mobile' => false,
            'is_portal' => true,
        ],
        'rescheduled' => [
            'is_mobile' => false,
            'is_portal' => true,
        ],
        'assigned'    => [
            'is_mobile' => false,
            'is_portal' => true,
        ],
        'reminder'    => [
            'is_mobile' => false,
            'is_portal' => true,
        ],
    ],
    'digital-therapy' => [
        'reminder'    => [
            'is_mobile' => true,
            'is_portal' => true,
        ],
        'group-session-invite' => [
            'is_mobile' => true,
            'is_portal' => true,
        ],
        'group-session-reschedule' => [
            'is_mobile' => true,
            'is_portal' => true,
        ],
        'group-session-cancel' => [
            'is_mobile' => true,
            'is_portal' => true,
        ],
        'session-start-reminder' => [
            'is_mobile' => true,
            'is_portal' => true,
        ]
    ],
    'consent-form' => [
        'consent-form-receive'  => [
            'is_mobile' => true,
            'is_portal' => true,
        ],
    ],
    'podcast'     => [
        'added'  => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
        'shared' => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
    ],
    'shorts'     => [
        'added'  => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
        'shared' => [
            'is_mobile' => true,
            'is_portal' => false,
        ],
    ],
];
