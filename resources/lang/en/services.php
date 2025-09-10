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
        'index'     => 'Services',
        'create'    => 'Add Service',
        'edit'      => 'Edit Service',
    ],
    'title'         => [
        'manage'    => 'Services',
        'add'       => 'Add Service',
        'edit'      => 'Edit Service'
    ],
    'table'         => [
        'updated_at'                        => 'Updated at',
        'name'                              => 'Service Name',
        'total'                             => 'Sub Categories',
        'wellbeing_specialist'              => 'Wellbeing Specialists',
        'action'                            => 'Action',
        'visible_to_services'               => 'Sub Categories',
        'no'                                => 'No',
        'subcategory'                       => 'Sub-Category',
        'visible_to_wellbeing_specialist'   => "Wellbeing Specialists",
        'user'                              => "Wellbeing Specialist",
        'service_type'                      => 'Service Type'
    ],
    'buttons'       => [
        'add'      => 'Add Service',
        'tooltips' => [
            'view' => 'View',
        ],
        'add_subcategories'     => "Add Sub-Category",
        'delete_subcategory'    => "Archive Sub-Category",
        'edit_subcategory'      => "Edit Sub-Category",
    ],
    'modal'       => [
        'add_subcategory'               => 'Add Sub-Category',
        'title'                         => 'Archive Service?',
        'message'                       => 'Are you sure you want to archive this Service?',
        'edit_subcategory'              => 'Edit Sub-Category',
        'delete_subcategory'            => 'Archive Sub-category',
        'delete_subcategory_message'    => 'Are you sure you want to archive this sub Category?'
    ],
    'messages'    => [
        'something_wrong_try_again' => 'Something went wrong. please try again.',
        'limit_20'                  => '20 subcategories have added, not allowed to add more.',
        'limit_10'                  => '10 subcategories have added, not allowed to add more.',
        'added'                     => 'Service has been added successfully!',
        'updated'                   => 'Service has been updated successfully!',
        'deleted'                   => 'Service has been archived successfully!',
        'in_use'                    => 'This Service is assigned to a Wellbeing Specialist. Please remove it before archiving!',
        'unauthorized'              => 'This action is unauthorized.',
        'image_size_2M_error'       => 'Maximum allowed size for uploading image is 2 mb. Please try again.',
        'image_valid_error'         => 'Please try again with uploading valid image.',
        'upload_image_dimension'    => 'The uploaded image does not match the given dimension and ratio.',
        'enter_valid_subcategory'   => 'Please enter valid sub-category name',
        'field_required'    => 'This field is required',
    ],
    'form'        => [
        'labels'       => [
            'service'               => 'Service Name',
            'service_sub_category'  => 'Sub-category Name',
            'logo'                  => 'Logo',
            'icon'                  => 'Icon',
            'sub_categories'        => 'Sub-Categories',
            'description'           => 'Description',
            'service_type'          => 'Service Type',
            'session_duration'      => 'Session Duration',
            'is_counselling'        => 'Is Counselling',
        ],
        'placeholders' => [
            'service'      => 'Enter Service Name',
            'choose_file'  => 'Choose File',
            'description'  => 'Enter Description',
        ],
        'tooltips' => [
            'session_duration' => 'How many minutes a session may last?',
        ],
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
            'logo'       => 'Logo',
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
                'edit'      => 'Edit',
                'delete'    => 'Delete',
                'archive'   => 'Archive',
            ],
        ],
        'modal'       => [
            'title'   => 'Delete Service?',
            'message' => 'Are you sure you want to delete this Service?',
        ],
        'messages'    => [
            'something_wrong_try_again' => 'Something went wrong. please try again.',
            'limit_20'                  => '20 subcategories have added, not allowed to add more.',
            'limit_10'                  => '10 subcategories have added, not allowed to add more.',
            'added'                     => 'Service has been added successfully!',
            'updated'                   => 'Service has been updated successfully!',
            'deleted'                   => 'Service has been archived successfully!',
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
