<?php
return [
    // Labels for Group module
    'title'      => [
        'index_title'     => 'Groups',
        'add_form_title'  => 'Add Group',
        'edit_form_title' => 'Edit Group',
        'main_groups'     => 'Main Groups',
        'other_groups'    => 'Other Groups',
        'filter'          => 'Filter',
        'details'         => 'Group Details',
        'reportabuse'     => 'Report Abuse',

    ],
    'filter'     => [
        'search_by_name'      => 'Search By Name',
        'select_sub_category' => 'Select Sub-category',
        'select_group_type'   => 'Select Group Type',
        'is_archived'         => 'Is Archived?',
    ],
    'table'      => [
        'updated_at'      => 'Updated At',
        'logo'            => 'Logo',
        'subcategory'     => 'Sub Category',
        'groupname'       => 'Group Name',
        'members'         => 'Members',
        'type'            => 'Type',
        'archived'        => 'Archived',
        'action'          => 'Actions',
        'name'            => 'Name',
        'email'           => 'Email',
        'groupmembers'    => 'Group Members',
        'grouptype'       => 'Group Type',
        'subcategoryname' => 'Sub-category Name',
        'grouptitle'      => 'Group Title',
        'created_by'      => 'Created By'

    ],
    'buttons'    => [
        'add_group' => 'Add Group',
    ],
    'form'       => [
        'labels'      => [
            'logo'               => 'Logo',
            'change'             => 'Browse',
            'group_creator'      => 'Group Creator',
            'title'              => 'Group Name',
            'category_name'      => 'Category Name',
            'members'            => 'Members',
            'details'            => 'Group Details',
            'introduction'       => 'Introduction',
            'sub_category'       => 'Sub-category',
            'private'            => 'Private',
            'public'             => 'Public',
            'participating_user' => 'Participating Users',
            'group_type'         => 'Group Type',
        ],
        'placeholder' => [
            'enter_group_name' => 'Enter Group Name',
            'select_category'  => 'Select category',

        ],
    ],
    'modal'      => [
        'delete'                 => 'Delete Group?',
        'delete_message'         => 'Are you sure you want to delete this Group?',
        'group_deleted'          => 'Group deleted',
        'group_in_use'           => 'The Group is in use!',
        'unable_to_delete_group' => 'Unable to delete Group data.',

    ],
    'message'    => [
        'added'                     => 'Location has been added successfully!',
        'updated'                   => 'Location has been updated successfully!',
        'something_wrong'           => 'Something went wrong please try again.',
        'unauthorized_access'       => 'You are not authorized.',
        'something_wrong_try_again' => 'Something went wrong please try again.',
        'data_store_success'        => 'Group has been added successfully!',
        'data_update_success'       => 'Group has been updated successfully!',
        'upload_image_dimension'    => 'The uploaded image does not match the given dimension and ratio.',
        'image_valid_error'         => 'Please try again with uploading valid image.',
        'image_size_2M_error'       => 'Maximum allowed size for uploading image or gif is 2 mb. Please try again.',
    ],
    'validation' => [
        'group_member_required' => 'The participating users field is required.',
        'group_member_min'      => 'Please select at least 2 participants.',
    ],
];
