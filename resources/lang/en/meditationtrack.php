<?php

/*
|--------------------------------------------------------------------------
| Labels for meditationtrack module
|--------------------------------------------------------------------------
|
| The following language lines are used in buttons throughout the system.
| Regardless where it is placed, a button can be listed here so it is easily
| found in a intuitive way.
|
 */
return [
    'title'               => [
        'index'      => 'Meditations',
        'add'        => 'Add Meditation',
        'edit'       => 'Edit Meditation',
        'visibility' => 'Company Visibility',
    ],
    'buttons'             => [
        'add'    => 'Add Meditation',
        'edit'   => 'Edit',
        'delete' => 'Delete',
        'listen' => 'Listen',
        'watch'  => 'Watch',
    ],
    'filter'              => [
        'name'       => 'Search By Name',
        'coach'      => 'Search by Author Name',
        'category'   => 'Select Category',
        'track_type' => 'Select Track Type',
        'tag'        => 'Select Category Tag',
    ],
    'table'               => [
        'updated_at'         => 'Updated At',
        'cover'              => 'Track Cover',
        'track'              => 'Track',
        'visible_to_company' => 'Visible to Company',
        'duration'           => 'Duration (Seconds)',
        'title'              => 'Track Name',
        'subcategory_name'   => 'Track Category',
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
            'title'             => 'Track Name',
            'subcategory_name'  => 'Track Category',
            'track_type'        => 'Track Type',
            'cover'             => 'Track Cover',
            'background'        => 'Track background (Mobile)',
            'background_portal' => 'Track background (Portal)',
            'track_file'        => 'Track File',
            'youtube'           => 'YouTube Link',
            'vimeo'             => 'Vimeo',
            'duration'          => 'Duration (Seconds)',
            'health_coach'      => 'Author',
            'goal_tag'          => 'Goal Tags',
            'audio_type'        => 'Audio type',
            'music'             => 'Music',
            'vocal'             => 'Vocal',
            'tag'               => 'Category Tag',
            'header_image'      => 'Header Image',
        ],
        'placeholder' => [
            'choose_file'       => 'Choose File',
            'name'              => 'Enter Track Name',
            'track_subcategory' => 'Select Category',
            'track_type'        => 'Select track type',
            'vimeo'             => 'Enter vimeo url',
            'duration'          => 'Track duration',
            'health_coach'      => 'Select Author',
            'goal_tag'          => 'Select Goal Tags',
            'tag'               => 'Select Category Tag',
        ],
    ],
    'modal'               => [
        'delete'          => [
            'title'   => 'Delete Meditation Track?',
            'message' => 'Are you sure you want to delete this Meditation Track?',
        ],
        'visible_company' => [
            'title' => 'Visible to Company',
        ],
    ],
    'messages'            => [
        'deleted'                            => "Meditation Track has been deleted successfully!",
        'delete_fail'                        => "Failed to delete meditation track.",
        'image_valid_error'                  => 'Please try again with uploading valid image.',
        'image_size_2M_error'                => 'Maximum allowed size for uploading image is 2 mb. Please try again.',
        'video_valid_error'                  => 'The video must be a file of type: mp4.',
        'meditation_track_audio_valid_error' => 'The Audio must be a file of type: m4a, mp3.',
        'video_size_100M_error'              => 'Maximum allowed size for uploading video is 100 mb. Please try again.',
        'audio_size_100M_error'              => 'Maximum allowed size for uploading audio is 100 mb. Please try again.',
        'something_wrong_try_again'          => 'Something went wrong please try again.',
        'uploading_media'                    => 'Uploading media....',
        'processing_media'                   => 'Processing on media...',
        'audio'                              => 'Audio',
        'video'                              => 'Video',
        'youtube'                            => 'Youtube',
        'vimeo'                              => 'Vimeo',
        'upload_image_dimension'             => 'The uploaded image does not match the given dimension and ratio.',
    ],
];
