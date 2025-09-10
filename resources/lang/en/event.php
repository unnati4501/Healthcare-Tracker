<?php

/*
|--------------------------------------------------------------------------
| Labels for event module
|--------------------------------------------------------------------------
|
| The following language lines are used in buttons throughout the system.
| Regardless where it is placed, a button can be listed here so it is easily
| found in a intuitive way.
|
 */
return [
    'title'    => [
        'index' => 'Events',
        'add'   => 'Add Event',
        'edit'  => 'Edit Event',
    ],
    'buttons'  => [
        'add'                   => 'Add Event',
        'view'                  => 'View',
        'feedback'              => 'View Feedback',
        'edit'                  => 'Edit',
        'delete'                => 'Delete',
        'publish'               => 'Publish',
        'view_cancel_details'   => 'View Cancel Details',
        'cancel_event'          => 'Cancel event',
        'book_as_special_event' => 'Book as Special Event',
    ],
    'filter'   => [
        'name'     => 'Search By Name',
        'category' => 'Select Category',
        'status'   => 'Select Status',
    ],
    'table'    => [
        'serviceManagment' => [
            'event_name'         => 'Event name',
            'assigned_companies' => 'Assignee',
            'subcategory_name'   => 'Category',
            'duration_listing'   => 'Duration',
            'status'             => 'Status',
            'actions'            => 'Actions',
        ],
    ],
    'form'     => [
        'labels'      => [
            'select_role_group' => 'Select Role Group',
            'role_name'         => 'Role Name',
            'role_desc'         => 'Description',
            'set_privileges'    => 'Set Privileges',
        ],
        'placeholder' => [
            'role_name' => 'Enter Role Name',
            'role_desc' => 'Enter Description',
        ],
    ],
    'modal'    => [
        'delete' => [
            'title'   => 'Delete Role?',
            'message' => 'Are you sure you want to delete this Role?',
        ],
    ],
    'messages' => [
        'upload_image_dimension' => 'The uploaded image does not match the given dimension and ratio.',
    ],

    // details
    'details'  => [
        'buttons' => [
            'view_cancel_details' => 'View Cancel Details',
            'cancel_event'        => 'Cancel event',
        ],
        'filter'  => [],
        'table'   => [
            'company'                 => 'Company',
            'presenter'               => 'Presenter',
            'duration_date_time_view' => 'Duration/Date-Time',
            'status'                  => 'Status',
            'actions'                 => 'Actions',
        ],
    ],

    // feedback
    'feedback' => [
        'filter' => [],
        'title'  => [
            'graph_title' => 'Event experience score',
        ],
        'table'  => [
            'company_name'   => 'Company',
            'presenter_name' => 'Presenter Name',
            'emoji'          => 'Emoji',
            'feedback_type'  => 'Feedback Type',
            'notes'          => 'Notes',
            'date'           => 'Date',
        ],
    ],
];
