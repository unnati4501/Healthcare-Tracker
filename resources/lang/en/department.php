<?php

return [
    // Labels for categories module
    'title'      => [
        'index_title'     => 'Departments',
        'add_form_title'  => 'Add Department',
        'edit_form_title' => 'Edit Department',
        'search'          => 'Filter',
    ],
    'filter'     => [
        'department'     => 'Department',
        'select_company' => 'Select Company',
    ],
    'table'      => [
        'updated_at' => 'Updated At',
        'department' => 'Departments',
        'company'    => 'Company',
        'teams'      => 'Teams',
        'members'    => 'Members',
        'action'     => 'Actions',
    ],
    'buttons'    => [
        'add_department' => 'Add Department',
        'view_location'  => 'View Location',
    ],
    'form'       => [
        'labels'      => [
            'company'          => 'Company',
            'department'       => 'Departments',
            'department_name'  => 'Department Name',
            'company_location' => 'Company Location',
            'set_your_team'    => 'Set Your team',
        ],
        'placeholder' => [
            'company'               => 'Select Company',
            'enter_department_name' => 'Enter Department Name',
        ],
    ],
    'modal'      => [
        'delete'                      => 'Delete Department?',
        'delete_message'              => 'Are you sure you want to delete this Department?',
        'department_deleted'          => 'Department deleted',
        'department_in_use'           => 'The department is in use!',
        'unable_to_delete_department' => 'Unable to delete department data.',
    ],
    'message'    => [
        'processing'                => 'Processing...',
        'data_store_success'        => 'Department has been added successfully!',
        'data_update_success'       => 'Department has been updated successfully!',
        'something_wrong_try_again' => 'Something went wrong please try again.',
        'something_wrong'           => 'Something wrong',
        'unauthorized_access'       => 'You are not authorized.',
    ],
    'validation' => [
        'employee_count_error'    => 'The employee count field is required.',
        'employee_count_length'   => 'The employee count must be at least',
        'employee_count_greater'  => 'The employee count may not be greater than',
        'team_unique'             => 'Team name should be unique.',
        'naming_convention'       => 'The naming convention field is required.',
        'naming_convention_param' => 'The naming convention may not be greater than :param characters.',
        'already_teamname_taken'  => 'Team name (:name) has already been taken.',
    ],
    'location'   => [
        'title' => [
            'department_location_title' => 'Department Locations',
        ],
        'table' => [
            'updated_at'   => 'Updated At',
            'locationname' => 'Location Name',
            'country'      => 'Country',
            'state'        => 'County',
            'time_zone'    => 'Time/Zone',
            'address'      => 'Address',
        ],
    ],
];
