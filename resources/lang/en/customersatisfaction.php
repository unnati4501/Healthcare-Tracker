<?php

return [
    // Labels for Customer Satisfaction module
    'title'         => [
        'index_title'                     => 'Department Management',
        'nps_feedback'                    => 'Customer Satisfaction',
        'app'                             => 'App',
        'portal'                          => 'Portal',
        'project'                         => 'Project',
        'filter'                          => 'Filter',
        'project_survey_experience_score' => 'Project Survey Experience Score',
        'portal_experience_score'         => 'Portal experience score',
        'mobile_app_experience_score'     => 'Mobile App Experience Score',
    ],
    'filter'        => [
        'select_company'         => 'Select Company',
        'select_feedback_type'   => 'Select FeedBack Type',
        'select_status'          => 'Select Status',
        'search_by_project'      => 'Search By Project',
        'select_from_start_date' => 'Select start date',
        'select_from_end_date'   => 'Select end date',
    ],
    'table'         => [
        'id'            => 'Id',
        'updated_at'    => 'Updated At',
        'company_name'  => 'Company name',
        'total_teams'   => 'Total teams',
        'total_users'   => 'Total users',
        'points'        => 'Points',
        'team_name'     => 'Team name',
        'logo'          => 'Logo',
        'feedback_type' => 'Feedback Type',
        'notes'         => 'Notes',
        'date'          => 'Date',
        'action'        => 'Actions',
        'name'          => 'Name',
        'name'          => "Project Name",
        'type'          => "Project Type",
        'start_date'    => "Start Date",
        'end_date'      => "End Date",
        'responses'     => "Responses",
        'status'        => "Status",
    ],
    'buttons'       => [
        'add_project'     => 'Add Project',
        'export_to_excel' => 'Export to excel',
    ],
    'form'          => [
        'labels'      => [
        ],
        'placeholder' => [
        ],
    ],
    'modal'         => [
        'delete_project_survey'  => 'Delete Project Survey?',
        'delete_project_message' => 'Are you sure you want to delete this Project Survey?',
        'export' =>[
            'title'              => 'Export Customer Satisfaction Report'
        ]
    ],
    'message'       => [
        'processing'                  => 'Processing...',
        'loadinggraph'                => 'loading graph...',
        'select_project_message'      => 'Please select the project to see the project experience score.',
        'project_survey_deleted'      => 'Project Survey deleted',
        'project_survey_is_use'       => 'The Project Survey is in use!',
        'unable_delete_project_data'  => 'Unable to delete Project Survey data.',
        'survey_link_copied'          => 'Survey link copied',
        'graph_data_get_successfully' => 'Graph data get succesfully.',
        'unable_get_graph_data'       => 'Responses are not available for the selected project.',
        'unauthorized_access'         => 'You are not authorized.',
        'something_wrong'             => 'Something wrong',
        'something_wrong_try_again'   => 'Something went wrong please try again.',
        'data_add_success'            => 'Project Survey has been added successfully!',
        'data_update_success'         => 'Project Survey has been updated successfully!',
    ],
    'projectsurvey' => [
        'title'  => [
            'index_title'     => 'Customer Satisfaction',
            'filter'          => 'Filter',
            'add_form_title'  => "Add Project",
            'edit_form_title' => "Edit Project",
        ],
        'filter' => [
            'select_feedback_type' => 'Select feedBack Type',
        ],
        'table'  => [
            'id'            => 'Id',
            'logo'          => 'Logo',
            'feedback_type' => 'Feedback Type',
            'notes'         => 'Notes',
            'date'          => 'Date',
        ],
        'form'   => [
            'labels'      => [
                'name'       => "Project Name",
                'type'       => "Project Type",
                'start_date' => "Start Date",
                'end_date'   => "End Date",
            ],
            'placeholder' => [
                'enter_project_name'  => 'Enter Project Name',
                'select_project_type' => 'Select Project Type',
            ],
        ],
    ],
];
