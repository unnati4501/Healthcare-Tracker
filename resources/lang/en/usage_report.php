<?php
return [
    // Labels for Usage Report module
    'title'    => [
        'index_title' => 'Usage Report',
        'filter'      => 'Filter',
        'modal-title' => 'Export Usage Report',
    ],
    'filter'   => [
        'select_company'  => 'Select Company',
        'select_location' => 'Select location',
    ],
    'table'    => [
        'company_name'   => 'Company',
        'location'       => 'Location',
        'registed_user'  => 'Registered Users',
        'active_7_days'  => 'Active (Last 7 Days)',
        'active_30_days' => 'Active (Last 30 Days)',
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
        'no_records_found'          => 'No Record Found!',
    ],
    'modal'    => [
        'export' => [
            'message' => 'Report generation is running in background, Once it will be generated, the report will be sent to email.',
            'form'    => [
                'labels'       => [
                    'email' => 'Email Address',
                ],
                'placeholders' => [
                    'email' => 'Enter Email Address',
                ],
            ],
        ],
    ],
];
