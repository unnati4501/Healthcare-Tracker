<?php

return [
    // Labels for Admin Alert module
    'title'       => [
        'admin_alerts' => 'Admin Alerts',
    ],
    'form'        => [
        'labels'      => [
            'alert_name'  => 'Alert Name',
            'description' => 'Description',
            'notify_users'=> 'Notifying Users',
            'user_name'   => 'Name',
            'user_email'  => 'Email',
            'action'      => 'Action',
            'users'       => 'Users',
        ],
        'placeholder' => [
            'alert_name'  => 'Alert Name',
            'description' => 'Description',
            'company'     => 'Select Company',
            'user_name'   => 'User Name',
            'user_email'  => 'User Email',
        ],
    ],
    'buttons'     => [
        'add_user' => 'Add User',
        'edit'   => 'Edit',
        'delete' => 'Delete',
    ],
    'table'       => [
        'updated_at'       => 'Updated At',
        'alert_name'       => 'Alert Name',
        'notifying_users'  => 'Notifying Users',
        'action'           => 'Actions',
        'user_name'        => 'Name',
        'user_email'       => 'Email',
        'no'               => 'No.',
        'user_list'        => 'Notify Users'
    ],
    'model_popup' => [
        'title'          => 'Add User',
        'delete_user'    => 'Delete user?',
        'delete_message' => 'Are you sure you want to remove this user?',
    ],
    'message'     => [
        'fullscreen_mode_for_description' => 'For the best appearance try full screen mode by clicking on button',
        'from_toolbar'                    => 'from toolbar.',
        'data_update_success'             => 'Data been updated successfully!',
        'something_wrong_try_again'       => 'Something went wrong please try again.',
        'user_deleted'                   => 'Users has been deleted successfully.',
    ],
    'validation'  => [
        'user_name_required'    => 'The user name field is required.',
        'user_name_valid'       => 'Please enter valid user name',
        'user_email_required'   => 'The email field is required.',
        'user_email_valid'      => 'Please enter valid email',
        'user_email_exists'     => 'Email already exists',
        'desc_required'         => 'The description field is required.',
        'desc_length'           => 'The description field may not be greater than 500 characters.',

    ],
];
