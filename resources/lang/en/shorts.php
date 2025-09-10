<?php

/*
|--------------------------------------------------------------------------
| Labels for shorts module
|--------------------------------------------------------------------------
|
| The following language lines are used in buttons throughout the system.
| Regardless where it is placed, a button can be listed here so it is easily
| found in a intuitive way.
|
 */
return [
    'title'               => [
        'index'      => 'Shorts',
        'add'        => 'Add Shorts',
        'edit'       => 'Edit Shorts',
        'visibility' => 'Company Visibility',
    ],
    'buttons'             => [
        'add'    => 'Add Shorts',
        'edit'   => 'Edit',
        'delete' => 'Delete',
        'listen' => 'Listen',
        'watch'  => 'Watch',
    ],
    'filter'              => [
        'name'                  => 'Search By Name',
        'author'                => 'Search by Author Name',
        'category'              => 'Select Category',
        'tag'                   => 'Select Category Tag',
        'select_shorts_type'    => 'Select Shorts Type',
    ],
    'table'               => [
        'updated_at'         => 'Updated At',
        'logo'               => 'Logo',
        'track'              => 'Track',
        'visible_to_company' => 'Visible to Company',
        'duration'           => 'Duration (Seconds)',
        'title'              => 'Name',
        'subcategory_name'   => 'Category',
        'tag'                => 'Category Tag',
        'author'             => 'Author',
        'created_at'         => 'Created At',
        'total_likes'        => 'Total Likes',
        'view_count'         => 'View Counts',
        'actions'            => 'Actions',
        'watch_video'        => 'Watch',
    ],
    'visible_company_tbl' => [
        'no'         => 'No.',
        'group_type' => 'Group type',
        'company'    => 'Company',
    ],
    'form'                => [
        'labels'      => [
            'title'             => 'Name',
            'category_name'     => 'Category',
            'track_file'        => 'Track File',
            'duration'          => 'Duration (Seconds)',
            'author'            => 'Author',
            'goal_tag'          => 'Goal Tags',
            'tag'               => 'Category Tag',
            'header_image'      => 'Header Image',
            'short_type'        => 'Short Type',
            'youtube'           => 'Youtube Link',
            'video'             => 'Video',
            'vimeo'             => 'Vimeo',
            'description'       => 'Description'
        ],
        'placeholder' => [
            'choose_file'           => 'Choose File',
            'name'                  => 'Enter Name',
            'shorts_category'       => 'Select Category',
            'duration'              => 'Duration',
            'author'                => 'Select Author',
            'goal_tag'              => 'Select Goal Tags',
            'tag'                   => 'Select Category Tag',
            'enter_vimeo_url'       => 'Enter Vimeo Url',
        ],
        'tooltip' => [
            'track'  => 'You can upload either an MP3 or M4A file. Maximum file size can be 100 MB',
        ],
    ],
    'modal'               => [
        'delete'          => [
            'title'   => 'Delete shorts?',
            'message' => 'Are you sure you want to delete this shorts?',
        ],
        'visible_company' => [
            'title' => 'Visible to Company',
        ],
    ],
    'message'            => [
        'data_store_success'                 => 'Short has been added successfully!',
        'data_update_success'                => "Short has been updated successfully!",
        'deleted'                            => "Short has been deleted successfully!",
        'delete_fail'                        => "Failed to delete short.",
        'image_valid_error'                  => 'Please try again with uploading valid image.',
        'image_size_2M_error'                => 'Maximum allowed size for uploading image is 2 mb. Please try again.',
        'video_valid_error'                  => 'The video must be a file of type mp4.',
        'video_size_100M_error'              => 'Maximum allowed size for uploading video is 100 mb. Please try again.',
        'something_wrong_try_again'          => 'Something went wrong please try again.',
        'uploading_media'                    => 'Uploading media....',
        'processing_media'                   => 'Processing on media...',
        'upload_image_dimension'             => 'The uploaded image does not match the given dimension and ratio.',
        'unauthorized_access'                => 'You are not authorized.',
        'no_shorts_found'                    => 'No results',
    ],
    'validation' => [
        'company_selection'     => 'The company selection is required',
        'description_required'  => 'The description field is required.',
        'description_max'       => 'The description may not be greater than 500 characters.',
        'valid_vimeo_url'       => 'The vimeo link is not valid url',
    ],
];
