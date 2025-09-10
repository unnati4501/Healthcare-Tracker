<?php

/*
|--------------------------------------------------------------------------
| Labels for roles module
|--------------------------------------------------------------------------
|
| The following language lines are used in buttons throughout the system.
| Regardless where it is placed, a button can be listed here so it is easily
| found in a intuitive way.
|
 */
return [
    'title'      => [
        'index' => 'Roles',
        'add'   => 'Add Role',
        'edit'  => 'Edit Role',
    ],
    'buttons'    => [
        'add'    => 'Add Role',
        'edit'   => 'Edit',
        'delete' => 'Delete',
    ],
    'filter'     => [
        'name' => 'Search By Role',
        'role' => 'Select Role Group',
    ],
    'table'      => [
        'updated_at' => 'Updated At',
        'name'       => 'Role Name',
        'group'      => 'Role Group',
        'desc'       => 'Description',
        'actions'    => 'Actions',
    ],
    'form'       => [
        'labels'      => [
            'select_role_group' => 'Select Role Group',
            'role_name'         => 'Role Name',
            'role_desc'         => 'Description',
            'set_privileges'    => 'Set Privileges',
        ],
        'placeholder' => [
            'role_name' => 'Enter Role Name',
            'role_desc' => 'Enter Description',
        ],
    ],
    'modal'      => [
        'delete' => [
            'title'   => 'Delete Role?',
            'message' => 'Are you sure you want to delete this Role?',
        ],
    ],
    'messages'   => [
        'deleted'             => "Role has been deleted successfully.",
        'in_user'             => "The role is in use!",
        'delete_fail'         => "Failed to delete role, please try again!",
        'data_store_success'  => 'Role has been added successfully!',
        'data_update_success' => 'Role has been updated successfully!',
        'role_exist'          => 'Role already exists.',
    ],
    'validation' => [
        'name_required'           => 'Please enter the Role Name.',
        'name_regex'              => 'Only Letter and Space are allowed.',
        'name_unique'             => 'Role already exists.',
        'group_required'          => 'Please select the Role Group.',
        'set_privileges_required' => 'Please set privileges.',
    ],
];
