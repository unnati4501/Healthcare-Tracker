<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Challenge Map Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during challenge Map for various
    | messages that we need to display to the user.
    |
     */

    'title'      => [
        'manage' => 'Map Library',
        'add'    => 'Add Map',
        'edit'   => 'Edit Map',
    ],
    'filter'     => [
        'target' => 'Select target type',
    ],
    'table'      => [
        'image'          => 'Image',
        'name'           => 'Name',
        'total_distance' => 'Total Distance',
        'locations'      => 'Locations',
        'status'         => 'Status',
        'description'    => 'description',
        'action'         => 'Actions',
    ],
    'form'       => [
        'labels'       => [
            'image'                      => 'Map Logo',
            'name'                       => 'Map Name',
            'description'                => 'Description',
            'location'                   => 'Location',
            'set_properties'             => 'Set Properties',
            'location_type'              => 'Location Type',
            'location_name'              => 'Location Name',
            'lat_long'                   => 'Lat/Long',
            'upload_files'               => 'Upload files',
            'distance_previous_location' => 'Distance from previous location',
            'total_locations'            => 'Total Locations',
            'total_distance'             => 'Total Distance',
            'total_steps'                => 'Total Steps',
            'km'                         => 'KM',
            'company_visibility'         => 'Company Visibility',
            'add_locations'              => 'Add Locations',
            'steps'                      => 'Steps',
        ],
        'placeholders' => [
            'name'                       => 'Enter map name',
            'description'                => 'Enter map description',
            'image'                      => 'Choose file',
            'location'                   => 'Location',
            'location_type'              => 'Select location type',
            'location_name'              => 'Enter location Name',
            'lat_long'                   => 'Lat/Long',
            'total_locations'            => 'Total Locations',
            'total_distance'             => 'Total Distance',
            'distance_previous_location' => 'Distance from previous location',
            'distance'                   => 'Distance',
            'steps'                      => 'Steps',
            'total_steps'                => 'Total Steps',
        ],
    ],
    'buttons'    => [
        'add'      => 'Add Map',
        'upload'   => 'Bulk Upload',
        'tooltips' => [
            'edit'    => 'Edit',
            'delete'  => 'Delete',
            'archive' => 'Archive',
            'view'    => 'View',
        ],
    ],
    'modal'      => [
        'delete'         => [
            'title'   => 'Delete Map?',
            'message' => 'Are you sure you want to delete this map?',
        ],
        'delete_latlong' => [
            'title'   => 'Delete Location?',
            'message' => 'Are you sure you want to delete this map location?',
        ],
        'archive'        => [
            'title'   => 'Archive Map?',
            'message' => 'Are you sure you want to archive this map?',
        ],
        'upload'         => [
            'title' => 'Bulk Upload',
            'form'  => [
                'labels'       => [
                    'target' => 'Target type',
                    'images' => 'Map',
                ],
                'placeholders' => [
                    'target' => 'Select target type',
                    'images' => 'Choose files',
                ],
            ],
        ],
        'view'           => [
            'title'            => 'View Map',
            'view_description' => 'View Description',
        ],
        'map_location'   => [
            'location_name'    => 'Location Name',
            'no'               => 'No',
            'name_of_location' => 'Name of Location',
        ],
    ],
    'messages'   => [
        'added'                     => 'Map has been added successfully!',
        'added-nextstep'            => 'Map has been added successfully, Please add property of each location!',
        'uploaded'                  => 'Map has been uploaded successfully!',
        'updated'                   => 'Map has been updated successfully!',
        'deleted'                   => 'Map has been deleted successfully!',
        'archived'                  => 'Map has been archived successfully!',
        'map_lat_long'              => 'Map Lat/long has been added successfully!',
        'property_updated'          => 'Map property has been updated successfully!',
        'deleted_location'          => 'Map location has been deleted successfully!',
        'unauthorized'              => 'This action is unauthorized.',
        'something_wrong_try_again' => 'Something went wrong. please try again.',
        'image_valid_error'         => 'Please try again with uploading valid image.',
        'image_size_2M_error'       => 'Maximum allowed size for uploading image is 2 mb. Please try again.',
        'upload_image_dimension'    => 'The uploaded image does not match the given dimension and ratio.',
        'add_locations'             => 'Please click on a specific location on the below map to add it.',
    ],
    'validation' => [
        'company_selection'        => 'The company selection is required',
        'location_required'        => 'The location name field is required.',
        'location_greater_char'    => 'The location name may not be greater than 50 characters.',
        'location_type_required'   => 'The location type field is required.',
        'distance_required'        => 'The distance field is required.',
        'distance_number'          => 'The distance field allow only number.',
        'property_set_message'     => 'Please set property of each location.',
        'property_upload_required' => 'The property Upload files field is required.',
        'steps_required'           => 'The steps field is required.',
        'steps_number'             => 'The steps field allow only number.',
        'steps_valid_number'       => 'Please enter valid steps number',
        'distance_valid_number'    => 'Please enter valid distance number',
        'destination_validation'   => 'Please select destination as main location.',
    ],
];
