<?php
return [
    // Labels for App Slides module
    'title'      => [
        'index_title'     => 'Onboarding',
        'add_form_title'  => 'Add Onboarding',
        'edit_form_title' => 'Edit Onboarding',
        'app'             => 'App',
        'portal'          => 'Portal',
        'eap'             => 'Digital Therapy',
    ],
    'filter'     => [
    ],
    'table'      => [
        'updated_at'  => 'Updated At',
        'description' => 'Description',
        'banner'      => 'Banner',
        'id'          => 'Id',
        'order'       => 'Order',
        'action'      => 'Actions',
    ],
    'buttons'    => [
        'add_record' => 'Add Onboarding',
    ],
    'form'       => [
        'labels'      => [
            'choosefile'        => 'Choose File',
            'select_image'      => 'Select Image',
            'content'           => 'Content',
            'mobile_content'    => 'Mobile Text',
            'portal_content'    => 'Portal Text',
            'mobile_image'      => 'Mobile Image',
            'portal_image'      => 'Portal Image',
        ],
        'placeholder' => [
            'enter' => 'Enter ...',
        ],
    ],
    'modal'      => [
        'deletemessage' => 'Are you sure you want to delete this Slide?',
        'deleteslide'   => 'Delete Slide?',
    ],
    'message'    => [
        'something_wrong'           => 'Something went wrong',
        'onboarding_side_deleted'   => 'Onboarding Slide deleted',
        'delete_error'              => 'delete error.',
        'image_valid_error'         => 'Please try again with uploading valid image.',
        'image_size_2M_error'       => 'Maximum allowed size for uploading image or gif is 2 mb. Please try again.',
        'data_store_success'        => 'Onboarding has been added successfully!',
        'data_update_success'       => 'Onboarding has been updated successfully!',
        'something_wrong_try_again' => 'Something went wrong please try again.',
        'unauthorized_access'       => 'You are not authorized.',
        'nothing_change_order'      => 'Nothing to change the order',
        'failed_update_order'       => 'Failed to update order, Please try again!',
        'order_update_success'      => 'Order has been updated successfully',
        'upload_image_dimension'    => 'The uploaded image does not match the given dimension and ratio.',
    ],
    'validation' => [
        'content_required'          => 'The content field is required.',
        'content_max'               => 'The content may not be greater than 500 characters.',
        'onboarding_max'            => 'Max 3 OnBoarding Allowed',
        'portal_content_required'   => 'The portal content field is required.',
        'portal_content_max'        => 'The portal content may not be greater than 500 characters.',
    ],
];
