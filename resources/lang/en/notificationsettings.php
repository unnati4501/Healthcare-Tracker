<?php
return [
    // Labels for Notification Settings module
    'title'      => [
        'index_title'             => 'Notifications',
        'filter'                  => 'Filter',
        'add_form_title'          => 'Create Notification',
        'details'                 => 'Notification Details',
        'title'                   => 'Title',
        'message'                 => 'Message',
        'notification_recipients' => 'Notification Recipients',
    ],
    'filter'     => [
        'search_by_title'   => 'Search By Title',
        'search_by_message' => 'Search By Message',
    ],
    'table'      => [
        'updated_at' => 'Updated At',
        'title'      => 'Title',
        'creator'    => 'Created By',
        'message'    => 'Message',
        'action'     => 'Actions',
        'name'       => 'User Name',
        'email'      => 'Email',
        'received'   => 'Notification Received At',
        'sent'       => 'Sent',
        'read'       => 'Read',
    ],
    'buttons'    => [
        'add_notification' => 'Add Notification',
        'view'             => 'View',
    ],
    'form'       => [
        'labels'      => [
            'scheduled_time' => 'Schedule DateTime',
            'message'        => 'Message',
            'choose_file'    => 'Choose File',
            'title'          => 'Title',
            'logo'           => 'Logo',
            'push'           => 'Send as a push notification',
            'members'        => 'Members',
        ],
        'placeholder' => [
            'select_datetime' => 'Select DateTime',
            'enter_message'   => 'Enter Message',
            'enter_title'     => 'Enter Title',
        ],
    ],
    'modal'      => [
        'delete'         => 'Delete notification?',
        'delete_message' => 'Are you sure you want to delete this notification?',
    ],
    'message'    => [
        'notification_deleted'   => 'notification deleted',
        'notification_in_use'    => 'The notification is in use!',
        'delete_error'           => 'delete error.',
        'image_valid_error'      => 'Please try again with uploading valid image.',
        'member_required'        => 'The members field is required.',
        'image_size_2M_error'    => 'Maximum allowed size for uploading image is 2 mb. Please try again.',
        'upload_image_dimension' => 'The uploaded image does not match the given dimension and ratio.',
        'message_required'       => 'The message field is required.',
        'message_characters'     => 'The message may not be greater than 200 characters.',
    ],
    'validation' => [
    ],
];
