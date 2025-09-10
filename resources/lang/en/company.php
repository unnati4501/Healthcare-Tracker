<?php

/*
|--------------------------------------------------------------------------
| Labels for roles module
|--------------------------------------------------------------------------
|
| The following language lines are used in buttons throughout the system.
| Regardless where it is placed, a button can be listed here so it is easily
| found in a intuitive way.
|
 */
return [
    'title'                => [
        'index'               => ' Companies',
        'add'                 => 'Add Company',
        'edit'                => 'Edit Company',
        'set_location_hours'  => 'Set Location Hours',
        'set_wellbeing_hours' => 'Set Wellbeing Hours',
    ],
    'buttons'              => [
        'add'           => 'Add Role',
        'edit'          => 'Edit',
        'delete'        => 'Delete',
        'add_moderator' => 'Add Moderator',
    ],
    'filter'               => [
        'name'                => 'Search By Role',
        'role'                => 'Select Role Group',
        'select_company_plan' => 'Search by company plan',
    ],
    'table'                => [
        'updated_at' => 'Updated At',
        'name'       => 'Role Group',
        'group'      => 'Role Name',
        'desc'       => 'Description',
    ],
    'form'                 => [
        'labels'      => [
            'enable_event'             => 'Enable Event',
            'eap_tab'                  => 'Is EAP',
            'email_header'             => 'Email Header',
            'remove_email_header'      => 'Remove',
            'company_plan'             => 'Company plan',
            'disable_sso'              => 'Disable SSO',
            'exclude_gender_and_dob'   => 'Exclude Gender and DOB',
            'manage_the_design_change' => 'Manage The Design Change',
        ],
        'placeholder' => [
            'remove_email_header' => 'Click to remove email header and set to default.',
            'company_plan'        => 'Select company plan',
        ],
    ],
    'modal'                => [
        'delete' => [
            'title'   => 'Delete Role?',
            'message' => 'Are you sure you want to delete this Role?',
        ],
    ],
    'messages'             => [
        'deleted'                       => "Role has been deleted successfully.",
        'in_user'                       => "The role is in use!",
        'delete_fail'                   => "Failed to delete role, please try again!",
        'data_store_success'            => 'Role has been added successfully!',
        'data_update_success'           => 'Role has been updated successfully!',
        'role_exist'                    => 'Role already exists.',
        "upload_image_dimension"        => "The uploaded image does not match the given dimension and ratio.",
        'subscription_end_date_message' => 'Subscription dates must be within range of the parent company.',
        'image_valid_error'             => 'Please try again with uploading valid image.',
        'image_size_2M_error'           => 'Maximum allowed size for uploading image is 2 mb. Please try again.',
        'portal_footer_updated'         => 'Portal footer data has been updated successfully!',
    ],
    'validation'           => [
        'name_required'                 => 'Please enter the Role Name.',
        'name_regex'                    => 'Only Letter and Space are allowed.',
        'name_unique'                   => 'Role already exists.',
        'group_required'                => 'Please select the Role Group.',
        'set_privileges_required'       => 'Please set privileges.',
        'first_name_required'           => 'Please enter first name.',
        'last_name_required'            => 'Please enter last name',
        'email_required'                => 'The email field is required.',
        'min_2_characters_first_name'   => 'The first name must be at least 2 characters.',
        'min_2_characters_last_name'    => 'The last name must be at least 2 characters.',
        'valid_first_name'              => 'Please enter valid first name',
        'valid_last_name'               => 'Please enter valid last name',
        'valid_email'                   => 'Please enter valid email',
        'email_exists'                  => 'Email already exists',
        'header_required'               => 'Please enter header text',
        'valid_header_required'         => 'Please enter valid header text',
        'header_max_limit'              => 'Maximum 200 characters are allowed',
        'contact_us_description_max'    => 'Maximum 300 characters are allowed',
        'contact_us_description_format' => 'Please enter valid description',
    ],

    'teams'                => [
        'title' => [
            'index' => 'Roles', 
        ],
        'table' => [
            'updated_at' => 'Teams of :company_name',
            'code'       => 'Team Code',
            'name'       => 'Team Name',
        ],
    ],
    'limit'                => [
        'title'   => [
            'index' => 'Limits of :company_name',
            'edit'  => 'Edit Limits',
        ],
        'buttons' => [
            'default' => 'Default limit',
        ],
        'form'    => [
            'placeholder' => [
                'enter'                  => 'Enter :value',
                'daily-meditation-limit' => 'Enter daily meditation limit',
                'enter-limits'           => 'Enter :limit Points',
                'enter-limits-pd'        => 'Enter :limit Per day',
                'daily-podcast-limit'    => 'Enter daily podcast limit',
            ],
            'labels'      => [
                'count'   => 'Count',
                'points'  => 'Points',
                'per-day' => 'Per day',
            ],
        ],
        'tabs'    => [
            'challenge_activity' => 'Challenge Activity Target :splitTypes and Points',
            'reward_activity'    => 'Reward Activity Target :splitTypes and Points',
            'reward_point_limit' => 'Reward Points :splitDaily Limit',
        ],
        'table'   => [
            'target_type'   => 'Target type',
            'target_values' => 'Target values',
        ],
        'modal'   => [
            'default' => [
                'title'   => 'Set Default?',
                'message' => 'Are you sure you want to set the default setting?',
            ],
        ],
    ],
    'survey_configuration' => [
        'title'    => [
            'index' => 'Survey Configuration of :company',
        ],
        'form'     => [
            'allow_survey_for_all' => 'Allow survey for all?',
            'select_users'         => 'Select Users',
        ],
        'messages' => [
            'select_users'          => 'Plesae select at least one user to send survey.',
            'survey_config_success' => 'Survey configuration updated successfully.',
        ],
    ],
    'digital_therapy'      => [
        'tooltips' => [
            'session_update'       => 'How many hours prior to the session start time a user is allowed to cancel or reschedule a session?',
            'advanced_booking'     => 'Minimum required duration between the session booking time and start time',
            'future_booking'       => 'How many days in advance can a user book a session?',
            'counselling_duration' => 'How many minutes a Counselling session may last?',
            'coaching_duration'    => 'How many minutes a Coaching or Custom session may last?',
            'max_sessions_user'    => 'Total number of sessions allowed per user in one year. 0 represents unlimited.',
            'max_sessions_company' => 'Total number of sessions allowed per company in one year. 0 represents unlimited.',
            'emergency_contacts'   => 'When set to Show the users will see the Emergency Contacts slide in Digital Therapy',
            'get_user_consent'     => 'If enabled the user users will receive a Consent form link through email before their first session with the Wellbeing Specialist',
        ],
    ],
    'manage_credits' => [
        'form'     => [
            'labels' => [
                'user_name'         => 'Updated By',
                'company_name'      => 'Company Name',
                'credits'           => 'Credit Count',
                'note'              => 'Note',
                'available_credits' => 'Available Credits',
                'onhold_credits'    => 'On-hold Credits',
                'update_type'       => 'Update Type',
            ],
            'tooltips' => [
                'credits' => 'While removing the Count should be equal or less than the existing Available Credits.',
            ],
            'placeholder' => [
                'enter_user_name' => 'Enter Name',
                'credits'         => '1-100',
                'enter_note'      => 'Enter Note',
            ]
        ],
        'table' => [
            'date_time'             => 'Date Time',
            'action'                => 'Action',
            'credit_count'          => 'Credit Count',
            'updated_by'            => 'Updated By',
            'available_credits'     => 'Available Credit Balance',
            'notes'                 => 'Notes',
            'credit_history'        => 'Credits History',
        ],
        'modal' => [
            'title' => 'Export Credit History',
            'email' => 'Email Address',
            'enter_email_address' => 'Enter Email Address',
        ],
        'messages' => [
            'credit_update_success'         => 'Credits has been added successfully!',
            'error_credit_count'            => 'Cannot remove more than the existing Available Credits.',
            'failed_to_load'                => 'Failed to load details.',
            'something_wrong'               => 'Something wrong',
            'something_wrong_try_again'     => 'Something went wrong please try again.',
            'unauthorized_access'           => 'You are not authorized.',
            'report_generate_in_background' => 'Report generation is running in the background, once it will be generated, the report will send to email.',
            'no_records_found'              => 'No record found!!!',
            'email_required'                => 'This field is required.',
            'valid_email'                   => 'Please enter a valid email address.',
        ]
    ],
    'dt_banners' => [
        'title'    => [
            'index' => 'DT Banners (:company)',
            'add'   => 'Add Banner',
            'edit'  => 'Edit Banner',
        ],
        'buttons'  => [
            'add'           => 'Add Banner',
            'remove_banner' => 'Delete Banner',
            'view'          => 'View',
            'edit'          => 'Edit',
            'delete'        => 'Delete',
        ],
        'table'                => [
            'updated_at'    => 'Updated At',
            'order'         => 'Order',
            'description'   => 'Description',
            'banner'        => 'Banner',
            'action'        => 'Actions'
        ],
        'form'     => [
            'labels' => [
                'order'             => 'Order',
                'description'       => 'Description',
                'baner'           => 'Banner',
                'note'              => 'Note',
                'available_credits' =>'Available Credits',
                'onhold_credits'    =>'On-hold Credits',
                'image'             => 'Image',
                'text'              => 'Text',
            ],
            'placeholder' => [
                'enter_user_name' => 'Enter Name',
                'credits'         => '1-100',
                'enter_note'      => 'Enter Note',
                'choose_file'     => 'Choose File',
            ]
        ],
        'messages' => [
            'banner_added'          => 'Banner has been added successfully!',
            'banner_updated'        => 'Banner has been updated successfully!',
            'nothing_change_order'  => 'Nothing to change the order',
            'failed_update_order'   => 'Failed to update order, Please try again!',
            'order_update_success'  => 'Order has been updated successfully',
            'banner_deleted'        => 'Banner has been deleted successfully!',
        ],
        'validation'           => [
            'description_required'          => 'This field is required',
            'description_max'               => 'Maximum 500 characters are allowed',
            'description_format'            => 'Please enter valid text',
            'banner_validation_max_limit'   => 'Cannot add more than :limit DT Banners',
            'banner_validation_min_limit'   => 'At least one DT banner is required',
            'delete_error'                  => 'Delete error',
        ],
    ]
];
