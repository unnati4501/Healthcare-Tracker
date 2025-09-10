<?php

/*
|--------------------------------------------------------------------------
| Labels for survey subcategories module
|--------------------------------------------------------------------------
|
| The following language lines are used in buttons throughout the system.
| Regardless where it is placed, a button can be listed here so it is easily
| found in a intuitive way.
|
 */
return [
    'title'    => [
        'index' => 'Manage Subcategories (:category)',
        'add'   => 'Add Subcategory (:category)',
        'edit'  => 'Edit Subcategory (:category)',
    ],
    'buttons'  => [
        'add'    => 'Add Subcategory',
        'edit'   => 'Edit',
        'delete' => 'Delete',
        'view'   => 'View Subcategories',
        'remove' => 'Remove',
    ],
    'filter'   => [
        'subcategory' => 'Search By Subcategory',
        'premium'     => 'Select Premium',
    ],
    'table'    => [
        'updated_at'       => 'Updated At',
        'subcategory_name' => 'Subcategory Name',
        'questions'        => 'No. of questions',
        'premium'          => 'Premium?',
        'actions'          => 'Actions',
    ],
    'form'     => [
        'labels'      => [
            'logo'        => 'Logo',
            'subcategory' => 'Subcategory Name',
            'goal'        => 'Goal Tags',
            'premium'     => 'Premium',
        ],
        'placeholder' => [
            'subcategory' => 'Enter Subcategory Name',
            'choose_file' => 'Choose File',
            'remove_logo' => 'Click to remove logo and set to default.',
        ],
    ],
    'modal'    => [
        'delete' => [
            'title'   => 'Delete Survey Sub-category?',
            'message' => 'Are you sure you want to delete this Survey Sub-category?',
        ],
        'remove' => [
            'title'   => 'Remove :action?',
            'message' => 'Are you sure you want to remove :action?',
        ],
    ],
    'messages' => [
        'deleted'                => "Survey Subcategory deleted",
        'in_use'                 => "The survey subcategory is in use!",
        'delete_fail'            => "Failed to delete survey subcategory",
        'image_valid_error'      => 'Please try again with uploading valid image.',
        'image_size_2M_error'    => 'Maximum allowed size for uploading image is 2 mb. Please try again.',
        "upload_image_dimension" => "The uploaded image does not match the given dimension and ratio.",
    ],
];
