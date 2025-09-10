<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Client list block language lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during calendly module for various
    | messages that we need to display to the user.
    |
     */

    /*
    |--------------------------------------------------------------------------
    | Client list module constants
    |--------------------------------------------------------------------------
     */
    'title'    => [
        'index'   => 'Clients',
        'details' => 'Client Details',
    ],
    'filters'  => [
        'name'    => 'Search by name',
        'email'   => 'Search by email',
        'company' => 'Select company',
    ],
    'table'    => [
        'client_name'       => 'Client Name',
        'email'             => 'Email',
        'company_name'      => 'Company',
        'completed_session' => 'Completed sessions',
        'upcoming'          => 'Upcoming',
        'action'            => 'Action',
    ],
    'buttons'  => [
        'book'     => 'Book Session',
        'cancel'   => 'Cancel',
        'join'     => 'Join',
        'back'     => 'Back',
        'tooltips' => [
            'view' => 'View',
        ],
    ],
    'messages' => [
        'something_wrong_try_again' => 'Something went wrong. please try again.',
        'unauthorized_access'       => 'You are not authorized.',
    ],

    'details'  => [
        'completed'     => 'Completed',
        'ongoing'       => 'Ongoing',
        'cancelled'     => 'Cancelled',
        'notes'         => 'Notes',
        'cm_notes'      => 'Case Manager Notes',
        'add_note'      => 'Add Notes',
        'session_notes' => 'Session Notes',
        'filters'   => [
            'session_name'   => 'By Session Name',
            'session_status' => 'By Status',
        ],
        'table'     => [
            'session_name' => 'Session Name',
            'duration_min' => 'Duration (mins)',
            'status'       => 'Status',
            'view'         => 'View',
        ],
        'modal'     => [
            'cancel' => [
                'title'  => 'Cancellation details',
                'fields' => [
                    'cancelled_by'     => 'Cancelled by',
                    'cancelled_at'     => 'Cancelled at',
                    'cancelled_reason' => 'Cancelled reason',
                ],
            ],
            'delete' =>[
                'title'   => 'Delete Note?',
                'message' => 'Are you sure you want to delete this note?',
            ]
        ],
        'messages'  => [
            'no_comments'           => 'No notes were added yet',
            'loading_cm_notes'      => 'Loading case manager notes....',
            'no_cm_notes_date'      => 'No notes were found for the selected date',
            'failed_cm_notes'       => 'Failed to load case manager notes',
            'note_deleted'          => 'Note deleted',
            'unable_to_delete_note' => 'Failed to delete note'
        ],
    ],
];
