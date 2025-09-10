<?php

/*
|--------------------------------------------------------------------------
| Labels for recipes module
|--------------------------------------------------------------------------
|
| The following language lines are used in buttons throughout the system.
| Regardless where it is placed, a button can be listed here so it is easily
| found in a intuitive way.
|
 */
return [
    'title'               => [
        'index' => 'Recipes',
        'add'   => 'Add Recipe',
        'edit'  => 'Edit Recipe',
        'view'  => 'Recipe Details',
    ],
    'buttons'             => [
        'add'         => 'Add Recipe',
        'view'        => 'View',
        'edit'        => 'Edit',
        'delete'      => 'Delete',
        'approve'     => 'Approve',
        'approved'    => 'Approved',
        'delete_ingd' => 'Delete Ingredient',
        'add_ingd'    => 'Add Ingredient',
    ],
    'filter'              => [
        'name'           => 'Search By Recipe name',
        'user'           => 'Search by Author Name',
        'status'         => 'Select Status',
        'company'        => 'Select Company',
        'pending'        => 'Pending',
        'approved'       => 'Approved',
        'pending_count'  => 'Pending Recipe Count:',
        'approved_count' => 'Approved Recipe Count:',
        'tag'            => 'Select Category Tag',
        'type'           => 'Select Recipe Type',
    ],
    'table'               => [
        'updated_at'         => 'Updated At',
        'recipe'             => 'Recipe',
        'company'            => 'Company',
        'visible_to_company' => 'Visible to Company',
        'tag'                => 'Category Tag',
        'username'           => 'Author',
        'type'               => 'Type',
        'status'             => 'Status',
        'actions'            => 'Actions',
        'created_at'         => 'Created At',
    ],
    'visible_company_tbl' => [
        'no'         => 'No.',
        'group_type' => 'Group type',
        'company'    => 'Company',
    ],
    'form'                => [
        'labels'      => [
            'author'             => 'Author',
            'recipe_name'        => 'Recipe Name',
            'time'               => 'Time (Minutes)',
            'calories'           => 'Calories(KCal)',
            'servings'           => 'Servings',
            'goal_tags'          => 'Goal Tags',
            'sub_category'       => 'Category',
            'directions'         => 'Directions',
            'recipe_images'      => 'Recipe Images',
            'ingredients'        => 'Ingredients',
            'nutritions'         => 'Nutritions',
            'company_visibility' => 'Company Visibility',
            'tag'                => 'Category Tag',
            'type'               => 'Type',
            'header_image'       => 'Header Image',
        ],
        'placeholder' => [
            'author'       => 'Author',
            'recipe_name'  => 'Recipe Name',
            'time'         => 'Time (Minutes)',
            'calories'     => 'Calories(KCal)',
            'servings'     => 'Servings',
            'goal_tags'    => 'Goal Tags',
            'delete_image' => 'Delete image',
            'choose_file'  => 'Choose File',
            'ingredients'  => 'Ingredients',
            'tag'          => 'Select Category Tag',
            'type'         => 'Select Recipe Type',
        ],
    ],
    'modal'               => [
        'delete'          => [
            'title'   => 'Delete Recipe?',
            'message' => 'Are you sure you want to delete this recipe?',
        ],
        'approve'         => [
            'title'   => 'Approve Recipe?',
            'message' => 'Are you sure you want to approve this recipe?',
        ],
        'visible_company' => [
            'title' => 'Visible to Company',
        ],
    ],
    'messages'            => [
        'delete_fail'               => 'Failed to delete recipe, please try again!',
        'approve_fail'              => 'Failed to approve recipe, please try again!',
        'recipe_images'             => 'Upload upto 3 files for recipe images',
        'recipe_images_help_text'   => "You can select and upload multiple<br />recipe images by pressing 'Ctrl' key",
        'directions_required'       => 'The directions field is required.',
        'directions_characters'     => 'The directions may not be greater than 5000 characters.',
        'upload_image_dimension'    => 'The uploaded image does not match the given dimension and ratio.',
        'image_valid_error'         => 'Please try again with uploading valid image.',
        'image_size_2M_error'       => 'Maximum allowed size for uploading image is 2 mb. Please try again.',
        'something_wrong_try_again' => 'Something went wrong please try again.',
        'uploading_media'           => 'Uploading media....',
        'processing_media'          => 'Processing on media...',
    ],

    //details
    'details'             => [
        'by'          => 'by',
        'calories'    => 'Calories (KCal)',
        'time'        => 'Time',
        'servings'    => 'Servings',
        'postdt'      => 'Post Date / Time',
        'category'    => 'Category',
        'ingredients' => 'Ingredients',
        'direction'   => 'Direction',
        'nutrition'   => 'Nutrition',
        'type'        => 'Type',
    ],
];
