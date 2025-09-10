<?php

return [
    // Labels for Location module
    'title'      => [
        'index_title'     => 'Locations',
        'add_form_title'  => 'Add Location',
        'edit_form_title' => 'Edit Location',
    ],
    'filter'     => [
        'search_name'     => 'Search By Name',
        'search_country'  => 'Select Country',
        'search_timezone' => 'Select Timezone',
        'search_county' => 'Select County',
    ],
    'table'      => [
        'updated_at'    => 'Updated At',
        'company'       => 'Company',
        'location_name' => 'Location Name',
        'country'       => 'Country',
        'county'        => 'County',
        'timezone'      => 'Time/Zone',
        'address'       => 'Address',
        'action'        => 'Actions',
    ],
    'buttons'    => [
        'add_location' => 'Add Location',
    ],
    'form'       => [
        'labels'      => [
            'company'       => 'Company',
            'location_name' => 'Location Name',
            'address_line1' => 'Address Line 1',
            'address_line2' => 'Address Line 2',
            'country'       => 'Country',
            'county'        => 'County',
            'timezone'      => 'Timezone',
            'postal_code'   => 'Postal Code',
        ],
        'placeholder' => [
            'select_company'      => 'Select Company',
            'enter_location_name' => 'Enter Location Name',
            'enter_address_line1' => 'Enter Address Line1',
            'enter_address_line2' => 'Enter Address Line2',
            'select'              => 'Select',
            'select_country'      => 'Select Country',
            'select_timezone'     => 'Select Timezone',
            'select_county'       => 'Select County',
            'enter_postal_code'   => 'Enter Postal Code',
        ],
    ],
    'modal'      => [
        'delete'                    => 'Delete Location?',
        'delete_message'            => 'Are you sure you want to delete this Location?',
        'location_deleted'          => 'Location deleted',
        'location_in_use'           => 'The location is in use!',
        'unable_to_delete_location' => 'Unable to delete Location data.',
    ],
    'message'    => [
        'added'               => 'Location has been added successfully!',
        'updated'             => 'Location has been updated successfully!',
        'something_wrong'     => 'Something went wrong please try again.',
        'unauthorized_access' => 'You are not authorized.',
    ],
    'validation' => [
        'already_taken_name' => "The name has already been taken.",
    ],
];
