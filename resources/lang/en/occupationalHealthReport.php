<?php
return [
    // Labels for Occupational Health Report module
    'title'    => [
        'index_title' => 'Occupational Health Report',
        'filter'      => 'Filter',
        'modal-title' => 'Export Occupational Health Report',
    ],
    'filter'   => [
        'title'                         => 'Title',
        'select_company'                => 'Select Company',
        'select_wellbeing_specialist'   => 'Select Wellbeing Specialist',
        'from_date'                     => 'From Date',
        'to_date'                       => 'To Date',
        'user_name'                     => 'Client Name',
    ],
    'table'    => [
        'id'                            => 'ID',
        'user_name'                     => 'Client Name',
        'user_email'                    => 'Client Email',
        'company_name'                  => 'Company Name',
        'date_added'                    => 'Date Added',
        'confirmation_client'           => 'Confirmation Client',
        'confirmation_date'             => 'Confirmation Date',
        'note'                          => 'Note',
        'attended'                      => 'Attended',
        'wellbeing_sepecialist_name'    => 'Wellbeing Specialist Name',
        'referred_by'                   => 'Referred By',
    ],
    'buttons'  => [
        'export_to_excel' => 'Export to excel',
    ],
    'messages' => [
        'failed_to_load'            => 'Failed to load details.',
        'processing'                => 'Processing...',
        'loadinggraph'              => 'loading graph...',
        'unauthorized_access'       => 'You are not authorized.',
        'something_wrong'           => 'Something wrong',
        'email_required'            => 'This field is required',
        'something_wrong_try_again' => 'Something went wrong please try again.',
        'valid_email'               => 'Please enter a valid email address',
        'report_success'            => 'Report generation is running in the background, once it will be generated, the report will send to email',
    ],
    'modal'    => [
        'export' => [
            'message' => 'Report generation is running in background, Once it will be generated, the report will be sent to email.',
            'form'    => [
                'labels'       => [
                    'email'     => 'Email Address'
                ],
                'placeholders' => [
                    'email'     => 'Enter Email Address'
                ],
            ],
        ],
    ],
];
