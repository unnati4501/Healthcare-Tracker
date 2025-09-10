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
    'title'           =>
[
        'index' => 'Users',
        'add'   => 'Add User',
        'edit'  => 'Edit User',
    ],
    'buttons'         => [
        'add'             => 'Add User',
        'edit'            => 'Edit',
        'delete'          => 'Delete',
        'archive'         => 'Archive',
        'block'           => 'Block',
        'unblock'         => 'Unblock',
        'disconnect'      => 'Disconnect user',
        'tracker_history' => 'View Tracker History',
        'back_to_team'    => 'Back to Teams',
        'disconnect_btn'  => 'Disconnect',
        'export_to_excel' => 'Export to excel',
        'verified'        => 'Verified',
        'unverified'      => 'Unverified',
        'not_verified'    => 'Not Verified',
    ],
    'filter'          => [
        'email'   => 'Search By Email',
        'name'    => 'Search By Full Name',
        'coach'   => 'Is Wellbeing Specialist',
        'company' => 'Select Company',
        'team'    => 'Select Team',
    ],
    'table'           => [
        'updated_at'      => 'Updated At',
        'full_name'       => 'Full Name',
        'email'           => 'Email',
        'role'            => 'Role',
        'company'         => 'Company',
        'team_name'       => 'Team',
        'is_health_coach' => 'Is Wellbeing Specialist',
        'actions'         => 'Actions',
    ],
    'form'            => [
        'labels'      => [
            'select_role_group'          => 'Select Role Group',
            'role_name'                  => 'Role Name',
            'role_desc'                  => 'Description',
            'set_privileges'             => 'Set Privileges',
            'counsellor_skills'          => 'Counsellor Skills',
            'counsellor_cover'           => 'Cover Image',
            'about'                      => 'Bio',
            'language'                   => 'Language',
            'video_conferencing_mode'    => 'Video Conferencing mode',
            'responsibilities'           => 'Responsibilities',
            'expertise'                  => 'Expertise',
            'advance_notice_period'      => 'Advance Notice Period',
            'shift'                      => 'Shift',
            'video_link'                 => 'Video Link',
            'sync_email_with_nylas'      => 'Sync email with Nylas',
            'years_of_experience'        => 'Years of experience',
            'service_type_subcategories' => 'Service Type & Subcategory',
        ],
        'placeholder' => [
            'role_name'                => 'Enter Role Name',
            'role_desc'                => 'Enter Description',
            'select_counsellor_skills' => 'Select counsellor skills',
            'choose_file'              => 'Choose File',
            'language'                 => 'Select Language',
            'video_conferencing_mode'  => 'Select Video Conferencing mode',
            'responsibilities'         => 'Responsibilities',
            'shift'                    => 'Select shift',
        ],
    ],
    'modal'           => [
        'delete'     => [
            'title'                       => 'Delete user?',
            'message'                     => 'Are you sure you want to delete this user?',
            'delete_custom_leave_title'   => 'Delete leave?',
            'delete_custom_leave_message' => 'Are you sure you want to delete this leave?',
        ],
        'archive'     => [
            'title'                       => 'Archive user?',
            'message'                     => 'Are you sure you want to archive',
            'delete_custom_leave_title'   => 'Archive leave?',
            'delete_custom_leave_message' => 'Are you sure you want to archive this leave?',
        ],
        'disconnect' => [
            'title'   => 'Disconnect user?',
            'message' => 'Are you sure you want to disconnect this user?',
        ],
    ],
    'messages'        => [
        "upload_image_dimension" => "The uploaded image does not match the given dimension and ratio.",
        "delete_custom_leave"    => "Leave deleted successfully",
        "unable_to_delete_leave" => "Something went wrong in deleting leave",
        "advance_notice_period_tooltip" => 'Advanced number of days required to book an Event with the Wellbeing Specialist',
    ],
    'validation'      => [
        'services_required' => 'The service type & subcategory field is required when user type is wellbeing specialist.',
    ],
    // tracker-history
    'tracker_history' => [
        'index'  => 'Tracker History - :username',
        'filter' => [
            'tracker'   => 'Tracker Name',
            'from_date' => 'From date',
            'to_date'   => 'To date',
            'to_sep'    => 'To',
        ],
        'table'  => [
            'tracker_name'             => 'Tracker Name',
            'tracker_change_date_time' => 'Tracker Date/Time (UTC)',
        ],
    ],

    'edit_profile'    => [
        'title'    => [
            'index' => 'Edit Profile',
        ],
        'form'     => [
            'labels'      => [
                'role_group'      => 'Role Group',
                'health_coach'    => 'Wellbeing Consultant',
                'company'         => 'Company',
                'department'      => 'Department',
                'team'            => 'Team',
                'profile_picture' => 'Profile Picture',
                'role'            => 'Role',
                'first_name'      => 'First name',
                'last_name'       => 'Last name',
                'email'           => 'Email',
                'date_of_birth'   => 'DOB',
                'gender'          => 'Gender',
                'height'          => 'Height',
                'weight'          => 'Weight',
                'about'           => 'About',
                'total_sessions'  => 'Total sessions:',
                'plan_type'       => 'Plan Type',
                'bio'             => 'Bio',
                'account_status'  => 'Account Status',
                'verified'        => 'Verified',
                'unverified'      => 'Unverified',
            ],
            'placeholder' => [
                'first_name'    => 'Enter First Name',
                'last_name'     => 'Enter Last Name',
                'email'         => 'Enter Email',
                'date_of_birth' => 'Select date of birth',
                'height'        => 'Enter Height(cm)',
                'weight'        => 'Enter Weight(kg)',
                'about'         => 'Enter Description',
            ],
        ],
        'messages' => [
            'image_valid_error'    => 'Please try again with uploading valid image.',
            'image_size_2M_error'  => 'Maximum allowed size for uploading image is 2 mb. Please try again.',
            'allow_company_domain' => 'Sorry, but you must use your official company email address',
        ],
    ],
];
