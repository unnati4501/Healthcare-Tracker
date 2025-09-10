<?php

/*
|--------------------------------------------------------------------------
| Labels for marketplace module
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
        'index'   => 'Marketplace',
        'booking' => 'Bookings',
    ],
    'tabs'            => [
        'bookings_tab' => 'Bookings',
        'booked_tab'   => 'Booked',
    ],
    'filter'          => [
        'name'      => 'Search by Event name',
        'company'   => 'Search Company',
        'presenter' => 'Select Presenter',
        'category'  => 'Select Category',
        'status'    => 'Select Event Status',
    ],
    'buttons'         => [
        'book'                 => 'Book',
        'register'             => 'Register',
        'booking_details'      => 'Booking details',
        'view_event_reg_users' => 'View Registered Users',
        'cancel_event'         => 'Cancel Event',
        'edit_event'           => 'Edit event',
        'more_details'         => 'More Details',
        'edit'                 => 'Edit',
        'save'                 => 'Save',
    ],
    'table'           => [
        'event_name'       => 'Event name',
        'company'          => 'Company',
        'category'         => 'Category',
        'presenter'        => 'Presenter',
        'date_time'        => 'Date-Time',
        'registered_users' => 'Registered users',
        'status'           => 'Status',
        'actions'          => 'Actions',
    ],
    'form'            => [
        'labels'      => [],
        'placeholder' => [],
    ],
    'modal'           => [
        'delete' => [
            'title'   => 'Delete Role?',
            'message' => 'Are you sure you want to delete this Role?',
        ],
        'export' => [
            'title'   => 'Export Bookings',
            'message' => 'Report generation is running in background, Once it will be generated, the report will be sent to email.',
        ],
    ],
    'messages'        => [
        'loading_events'           => 'Loading events...',
        'no_result_found'          => 'No result found!',
        'load_more_events'         => 'Load more events',
        'uielementnotfound'        => 'Oops, an error occurred while fetching the Wellbeing Specialist availability. Please reach out to the Zevo Health admin at
<a href="mailto:support@zevohealth.zendesk.com" title="support@zevohealth.zendesk.com">
    support@zevohealth.zendesk.com
</a>
to notify them about this error.',
        'uielementnotfoundMessage' => 'Oops, an error occurred while fetching the Wellbeing Specialist availability. Please reach out to the Zevo Health admin at support@zevohealth.zendesk.com to notify them about this error.',
        'event_copied'             => 'Event details copied to the clipboard',
    ],

    'book_event'      => [
        'title'    => [
            'index' => 'Book Event',
            'edit'  => 'Edit Booked Event',
        ],
        'buttons'  => [
            'edit_event' => 'Edit Event',
            'book'       => 'Book',
        ],
        'form'     => [
            'labels'      => [
                'event_name'         => 'Event name',
                'duration'           => 'Duration',
                'location'           => 'Location type',
                'capacity'           => 'Capacity',
                'description'        => 'Description',
                'add_to_story'       => 'Create a Story',
                'company'            => 'Company',
                'date'               => 'Date',
                'time-from'          => 'Time-From',
                'time-to'            => 'Time-To',
                'time_presenter'     => 'Presenter',
                'register_all_users' => 'Register all users',
                'complementary'      => 'Complementary',
                'additional_notes'   => 'Additional notes',
                'email_notes'        => 'Email notes',
                'pick_a_time'        => 'Pick a presenter/time',
                'presenter_name'     => 'Name of Presenter',
                'company_type'       => 'Company Type',
                'video_link'         => 'Video Link',
                'cc_emails'          => 'CC Emails',
                'status'             => 'Status',
                'video_link_by'      => 'Video Link By',
            ],
            'placeholder' => [
                'presenter_name' => 'Enter presenter name',
            ],
            'tooltip'     => [
                'registration_date' => 'Please note this is not the Event date. Registration Date refers to the date and time when the Event will be available for the users to register on the App/Platform.',
            ],
        ],
        'modals'   => [
            'book' => [
                'title'   => 'Book Event',
                'message' => 'Are you sure you want to book the event?',
            ],
        ],
        'messages' => [
            'slot'                      => 'Please select Company, Date, and Time to view presenters with availability.',
            'loading_slots'             => 'Loading slots...',
            'no_result_found'           => 'No presenters are available for your chosen date. Please choose another day and try again',
            'something_wrong_try_again' => 'Something went wrong please try again.',
            'capacity_error'            => '#capacity# seat(s) are available for the event, you can not auto-register this event for all users',
            'credit_error'              => 'The selected company does not have sufficient credits to make this booking. Please reach out the the Customer Support team at support@zevohealth.zendesk.com for further assistance. Thank you!',
        ],
    ],

    'booking_details' => [
        'title'       => [
            'index' => 'Booking Details',
        ],
        'buttons'     => [
            'cancel_event' => 'Cancel Event',
            'edit_event'   => 'Edit Booking',
        ],
        'placeholder' => [
            'reason' => 'Enter reason for cancel the event',
        ],
        'form'        => [
            'labels' => [
                'event_name'        => 'Event name',
                'duration'          => 'Duration',
                'location'          => 'Location type',
                'capacity'          => 'Capacity',
                'description'       => 'Description',
                'add_to_story'      => 'Create a Story',
                'company'           => 'Company',
                'date'              => 'Date',
                'time-from'         => 'Time-From',
                'time-to'           => 'Time-To',
                'time_presenter'    => 'Presenter',
                'registration_date' => 'Registration Date',
                'register_all'      => 'Register all users',
                'complementary'     => 'Complementary',
                'company_type'      => 'Company Type',
                'video_link'        => 'Video Link',
            ],
        ],
        'modal'       => [
            'cancel' => [
                'title' => 'Are you sure, you want to cancel the event?',
            ],
        ],
    ],

    'users'           => [
        'title'   => [
            'index' => 'Event Registered Users (:event_name)',
        ],
        'filter'  => [
            'name'  => 'Search by name',
            'email' => 'Search By email',
        ],
        'buttons' => [
            'export_to_excel' => 'Export to excel',
        ],
        'table'   => [
            'user_name'         => 'User Name',
            'email'             => 'Email',
            'registration_date' => 'Registration Date',
        ],
    ],
];
