<?php

/*
|--------------------------------------------------------------------------
| Labels for podcast module
|--------------------------------------------------------------------------
|
| The following language lines are used in buttons throughout the system.
| Regardless where it is placed, a button can be listed here so it is easily
| found in a intuitive way.
|
 */
return [
    'title'               => [
        'index'      => 'Podcasts',
        'add'        => 'Add Podcast',
        'edit'       => 'Edit Podcast',
        'visibility' => 'Company Visibility',
    ],
    'buttons'             => [
        'add'    => 'Add Podcast',
        'edit'   => 'Edit',
        'delete' => 'Delete',
        'listen' => 'Listen',
        'watch'  => 'Watch',
    ],
    'filter'              => [
        'name'       => 'Search By Name',
        'coach'      => 'Search by Author Name',
        'category'   => 'Select Category',
        'tag'        => 'Select Category Tag',
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
        'health_coach'       => 'Author',
        'created_at'         => 'Created At',
        'total_likes'        => 'Total Likes',
        'view_count'         => 'View Counts',
        'actions'            => 'Actions',
    ],
    'visible_company_tbl' => [
        'no'         => 'No.',
        'group_type' => 'Group type',
        'company'    => 'Company',
    ],
    'form'                => [
        'labels'      => [
            'title'             => 'Name',
            'subcategory_name'  => 'Category',
            'track_file'        => 'Track File',
            'duration'          => 'Duration (Seconds)',
            'health_coach'      => 'Author',
            'goal_tag'          => 'Goal Tags',
            'tag'               => 'Category Tag',
            'logo'              => 'Logo',
        ],
        'placeholder' => [
            'choose_file'           => 'Choose File',
            'name'                  => 'Enter Name',
            'podcast_subcategory'   => 'Select Category',
            'duration'              => 'Duration',
            'health_coach'          => 'Select Author',
            'goal_tag'              => 'Select Goal Tags',
            'tag'                   => 'Select Category Tag',
        ],
        'tooltip' => [
            'track'  => 'You can upload either an MP3 or M4A file. Maximum file size can be 100 MB',
        ],
    ],
    'modal'               => [
        'delete'          => [
            'title'   => 'Delete Podcast?',
            'message' => 'Are you sure you want to delete this podcast?',
        ],
        'visible_company' => [
            'title' => 'Visible to Company',
        ],
    ],
    'messages'            => [
        'deleted'                            => "Podcast has been deleted successfully!",
        'delete_fail'                        => "Failed to delete podcast.",
        'image_valid_error'                  => 'Please try again with uploading valid image.',
        'image_size_2M_error'                => 'Maximum allowed size for uploading image is 2 mb. Please try again.',
        'podcast_audio_valid_error'          => 'The Audio must be a file of type: m4a, mp3.',
        'audio_size_100M_error'              => 'Maximum allowed size for uploading audio is 100 mb. Please try again.',
        'something_wrong_try_again'          => 'Something went wrong please try again.',
        'uploading_media'                    => 'Uploading media....',
        'processing_media'                   => 'Processing on media...',
        'audio'                              => 'Audio',
        'upload_image_dimension'             => 'The uploaded image does not match the given dimension and ratio.',
    ],
];
