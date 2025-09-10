<?php

/*
|--------------------------------------------------------------------------
| Labels for masterclass module
|--------------------------------------------------------------------------
|
| The following language lines are used in buttons throughout the system.
| Regardless where it is placed, a button can be listed here so it is easily
| found in a intuitive way.
|
 */
return [
    'title'               => [
        'index' => 'Masterclasses',
        'add'   => 'Add Masterclass',
        'edit'  => 'Edit Masterclass',
    ],
    'buttons'             => [
        'add'       => 'Add Masterclass',
        'view'      => 'View',
        'edit'      => 'Edit',
        'delete'    => 'Delete',
        'unpublish' => 'Unpublish',
        'publish'   => 'Publish',
        'published' => 'Published',
        'lessons'   => 'Lessons',
    ],
    'filter'              => [
        'title'    => 'Search By Title',
        'coach'    => 'Search By Author Name',
        'category' => 'Select Category',
        'tag'      => 'Select Category Tag',
    ],
    'table'               => [
        'updated_at'         => 'Updated At',
        'title'              => 'Title',
        'sub_category_name'  => 'Category',
        'author'             => 'Author',
        'visible_to_company' => 'Visible to Company',
        'category_tag'       => 'Category Tag',
        'members'            => 'Total Members',
        'lessons'            => 'Total Lessons',
        'durations'          => 'Total Durations',
        'status'             => 'Status',
        'total_likes'        => 'Total Likes',
        'actions'            => 'Actions',
    ],
    'visible_company_tbl' => [
        'no'         => 'No.',
        'group_type' => 'Group type',
        'company'    => 'Company',
    ],
    'form'                => [
        'labels'      => [
            'logo'                    => 'Logo',
            'category'                => 'Category',
            'title'                   => 'Title',
            'author'                  => 'Author',
            'has_trailer'             => 'Trailer preview',
            'goal_tag'                => 'Goal Tags',
            'tag'                     => 'Category Tag',
            'audio'                   => 'Audio',
            'audio_background'        => 'Audio background (Mobile)',
            'audio_background_portal' => 'Audio background (Portal)',
            'vimeo_background'        => 'Vimeo thumbnail',
            'video'                   => 'Video',
            'trailer_type'            => 'Trailer Type',
            'youtube'                 => 'Youtube Link',
            'vimeo'                   => 'Vimeo',
            'company_visibility'      => 'Company Visibility',
            'description'             => 'Description',
            'header_image'            => 'Header Image',
        ],
        'placeholder' => [
            'category'     => 'Select Category',
            'title'        => 'Title',
            'author'       => 'Author',
            'goal_tag'     => 'Select Goal Tags',
            'trailer_type' => 'Select trailer type',
            'vimeo'        => 'Enter vimeo URL',
            'choose-file'  => 'Choose File',
            'tag'          => 'Select Category Tag',
        ],
    ],
    'modal'               => [
        'delete'          => [
            'title'   => 'Delete Masterclass?',
            'message' => 'Are you sure you want to delete this Masterclass?',
        ],
        'publish'         => [
            'title'   => 'Publish Masterclass?',
            'message' => 'Are you sure, you want to publish the Masterclass?',
        ],
        'unpublish'       => [
            'title'   => 'Unpublish Masterclass?',
            'message' => 'Are you sure, you want to unpublish the Masterclass?',
        ],
        'visible_company' => [
            'title' => 'Visible to Company',
        ],
    ],
    'messages'            => [
        'deleted'                            => 'Masterclass has deleted successfully.',
        'in_use'                             => 'The masterclass is in use!',
        'delete_fail'                        => 'Unable to delete masterclass data.',
        'failed_action'                      => 'Failed to :action masterclass.',
        'image_valid_error'                  => 'Please try again with uploading valid image.',
        'image_size_2M_error'                => 'Maximum allowed size for uploading image is 2 mb. Please try again.',
        'video_valid_error'                  => 'The video must be a file of type: mp4.',
        'meditation_track_audio_valid_error' => 'The Audio must be a file of type: m4a, mp3.',
        'video_size_100M_error'              => 'Maximum allowed size for uploading video is 100 mb. Please try again.',
        'audio_size_100M_error'              => 'Maximum allowed size for uploading audio is 100 mb. Please try again.',
        'something_wrong_try_again'          => 'Something went wrong please try again.',
        'uploading_media'                    => 'Uploading media....',
        'processing_media'                   => 'Processing on media...',
        'companies_remove'                   => "Company(s) can't be removed whose users are associated with the Masterclass.",
        'company_remove'                     => "Company can't be removed whose users are associated with the Masterclass.",
        "upload_image_dimension"             => "The uploaded image does not match the given dimension and ratio.",
    ],
    'validation'          => [
        'description_required' => 'The description field is required.',
        'description_max'      => 'The description may not be greater than 500 characters.',
    ],

    // lessons
    'lesson'              => [
        'title'    => [
            'index' => 'Lessons of :masterclass',
            'add'   => 'Add Lesson',
            'edit'  => 'Edit Lesson',
        ],
        'buttons'  => [
            'add'           => 'Add Lesson',
            'add_survey'    => 'Add survey',
            'remove_survey' => 'Delete survey',
            'view'          => 'View',
            'edit'          => 'Edit',
            'delete'        => 'Delete',
            'publish'       => 'Publish',
            'published'     => 'Published',
            'unpublish'     => 'Unpublish',
        ],
        'tooltip'  => [
            'publish' => 'Publish masterclass first to publish a lesson',
        ],
        'filter'   => [
            'title' => 'Search By Course Lesson Title',
        ],
        'table'    => [
            'order'    => 'Order',
            'id'       => 'Id',
            'title'    => 'Title',
            'duration' => 'Duration',
            'status'   => 'Status',
            'action'   => 'Actions',
        ],
        'modal'    => [
            'delete'  => [
                'title'   => 'Delete Lesson?',
                'message' => 'Are you sure you want to delete this lesson?',
            ],
            'publish' => [
                'title'   => 'Publish Lesson?',
                'message' => 'Are you sure, you want to publish this lesson?',
            ],
        ],
        'form'     => [
            'labels'      => [
                'title'                   => 'Title',
                'auto_progress'           => 'Auto progress',
                'lesson_type'             => 'Lesson Type',
                'audio'                   => 'Audio',
                'audio_background'        => 'Audio background (Mobile)',
                'audio_background_portal' => 'Audio background (Portal)',
                'video'                   => 'Video',
                'youtube'                 => 'Youtube Link',
                'vimeo'                   => 'Vimeo Link',
                'duration'                => 'Duration',
                'content'                 => 'Content',
                'choose_file'             => 'Choose File',
                'logo'                    => 'Logo',
            ],
            'placeholder' => [
                'title'       => 'Enter Title',
                'lesson_type' => 'Select lesson type',
                'choose_file' => 'Choose File',
                'minutes'     => 'Minutes',
                'vimeo'       => 'Enter vimeo URL',
                'choose_file' => 'Choose File'
            ],
        ],
        'messages' => [
            'image_valid_error'                  => 'Please try again with uploading valid image.',
            'image_size_2M_error'                => 'Maximum allowed size for uploading image is 2 mb. Please try again.',
            'video_valid_error'                  => 'The video must be a file of type: mp4.',
            'meditation_track_audio_valid_error' => 'The Audio must be a file of type: m4a, mp3.',
            'video_size_100M_error'              => 'Maximum allowed size for uploading video is 100 mb. Please try again.',
            'audio_size_100M_error'              => 'Maximum allowed size for uploading audio is 100 mb. Please try again.',
            'something_wrong_try_again'          => 'Something went wrong please try again.',
            'uploading_media'                    => 'Uploading media....',
            'processing_media'                   => 'Processing on media...',
            'video_length_limit'                 => 'Video must be at least 1 minute long.',
            'audio_length_limit'                 => 'Audio must be at least 1 minute long.',
            'deleted'                            => "Lesson has been deleted successfully.",
            'delete_fail'                        => "Failed to delete lesson.",
            'lession_publish_failed'             => "Failed to publish lesson.",
            'delete_fail_survey'                 => "Failed to delete surveys.",
            "upload_image_dimension"             => "The uploaded image does not match the given dimension and ratio.",
        ],
    ],

    // survey
    'survey'              => [
        'title'    => [
            'index' => 'Surveys of :masterclass',
            'edit'  => 'Edit survey(:type)',
        ],
        'buttons'  => [
            'add_survey'    => 'Add survey',
            'remove_survey' => 'Delete survey',
            'view'          => 'View',
            'edit'          => 'Edit',
            'delete'        => 'Delete',
            'publish'       => 'Publish',
            'published'     => 'Published',
            'unpublish'     => 'Unpublish',
            'add_op'        => 'Add Option',
            'delete_op'     => 'Delete Option',
            'add_question'  => 'Add Question',
        ],
        'table'    => [
            'updated_at'  => 'Updated At',
            'survey_type' => 'Survey type',
            'title'       => 'Title',
            'status'      => 'Status',
            'action'      => 'Action',
        ],
        'modal'    => [
            'delete'          => [
                'title'   => 'Delete Survey?',
                'message' => 'Are you sure you want to delete both surveys?',
            ],
            'delete_question' => [
                'title'   => 'Delete question?',
                'message' => 'Are you sure you want to delete question?',
            ],
        ],
        'tooltip'  => [
            'publish' => 'Publish masterclass first to publish a lesson',
        ],
        'form'     => [
            'labels'      => [
                'option'               => 'Option',
                'survey_question_logo' => 'Logo',
            ],
            'placeholder' => [
                'title'       => 'Enter Title',
                'question'    => 'Type your quesiton here',
                'score'       => 'Score',
                'option'      => 'Option',
                'choose_file' => 'Choose File',
            ],
        ],
        'messages' => [
            'image_valid_error'      => 'Please try again with uploading valid image.',
            'image_size_2M_error'    => 'Maximum allowed size for uploading image is 2 mb. Please try again.',
            'min_options'            => 'Question must have at least 2 options.',
            'max_options'            => 'Maximum 5 questions can be added to the survey!',
            'min_questions'          => 'At least 1 question should be there!',
            'max_questions'          => 'Maximum 7 options can be added to the question!',
            "upload_image_dimension" => "The uploaded image does not match the given dimension and ratio.",
        ],
    ],

    'view'                => [
        'labels'   => [
            'pre_survey'  => 'Pre survey',
            'lessons'     => 'Lessons',
            'post_survey' => 'Post survey',
            'view_lesson' => 'Lesson (:current of :total)',
        ],
        'tooltip'  => [
            'close_preview' => 'Close preview',
        ],
        'buttons'  => [
            'begin'         => 'Begin',
            'edit_lesson'   => 'Edit Lesson',
            'revisit'       => 'Revisit',
            'preSurvey'     => [
                "next"     => 'Next question',
                "previous" => 'Previous question',
                "finish"   => "Begin lesson",
                "cancel"   => "Manage Lessons",
            ],
            'surveyLessons' => [
                "next"     => 'Next lesson',
                "previous" => 'Previous lesson',
                "finish"   => "Start post survey",
                "cancel"   => "Manage Lessons",

            ],
            'postSurvey'    => [
                'next'     => 'Next question',
                'previous' => 'Previous question',
                'cancel'   => "Manage Lessons",
            ],
        ],
        'messages' => [
            'no_pre_survey'  => 'No pre survey questions are added, Please add to view!',
            'no_lessons'     => 'No lessons are added, Please add to view!',
            'no_post_survey' => 'No post survey questions are added, Please add to view!',
        ],
        'modals'   => [
            'preview_ended' => [
                'message' => "Master class preview has ended, tap on 'Revisit' to view preview again.",
            ],
        ],
    ],
];
