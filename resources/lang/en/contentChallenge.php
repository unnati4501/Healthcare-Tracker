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

    'title'       => [
        'manage' => 'Content Challenge',
        'edit'   => 'Edit Content Challenge',
    ],

    'table'       => [
        'updated_at'    => 'Updated at',
        'category_name' => 'Category Name',
        'activities'    => 'Activities',
        'action'        => 'Actions',
    ],
    'breadcrumbs' => [
        'index' => 'Content Challenge',
    ],
    'form'        => [
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
    'buttons'     => [
        'add_goal' => 'Add Goal',
        'tooltips' => [
            'edit' => 'Edit',
            'save' => 'Save',
        ],
    ],
    'messages'    => [
        'unauthorized'               => 'This action is unauthorized.',
        'description_update_success' => 'Description has been updated successfully!',
        'something_wrong_try_again'  => 'Something went wrong. please try again.',
    ],

    'activities'  => [
        'breadcrumbs' => [
            'index' => 'Edit Activity Limits',
        ],
        'title'       => [
            'manage' => 'Edit Activity Limits',
        ],
        'table'       => [
            'updated_at'        => 'Updated at',
            'activity'          => 'Activity',
            'daily_limit'       => 'Daily Limit',
            'points_per_action' => 'Points per Action',
            'action'            => 'Action',
        ],
        'validation'  => [
            'daily_limit_required'       => 'Daily limit is required',
            'points_per_action_required' => 'Points per action is required',
            'greater_then_zero_allowed'  => 'Please enter the value greater then 0'
        ],
        'messages'    => [
            'activity_updated'          => 'Activity updated successfully',
            'something_wrong_try_again' => 'Something went wrong. please try again.',
        ],
    ],
];
