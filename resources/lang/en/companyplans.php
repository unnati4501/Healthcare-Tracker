<?php

return [
    // Labels for Company Plan module
    'title'      => [
        'index_title'        => 'Company Plan',
        'add_form_title'     => 'Add Company Plan',
        'edit_form_title'    => 'Edit Company Plan',
        'visible_to_company' => 'Visible to Company',
        'search'             => 'Filter',
    ],
    'filter'     => [
        'select_group_type'      => 'Select group type',
        'search_by_company_name' => 'Search by company plan',
    ],
    'table'      => [
        'updated_at'       => 'Updated At',
        'company_plan'     => 'Company Plan',
        'group_type'       => 'Group Type',
        'mapped_companies' => 'Mapped Companies',
        'action'           => 'Actions',
    ],
    'buttons'    => [
        'add_company_plan' => 'Add Company Plan',
    ],
    'form'       => [
        'labels'      => [
            'company'            => 'Company',
            'group_type'         => 'Select Group Type',
            'companyplan'        => 'Company Plan',
            'description'        => 'Description',
            'set_privileges'     => 'Set Privileges',
            'set_prvileges_desc' => 'The standard features which are common across all plans are not configurable. The optional features below may be added to a custom plan.',
            'reseller'           => 'Reseller',
        ],
        'placeholder' => [
            'enter_companyplan' => 'Enter Company Plan Name',
            'enter_description' => 'Enter Description',
        ],
    ],
    'modal'      => [
        'delete'                     => 'Delete company plan?',
        'company_plan_delete_msg'    => "All data related to this company plan deleted.",
        'delete_message'             => 'Are you sure you want to delete this company plan?',
        'companyplan_deleted'        => 'Company plan has been deleted successfully!',
        'failed_delete_company_plan' => 'Failed to delete company plan.',
    ],
    'message'    => [
        'data_store_success'        => 'Company plan has been added successfully!',
        'data_update_success'       => 'Company plan has been updated successfully!',
        'something_wrong_try_again' => 'Something went wrong please try again.',
        'unauthorized_access'       => 'You are not authorized.',
        'upload_image_dimension'    => 'The uploaded image does not match the given dimension and ratio.',
        'already_in_use'            => 'The plan is already in use!!!',
    ],
    'validation' => [
        'set_privileges_required' => 'At least one privilege will be selected',
    ],
];
