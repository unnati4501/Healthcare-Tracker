<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Calendly Session block Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during calendly module for various
    | messages that we need to display to the user.
    |
     */

    /*
    |--------------------------------------------------------------------------
    | Calendly module constants
    |--------------------------------------------------------------------------
     */
    'title'      => [
        'manage'      => 'Sessions',
        'details'     => 'Session Details',
        'add_session' => 'Add Sessions',
        'edit_session' => 'Edit Sessions',
    ],
    'table'      => [
        'updated_at' => 'Updated at',
        'name'       => 'Event name',
        'user'       => 'Client name',
        'email'      => 'Client email',
        'counsellor' => 'Counsellor',
        'company'    => 'Company',
        'duration'   => 'Duration (mins)',
        'datetime'   => 'Date/Time',
        'status'     => 'Status',
        'action'     => 'Action',
    ],
    'buttons'    => [
        'book'     => 'Book Session',
        'cancel'   => 'Cancel',
        'join'     => 'Join',
        'back'     => 'Back',
        'tooltips' => [
            'view' => 'View',
        ],
    ],
    'messages'   => [
        'something_wrong_try_again' => 'Something went wrong. please try again.',
        'unauthorized_access'       => 'You are not authorized.',
        'completed'                 => 'Session has been completed successfully.',
        'notes_update_success'      => 'Notes has been updated successfully!',
    ],
    'form'       => [
        'labels' => [
            'notes' => 'Notes',
        ],
    ],
    'validation' => [
        'notes_required' => 'Notes field is required.',
    ],
];
