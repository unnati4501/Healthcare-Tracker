<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Moods block Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during moods for various
    | messages that we need to display to the user.
    |
     */

    /*
    |--------------------------------------------------------------------------
    | Moods module constants
    |--------------------------------------------------------------------------
     */
    'title'    => [
        'manage' => 'Moods',
        'add'    => 'Add Moods',
        'edit'   => 'Edit Moods',
    ],
    'table'    => [
        'updated_at' => 'Updated at',
        'logo'       => 'Logo',
        'moods'      => 'Moods',
        'action'     => 'Actions',
    ],
    'buttons'  => [
        'add'      => 'Add Mood',
        'tooltips' => [
            'edit'   => 'Edit',
            'delete' => 'Delete',
        ],
    ],
    'modal'    => [
        'title'   => 'Delete Mood?',
        'message' => 'Are you sure you want to delete the type of the Mood?',
    ],
    'form'     => [
        'labels'       => [
            'logo' => 'Moods Logo',
            'name' => 'Moods Name',
        ],
        'placeholders' => [
            'choose' => 'Choose File',
            'name'   => 'Enter Name',
        ],
    ],
    'messages' => [
        'limit'                     => 'Limit to add mood types reached.',
        'created'                   => 'Moods type has been added successfully.',
        'updated'                   => 'Moods type has been updated successfully.',
        'deleted'                   => 'Moods type has been deleted successfully.',
        'something_wrong_try_again' => 'Something went wrong. please try again.',
        'unauthorized_access'       => 'You are not authorized.',
        'image_valid_error'         => 'Please try again with uploading valid image.',
        'image_size_2M_error'       => 'Maximum allowed size for uploading image is 2 mb. Please try again.',
        'upload_image_dimension'    => 'The uploaded image does not match the given dimension and ratio.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Mood Tags module constants
    |--------------------------------------------------------------------------
     */
    'tags'     => [
        'title'    => [
            'manage' => 'Tags',
            'add'    => 'Add Tags',
            'edit'   => 'Edit Tags',
        ],
        'table'    => [
            'updated_at' => 'Updated at',
            'tags'       => 'Tags',
            'action'     => 'Actions',
        ],
        'buttons'  => [
            'add'      => 'Add Tag',
            'tooltips' => [
                'edit'   => 'Edit',
                'delete' => 'Delete',
            ],
        ],
        'modal'    => [
            'title'   => 'Delete Mood Tag?',
            'message' => 'Are you sure you want to delete the tag?',
        ],
        'form'     => [
            'labels'       => [
                'name' => 'Tag Name',
            ],
            'placeholders' => [
                'name' => 'Enter Name',
            ],
        ],
        'messages' => [
            'limit'                     => 'Limit to add tags reached.',
            'created'                   => 'Moods tag has been added successfully.',
            'updated'                   => 'Moods tag has been updated successfully.',
            'deleted'                   => 'Moods tag has been deleted successfully.',
            'something_wrong_try_again' => 'Something went wrong. please try again.',
            'unauthorized_access'       => 'You are not authorized.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Moods Analysis Dashboard constants
    |--------------------------------------------------------------------------
     */
    'analysis' => [
        'title'    => [
            'dashboard' => 'Moods Dashboard',
            'users'     => 'No. of Users',
            'moods'     => 'Moods analysis',
            'tags'      => 'Tag Analysis',
        ],
        'filter'   => [
            'company'    => 'Select Company',
            'department' => 'Select Department',
            'week'       => 'Week',
            'month'      => 'Month',
            'year'       => 'Year',
        ],
        'labels'   => [
            'total'   => 'Total',
            'active'  => 'Active',
            'passive' => 'Passive',
        ],
        'messages' => [
            'unauthorized_access'       => 'You are not authorized.',
            'something_wrong_try_again' => 'Something went wrong. please try again.',
        ],
    ],
];
