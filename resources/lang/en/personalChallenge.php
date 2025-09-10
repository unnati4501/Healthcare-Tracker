<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Personal Challenge Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during personal challenges for various
    | messages that we need to display to the user.
    |
     */

    'title'    => [
        'manage'      => 'Personal Challenge',
        'add'         => 'Add Personal Challenge',
        'edit'        => 'Edit Personal Challenge',
        'manage_goal' => 'Goals',
        'edit_goal'   => 'Edit Goal',
        'add_goal'    => 'Add Goal',
    ],
    'filter'   => [
        'name'           => 'Search by name',
        'challenge_type' => 'Select Challenge Type',
        'goal_type'      => 'Select Goal Type',
        'type'           => 'Select Sub Type',
        'recursive'      => 'Select Recursive',
    ],
    'table'    => [
        'updated_at'     => 'Updated at',
        'logo'           => 'Logo',
        'name'           => 'Challenge Name',
        'name_goal'      => 'Goal Name',
        'duration'       => 'Duration',
        'type'           => 'Sub Type',
        'joined'         => 'Joined',
        'action'         => 'Actions',
        'challenge_type' => 'Challenge Type',
        'goal_type'      => 'Goal Type',
        'created_by'     => 'Created By',
    ],
    'form'     => [
        'labels'       => [
            'logo'           => 'Logo',
            'name'           => 'Challenge name',
            'name_goal'      => 'Goal name',
            'duration'       => 'Duration',
            'description'    => 'Description',
            'type'           => 'Sub Type',
            'tasks'          => 'Tasks',
            'days'           => 'Days',
            'challenge_type' => 'Challenge Type',
            'goal_type'      => 'Goal Type',
            'target_value'   => 'Target Value',
            'uom'            => 'Unit of Measurement',
            'counts'         => 'Counts',
            'meter'          => 'Meter',
            'minutes'        => 'Minutes',
            'is_recursive'   => 'Is Recursive',
        ],
        'placeholders' => [
            'logo'                     => 'Choose file',
            'name'                     => 'Enter challenge name',
            'name_goal'                => 'Enter goal name',
            'duration'                 => 'Enter duration',
            'description'              => 'Enter Description',
            'to-do'                    => 'To-do',
            'streak'                   => 'Streak',
            'task'                     => 'Enter Task',
            'tasks'                    => 'Tasks',
            'routineplan'              => 'Routine Plan',
            'personalfitnesschallenge' => 'Personal Fitness Challenge',
            'habbitplan'               => 'Habit Plan',
            'steps'                    => 'Steps',
            'distance'                 => 'Distance',
            'meditation'               => 'Meditation',

        ],
        'tooltips'     => [
            'add'    => 'Add task',
            'delete' => 'Delete task',
        ],
    ],
    'buttons'  => [
        'add'      => 'Add Personal Challenge',
        'add_goal' => 'Add Goal',
        'tooltips' => [
            'edit'   => 'Edit',
            'delete' => 'Delete',
        ],
    ],
    'modal'    => [
        'title'        => 'Delete Challenge?',
        'title_goal'   => 'Delete Goal?',
        'message'      => 'Are you sure you want to delete this Challenge?',
        'message_goal' => 'Are you sure you want to delete this Goal?',
    ],
    'messages' => [
        'added'                     => 'Personal challenge has been added successfully!',
        'updated'                   => 'Personal challenge has been updated successfully!',
        'deleted'                   => 'Personal challenge has been deleted successfully!',
        'added_goal'                => 'Goal has been added successfully!',
        'updated_goal'              => 'Goal has been updated successfully!',
        'deleted_goal'              => 'Goal has been deleted successfully!',
        'unauthorized'              => 'This action is unauthorized.',
        'something_wrong_try_again' => 'Something went wrong. please try again.',
        'image_valid_error'         => 'Please try again with uploading valid image.',
        'image_size_2M_error'       => 'Maximum allowed size for uploading image is 2 mb. Please try again.',
        'upload_image_dimension'    => 'The uploaded image does not match the given dimension and ratio.',
    ],
];
