<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Categories Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during categories for various
    | messages that we need to display to the user.
    |
     */

    /*
    |--------------------------------------------------------------------------
    | Category page constants
    |--------------------------------------------------------------------------
     */
    'breadcrumbs'   => [
        'index' => 'Master Category',
    ],
    'title'         => [
        'manage' => 'Master Category',
    ],
    'table'         => [
        'updated_at' => 'Updated at',
        'name'       => 'Category Name',
        'total'      => 'Total Sub-categories',
        'action'     => 'Action',
    ],
    'buttons'       => [
        'add'      => 'Add Sub-category',
        'tooltips' => [
            'view' => 'View',
        ],
    ],
    'messages'      => [
        'something_wrong_try_again' => 'Something went wrong. please try again.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Sub-category page constants
    |--------------------------------------------------------------------------
     */
    'subcategories' => [
        'breadcrumbs' => [
            'index'  => 'Sub-category',
            'create' => 'Add Sub-category',
            'edit'   => 'Edit Sub-category',
        ],
        'title'       => [
            'manage' => 'Sub-category (:category)',
            'add'    => 'Add Sub-category',
            'edit'   => 'Edit Sub-category',
        ],
        'table'       => [
            'updated_at' => 'Updated at',
            'name'       => 'Sub-category Name',
            'action'     => 'Actions',
        ],
        'form'        => [
            'labels'       => [
                'category'         => 'Category',
                'sub_category'     => 'Sub-category Name',
                'background_image' => 'Background image',
                'logo'             => 'Logo'
            ],
            'placeholders' => [
                'category'     => 'Select Category',
                'sub_category' => 'Enter Sub-category Name',
                'choose_file'  => 'Choose File',
            ],
        ],
        'buttons'     => [
            'tooltips' => [
                'edit'   => 'Edit',
                'delete' => 'Delete',
            ],
        ],
        'modal'       => [
            'title'   => 'Delete Sub-category?',
            'message' => 'Are you sure you want to delete this Sub-category?',
        ],
        'messages'    => [
            'something_wrong_try_again' => 'Something went wrong. please try again.',
            'limit_20'                  => '20 subcategories have added, not allowed to add more.',
            'limit_10'                  => '10 subcategories have added, not allowed to add more.',
            'added'                     => 'Sub-category has been added successfully!',
            'updated'                   => 'Sub-category has been updated successfully!',
            'deleted'                   => 'Sub-category has been deleted successfully!',
            'in_use'                    => 'The Sub-category is in use!',
            'unauthorized'              => 'This action is unauthorized.',
            'image_size_2M_error'       => 'Maximum allowed size for uploading image is 2 mb. Please try again.',
            'image_valid_error'         => 'Please try again with uploading valid image.',
            'upload_image_dimension'    => 'The uploaded image does not match the given dimension and ratio.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | category tags page constants
    |--------------------------------------------------------------------------
     */
    'tags'          => [
        'title'    => [
            'index'  => 'Category Tags',
            'create' => 'Add Category Tag',
            'view'   => 'View Category Tags (:category)',
        ],
        'table'    => [
            'name'       => 'Name',
            'total_tags' => 'Total tags',
            'action'     => 'Action',
        ],
        'buttons'  => [
            'add_tag' => 'Add Category Tag',
        ],
        'form'     => [
            'labels'      => [
                'category' => 'Category',
                'name'     => 'Tag Name',
            ],
            'placeholder' => [
                'category' => 'Select Category',
                'name'     => 'Tag Name',
            ],
        ],
        'messages' => [
            'something_wrong_try_again' => 'Something went wrong. please try again.',
            'limit_15'                  => '15 Category Tags have added, not allow to add more.',
            'added'                     => 'Tag has been added successfully!',
            'updated'                   => 'Tag has been updated successfully!',
            'deleted'                   => 'Tag has been deleted successfully!',
            'in_use'                    => 'The Tag is in use!',
            'unauthorized'              => 'This action is unauthorized.',
        ],
        'view'     => [
            'table'  => [
                'tag_name'       => 'Category Tag Name',
                'mapped_content' => 'Total Mapped Content',
                'actions'        => 'Actions',
            ],
            'modals' => [
                'delete' => [
                    'title'   => 'Delete Category Tag',
                    'message' => 'Are you sure you want to delete category tag?',
                ],
            ],
        ],
    ],
];
