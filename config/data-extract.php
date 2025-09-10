<?php

return [
    'DO_SPACES_KEY'          => env('DATA_EXTRACT_DO_SPACES_KEY', ''),
    'DO_SPACES_SECRET'       => env('DATA_EXTRACT_DO_SPACES_SECRET', ''),
    'DO_SPACES_BUCKET'       => env('DATA_EXTRACT_DO_SPACES_BUCKET', ''),
    'DO_SPACES_DOMAIN'       => env('DATA_EXTRACT_DO_SPACES_DOMAIN', ''),
    // Folder path on Digital Ocean
    'excelfolderpath'        => env('DATA_EXTRACT_DO_SPACES_FOLDER_NAME', ''), // irishlife_data_extract/
    // Irishlife data extract
    'irishlife_data_extract' => [
        'company_code'              => [
            'local'      => [
                '319265',
            ],
            'dev'        => [
                '215477',
            ],
            'qa'         => [
                '319265',
            ],
            'uat'        => [
                '207589',
            ],
            'production' => [
                '279751',
            ],
        ],
        'emails'                    => [
            'local'      => [
                'test@yopmail.com',
            ],
            'dev'        => [
                'test@yopmail.com',            
            ],
            'qa'         => [
                'test@yopmail.com',           
            ]
        ],
        'email'                     => 'test@yopmail.com',
        'registered_platform_users' => [
            'sub_category' => [
                'all'   => 'Overall',
                '0-17'  => '0-17 Age Group',
                '18-29' => '18-29 Age Group',
            ],
            'field'        => [
                'male'   => 'Male',
                'female' => 'Female',
                'none'   => 'Prefer not to say',
                'other'  => 'Other',
            ],
        ],
        'active_user'               => [
            'sub_category' => [
                7  => '7 Days',
                30 => '30 Days',
            ],
            'field'        => [
                'male'   => 'Male',
                'female' => 'Female',
                'none'   => 'Prefer not to say',
                'other'  => 'Other',
            ],
        ],
        'masterclass'               => [
            'field' => [
                'male'   => 'Male',
                'female' => 'Female',
                'none'   => 'Prefer not to say',
                'other'  => 'Other',
            ],
        ],
        'wellbeing_survey'          => [
            'sub_category' => [
                'all'      => 'Overall',
                '0-17'     => '0-17 Age Group',
                '18-29'    => '18-29 Age Group',
                'response' => 'Responses',
            ],
            'field'        => [
                'male'   => 'Male',
                'female' => 'Female',
                'none'   => 'Prefer not to say',
                'other'  => 'Other',
            ],
        ],
        'heading'                   => [
            'extract'          => [
                'Company Code',
                'Category',
                'Sub Category',
                'Field',
                'Value',
                'Type',
            ],
            'question_extract' => [
                'Survey Title',
                'Survey ID',
                'Company Code',
                'Category',
                'Sub Category',
                'Question Type',
                'Question ID',
                'Question',
                'Index',
                'Response',
                'Answer1',
                'Answer2',
                'Answer3',
                'Answer4',
                'Answer5',
                'Answer6',
                'Answer7',
                'Score',
            ],
            'booking_extract'  => [
                'Event Title',
                'Company Code',
                'Event ID',
                'Category',
                'Status',
                'Date Booked',
                'Date Held',
                'Average Age',
                'Male',
                'Female',
                'Prefer not to say',
                'Other',
            ],
        ],
    ],
];
