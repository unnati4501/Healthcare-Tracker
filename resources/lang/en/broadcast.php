<?php
return [
    // Labels for Broadcast module
    'title'      => [
        'index_title'     => 'Group Broadcast',
        'add_form_title'  => 'Add Broadcast Message',
        'edit_form_title' => 'Edit Broadcast Message',
        'filter'          => 'Filter',
    ],
    'filter'     => [
        'search_by_title'       => 'Search By Title',
        'select_group_type'     => 'Select Group Type',
        'search_by_group_name'  => 'Search By Group Name',
        'select_broadcast_type' => 'Select Broadcast Type',
    ],
    'table'      => [
        'updated_at' => 'Updated At',
        'action'     => 'Actions',
        'title'      => 'Title',
        'status'     => 'Status',
        'message'    => 'Message',
        'group_type' => 'Group Type',
        'group_name' => 'Group Name',
        'created_at' => 'Created At',
    ],
    'buttons'    => [
        'add_record' => 'Add Group Broadcast',
    ],
    'form'       => [
        'labels'      => [
            'instant_broadcast' => 'Instant Broadcast?',
            'date_time'         => 'Schedule Date Time',
            'group'             => 'Group',
            'group_type'        => 'Group Type',
            'message'           => 'Message',
            'title'             => 'Title',
        ],
        'placeholder' => [
            'enter_title'        => 'Enter Title',
            'select_schedule_at' => 'Select Schedule at',
            'enter_message'      => 'Enter Message',
            'select_group_type'  => 'Select Group Type',
            'select_group'       => 'Select Group',
        ],
    ],
    'modal'      => [
        'deletemessage' => 'Are you sure you want to delete broadcast?',
        'delete'        => 'Delete Broadcast?',
    ],
    'message'    => [
        'something_wrong'            => 'Something went wrong',
        'image_valid_error'          => 'Please try again with uploading valid image.',
        'image_size_2M_error'        => 'Maximum allowed size for uploading image or gif is 2 mb. Please try again.',
        'broadcast_deleted'          => 'Broadcast has been deleted successfully!',
        'delete_broadcast_message'   => 'You can\'t delete broadcast as it\'s about to send in sometime.',
        'failed_delete_broadcast'    => 'Failed to delete broadcast',
        'something_wrong_try_again'  => 'Something went wrong please try again.',
        'unauthorized_access'        => 'You are not authorized.',
        'edit_broadcast_sometime'    => 'You can\'t edit broadcast as it\'s about to send in sometime.',
        'schedule_broadcast_message' => 'Schedule date time should be greater than current time while scheduling a broadcast.',
    ],
    'validation' => [
    ],
];
