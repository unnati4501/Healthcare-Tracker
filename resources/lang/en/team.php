<?php

return [
    // Labels for categories module
    'title'      => [
        'index_title'     => 'Teams',
        'add_form_title'  => 'Add Team',
        'edit_form_title' => 'Edit Team',
        'search'          => 'Filter',
    ],
    'filter'     => [
        'team'           => 'Team',
        'select_company' => 'Select Company',
    ],
    'table'      => [
        'updated_at'  => 'Updated At',
        'company'     => 'Company',
        'logo'        => 'Logo',
        'team'        => 'Team',
        'team_member' => 'Team Member',
        'action'      => 'Actions',
    ],
    'buttons'    => [
        'add_team'      => 'Add Team',
        'view_location' => 'View Location',
    ],
    'form'       => [
        'labels'      => [
            'logo'              => 'Logo',
            'company'           => 'Company',
            'team_name'         => 'Team Name',
            'department'        => 'Department',
            'location'          => 'Location',
            'team_limit'        => 'Team Limit',
            'manage_content'    => 'Manage Content'
        ],
        'placeholder' => [
            'team'               => 'Team Name',
            'select_company'     => 'Select Company',
            'enter_team_name'    => 'Enter Team Name',
            'select_department'  => 'Select Department',
            'select_location'    => 'Select Location',
            'auto_team_creation' => 'Set auto team creation',

        ],
    ],
    'modal'      => [
        'delete'                => 'Delete Team?',
        'delete_message'        => 'Are you sure you want to delete this Team?',
        'set_limit'             => 'Set Limit',
        'set_limit_error_msg'   => 'You can set the limit once the ongoing and upcoming challenges are completed.',
        'team_deleted'          => 'Team deleted',
        'team_in_use'           => 'The team is in use!',
        'unable_to_delete_team' => 'Unable to delete team data.',
    ],
    'message'    => [
        'limit_updated'             => 'Limit has been updated successfully',
        'data_store_success'        => 'Team has been added successfully!',
        'data_update_success'       => 'Team has been updated successfully!',
        'image_valid_error'         => 'Please try again with uploading valid image.',
        'image_size_2M_error'       => 'Maximum allowed size for uploading image or gif is 2 mb. Please try again.',
        'something_wrong'           => 'Something wrong',
        'something_wrong_try_again' => 'Something went wrong please try again.',
        'unauthorized_access'       => 'You are not authorized.',
        'upload_image_dimension'    => 'The uploaded image does not match the given dimension and ratio.',
    ],
    'validation' => [
        'set_limit_error_msg' => 'You can set the limit once the ongoing and upcoming challenges are completed.',
        'already_name_taken'  => 'The name has already been taken.',
    ],
];
