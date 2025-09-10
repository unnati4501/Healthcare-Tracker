<?php

/*
|--------------------------------------------------------------------------
| Labels for survey and sub modules module
|--------------------------------------------------------------------------
|
| The following language lines are used in buttons throughout the system.
| Regardless where it is placed, a button can be listed here so it is easily
| found in a intuitive way.
|
 */
return [
    'title'          => [
        'index'            => 'Surveys',
        'add'              => 'Add Survey',
        'edit'             => 'Edit Survey',
        'view'             => 'Survey preview',
        'preview_question' => 'View Questions',
    ],
    'buttons'        => [
        'add'    => 'Add Survey',
        'edit'   => 'Edit',
        'delete' => 'Delete',
    ],
    'filter'         => [

    ],
    'table'          => [
    ],
    'form'           => [
        'labels'      => [
        ],
        'placeholder' => [
        ],
    ],
    'modal'          => [
        'delete' => [
            'title'   => '',
            'message' => '',
        ],
    ],
    'messages'       => [
        'upload_image_dimension' => 'The uploaded image does not match the given dimension and ratio.',
    ],

    // review suggestions(Feedback)
    'feedback'       => [
        'title'    => [
            'index'         => 'Feedback',
            'index_message' => 'Review suggestions left by users that have completed survey',
        ],
        'filter'   => [
            'company' => 'All Companies',
            'date'    => 'Search by date',
        ],
        'labels'   => [
            'progress'   => 'Progress',
            'expired'    => 'Expired',
            'unfavorite' => 'Remove from favorite',
            'favorite'   => 'Mark as favorite',
        ],
        'tabs'     => [
            'suggestions' => 'All Suggestions',
            'favorites'   => 'Favorites',
        ],
        'table'    => [
            'sr_no'        => 'No.',
            'suggestion'   => 'Suggestion',
            'survey_name'  => 'Survey name',
            'company_name' => 'Company name',
            'publish_date' => 'Publish date',
            'status'       => 'Status',
            'action'       => 'Action',
        ],
        'messages' => [
            'fail_to_load' => 'Failed to complete action on suggestion! Please try again.',
        ],
    ],

    // Insights
    'insights'       => [
        'title'         => [
            'index'            => 'Insights',
            'index_message'    => 'Provide insights on upcoming surveys and published surveys for a given period',
            'subtitle'         => 'Published surveys',
            'subtitle_message' => 'Surveys that have recently been published and their expiry date',
            'details'          => 'Survey Insights Details',
        ],
        'filter'        => [
            'company' => 'All Companies',
            'publish' => 'Publish Date',
            'expire'  => 'Expiry Date',
        ],
        'labels'        => [
            'progress'         => 'Progress',
            'expired'          => 'Expired',
            'upcoming_date'    => 'Upcoming survey date',
            'publish_date'     => 'Publish date',
            'expiry_date'      => 'Expiry date',
            'preview_question' => 'Preview Question',
        ],
        'buttons'       => [
            'view' => 'View',
        ],
        'table'         => [
            'sr_no'             => 'No.',
            'company_name'      => 'Company name',
            'survey_title'      => 'Survey title',
            'publish_date'      => 'Publish date',
            'expiry_date'       => 'Expiry date',
            'no_of_survey_sent' => 'No. of Survey Sent',
            'responses'         => "Responses",
            'retake_response'   => 'Retake Response',
            'response_rate'     => 'Response Rate',
            'percentage'        => "Percentage",
            'status'            => 'Status',
            'view'              => 'View details',
        ],
        'insight_table' => [
            'sr_no'         => 'No.',
            'question_type' => 'Question Type',
            'question'      => 'Question',
            'category'      => 'Category',
            'sub_category'  => 'Sub Category',
            'responses'     => 'Responses',
            'options'       => 'Options',
            'percentage'    => 'Percentage',
            'action'        => 'Action',

        ],
    ],

    // HR Report
    'hr_report'      => [
        'title'           => [
            'index'     => 'HR Report details',
            'free_text' => 'Review free text questions',
        ],
        'filter'          => [
            'company' => 'Select company',
            'from'    => 'From month',
            'to'      => 'To month',
        ],
        'buttons'         => [
            'review_free_text' => 'Review free text questions',
            'back_to_report'   => 'Back to HR Report',
        ],
        'messages'        => [
            'loading'        => 'Loading details...',
            'empty_graph'    => 'No data available to plot graph.',
            'no_subcategory' => 'No data available to plot subcategory graph.',
            'no_data'        => 'No data found!',
        ],
        'free_text_table' => [
            'sr_no'   => 'No.',
            'company' => 'Company',
            'answers' => 'Answers',
        ],
    ],

    'zcquestionbank' => [
        'title'   => [
            'index' => 'Question Library',
            'add'   => 'Add Question',
            'edit'  => 'Edit Question',
        ],
        'buttons' => [
            'add'    => 'Add Questions',
            'view'   => 'View',
            'edit'   => 'Edit',
            'delete' => 'Delete',
        ],
    ],
];
