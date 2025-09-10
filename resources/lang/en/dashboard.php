<?php
return [
    'title'       => [
        'index'           => 'Dashboard',
        'question_report' => 'Question report',
    ],
    'tabs'        => [
        'usage'             => 'Usage',
        'behaviour'         => 'Behaviour',
        'audit'             => 'Audit',
        'booking'           => 'Event Bookings',
        'eapactivity'       => 'EAP Activity',
        'digitaltherapy'    => 'Digital Therapy Activity',
    ],
    // Labels for Booking Tab Dashboard
    'booking'     => [
        'upcoming_events'       => 'Upcoming Events',
        'total'                 => 'Total',
        'today'                 => "Today's Events",
        '7days'                 => '7 Days',
        '30days'                => '30 Days',
        'events_revenue'        => 'Events Revenue',
        'days'                  => 'days',
        'completed'             => 'Completed Events',
        'booked'                => 'Booked',
        'cancelled'             => 'Cancelled Events',
        'todays_event_calendar' => 'Today\'s Event calendar',
        'view_more'             => 'View More',
        'issue_trend'           => 'Top 10 Categories',
        'tooltips'              => [
            'upcoming_events' => 'Events that are  planned for the specified period.',
            'events_revenue'  => 'Cost / Revenue for planned events.',
        ],
    ],

    'usage'       => [
        'tooltips' => [
            'meditation_minutes' => 'Minutes of listening across the company.',
        ],
    ],

    'audit'       => [
        'headings'            => [
            'wellbeing_audit'              => 'Wellbeing audit',
            'company_score'                => 'Company Barometer',
            'category_company_score'       => 'Category Barometer',
            'detailed_category_score'      => 'Detailed Category Barometer',
            'percentage_vs_category_score' => 'Users vs Category Barometer',
            'subcategory_score'            => 'Subcategory Barometer',
            'review_free_text_question'    => 'Review free text question',
        ],
        'tooltips'            => [
            'company_score_help_text'     => 'Overall Wellbeing Audit scores of the company.',
            'category_company_score'      => 'Wellbeing Audit domain score.',
            'category_score'              => 'Category Score',
            'subcategory_score'           => 'Wellbeing Audit sub domain score.',
            'detailed_category_barometer' => 'Category and question-level insights',
            'users_vs_category_barometer' => 'Percentage of users above or below the detailed category score.',
        ],
        'buttons'             => [
            'question_report' => 'Go to question report',
            'view_answers'    => 'View answers',
            'back_to_report'  => 'Back to Report',
        ],
        'messages'            => [
            'loading_graphs'               => 'Loading graphs....',
            'no_data_category_graphs'      => 'No data available to plot the graphs',
            'loading_subcategories_graphs' => 'Loading subcategories graphs....',
            'no_data_subcategory_graphs'   => 'No data available to plot subcategories graphs',
            'loading_questions'            => 'Loading questions....',
            'no_data_to_display'           => 'No data available to display.',
        ],
        'details_table'       => [
            'sr_no'   => 'No.',
            'company' => 'Company',
            'answer'  => 'Answers',
        ],
        'question_report_tbl' => [
            'sr_no'         => 'No.',
            'question_type' => 'Question Type',
            'question'      => 'Question',
            'response'      => 'Response',
            'percentage'    => 'Percentage',
        ],
    ],

    'behaviour'   => [
        'tooltips' => [
            'moods_analysis' => 'Moods analysis for the company for the period.',
        ],
    ],

    'eapactivity' => [
        'headings' => [
            'todays'             => 'Today\'s',
            'sessions'           => 'Sessions',
            'upcoming_sessions'  => 'Upcoming Sessions',
            'completed_sessions' => 'Completed Sessions',
            'cancelled_sessions' => 'Cancelled Sessions',
            'appointment_trend'  => 'Appointment Days/Time Activity',
            'skill_trend'        => 'Top 10 Skills',
            'therapist'          => 'Counsellor',
            'total_therapist'    => 'Total Counsellor',
            'active_therapist'   => 'Active Counsellor',
            'utilization'        => 'Utilization',
            'referral_rate'      => 'Referral Rate',
        ],
    ],

    'digital-therapy' => [
        'headings' => [
            'todays'                        => 'Today\'s',
            'sessions'                      => 'Sessions',
            'upcoming_sessions'             => 'Upcoming Sessions',
            'completed_sessions'            => 'Completed Sessions',
            'cancelled_sessions'            => 'Cancelled Sessions',
            'appointment_trend'             => 'Completed Sessions by Day',
            'issue_trend'                   => 'Top 10 Issues',
            'wellbeing_specialist'          => 'Wellbeing Specialist',
            'total_wellbeing_specialist'    => 'Total Wellbeing Specialist',
            'active_wellbeing_specialist'   => 'Active Wellbeing Specialist',
            'utilization'                   => 'Utilization',
            'referral_rate'                 => 'Referral Rate',
        ],
    ],
];
