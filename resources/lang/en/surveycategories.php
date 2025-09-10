<?php

/*
|--------------------------------------------------------------------------
| Labels for survey categories module
|--------------------------------------------------------------------------
|
| The following language lines are used in buttons throughout the system.
| Regardless where it is placed, a button can be listed here so it is easily
| found in a intuitive way.
|
 */
return [
    'title'    => [
        'index' => 'Survey Categories',
        'add'   => 'Add Category',
        'edit'  => 'Edit Category',
    ],
    'buttons'  => [
        'add'    => 'Add Category',
        'edit'   => 'Edit',
        'delete' => 'Delete',
        'view'   => 'View Subcategories',
        'remove' => 'Remove',
    ],
    'filter'   => [
        'category' => 'Search By Category',
    ],
    'table'    => [
        'updated_at'    => 'Updated At',
        'category_name' => 'Category Name',
        'total_sub_cat' => 'Total Subcategory',
        'actions'       => 'Actions',
    ],
    'form'     => [
        'labels'      => [
            'logo'     => 'Logo',
            'category' => 'Category Name',
            'goal'     => 'Goal Tags',
        ],
        'placeholder' => [
            'category'    => 'Enter Category Name',
            'goal'        => 'Select Goal Tags',
            'choose_file' => 'Choose File',
            'remove_logo' => 'Click to remove logo and set to default.',
        ],
    ],
    'modal'    => [
        'delete' => [
            'title'   => 'Delete Survey Category?',
            'message' => 'Are you sure you want to delete this Survey Category?',
        ],
        'remove' => [
            'title'   => 'Remove :action?',
            'message' => 'Are you sure you want to remove :action?',
        ],
    ],
    'messages' => [
        'deleted'                => "Survey Category deleted",
        'in_use'                 => "The survey category is in use!",
        'delete_fail'            => "Failed to delete survey category",
        'image_valid_error'      => 'Please try again with uploading valid image.',
        'image_size_2M_error'    => 'Maximum allowed size for uploading image is 2 mb. Please try again.',
        "upload_image_dimension" => "The uploaded image does not match the given dimension and ratio.",
    ],
];
