<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Challenge library Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during challenge library for various
    | messages that we need to display to the user.
    |
     */

    'title'    => [
        'manage' => 'Challenge Image Library',
        'add'    => 'Add Image',
        'edit'   => 'Edit Image',
    ],
    'filter'   => [
        'target' => 'Select target type',
    ],
    'table'    => [
        'image'  => 'Image',
        'target' => 'Target type',
        'action' => 'Actions',
    ],
    'form'     => [
        'labels'       => [
            'target' => 'Target type',
            'image'  => 'Image',
        ],
        'placeholders' => [
            'target' => 'Select target type',
            'image'  => 'Choose file',
        ],
    ],
    'buttons'  => [
        'add'      => 'Add Image',
        'upload'   => 'Bulk Upload',
        'tooltips' => [
            'edit'   => 'Edit',
            'delete' => 'Delete',
        ],
    ],
    'modal'    => [
        'delete' => [
            'title'   => 'Delete Image?',
            'message' => 'Are you sure you want to delete this image?',
        ],
        'upload' => [
            'title' => 'Bulk Upload',
            'form'  => [
                'labels'       => [
                    'target' => 'Target type',
                    'images' => 'Images',
                ],
                'placeholders' => [
                    'target' => 'Select target type',
                    'images' => 'Choose files',
                ],
            ],
        ],
    ],
    'messages' => [
        'added'                     => 'Image has been added successfully!',
        'uploaded'                  => 'Images has been uploaded successfully!',
        'updated'                   => 'Image has been updated successfully!',
        'deleted'                   => 'Image has been deleted successfully!',
        'unauthorized'              => 'This action is unauthorized.',
        'something_wrong_try_again' => 'Something went wrong. please try again.',
        'image_valid_error'         => 'Please try again with uploading valid image.',
        'image_size_2M_error'       => 'Maximum allowed size for uploading image is 2 mb. Please try again.',
        'upload_image_dimension'    => 'The uploaded image does not match the given dimension and ratio.',
    ],
];
