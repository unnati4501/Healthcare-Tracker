<?php
return [
    // Labels for Cronofy module
    'title'           => [
        'index_title'   => 'Authenticate', 
        'availability'  => 'Availability',
        'add_details'   => 'Add Details',
        'book_sessions' => 'Book Sessions',
    ],
    'table'           => [
        'updated_at'    => 'Updated At',
        'action'        => 'Action',
        'status'        => 'Status',
        'primary_email' => 'Primary',
        'email'         => 'Calendar ID',
    ],
    'buttons'         => [
        'add_calendar'       => 'Add Calendar',
        'email_consent_form' => 'Email Consent Form',
    ],
    'tooltips'        => [
        'reconnect'  => 'Re-Connect',
        'disconnect' => 'Disconnect',
    ],
    'modal'           => [
        'unlink'          => 'Unlink Calendar?',
        'unlink_message'  => 'Are you sure you want to unlink this calendar?',
        'primary'         => 'Primary Calendar',
        'primary_message' => 'Are you sure you want to primary this calendar?',
    ],
    'message'         => [
        'something_wrong'       => 'Something went wrong please try again.',
        'unauthorized_access'   => 'You are not authorized.',
        'calendar_availability' => 'Calendar availability has been set successfully!',
        'calendar_unlink'       => 'Calendar has been unlink successfully.',
        'calendar_primary'      => 'Calendar has been primary update successfully.',
    ],
    'ical'            => [
        'title'       => '1:1 Session - :user_name and :wbs_name',
        'description' => 'Your :service_name session with :wbs_name on :session_date :session_time has been confirmed, Join your session from this link or from your confirmation email: :whereby_link, Please join 5-10 minutes before your session is due to begin to ensure it can begin on time and avoid any technical difficulties.',
    ],
    'session_list'    => [
        'title'      => [
            'manage'         => 'Sessions',
            'details'        => 'Session Details',
            'participants'   => 'Participants',
            'single_session' => '1:1',
            'group'          => 'Group',
            'one_session'    => '1:1 Session',
            'group_session'  => 'Group Session',
        ],
        'filters'    => [
            'name'              => 'Search By Client Name',
            'email'             => 'Search By Client Email',
            'time'              => 'Select Time',
            'status'            => 'Select Status',
            'service'           => 'Select Service',
            'company'           => 'Select Company',
            'wellbeing_sp'      => 'Select Wellbeing Specialist',
            'sub_category'      => 'Select Sub-category',
            'client_name_email' => 'Search By Client Name/Email',
        ],
        'table'      => [
            'updated_at'           => 'Updated at',
            'service'              => 'Service',
            'sub_category'         => 'Sub Category',
            'participants'         => 'Participants',
            'counsellor'           => 'Counsellor',
            'user'                 => 'Client Name',
            'email'                => 'Email',
            'company'              => 'Company',
            'duration'             => 'Duration (Mins)',
            'datetime'             => 'Date/Time',
            'status'               => 'Status',
            'action'               => 'Action',
            'view'                 => 'View',
            'no'                   => 'No',
            'name'                 => 'Name',
            'client_email'         => 'Client Email',
            'wellbeing_specialist' => 'Wellbeing Specialist',
            'client_timezone'      => 'Client Timezone',
        ],
        'buttons'    => [
            'book'     => 'Book Session',
            'cancel'   => 'Cancel',
            'join'     => 'Join',
            'back'     => 'Back',
            'tooltips' => [
                'view'  => 'View',
                'edit'  => 'edit',
                'email' => 'Email User',
            ],
        ],
        'messages'   => [
            'something_wrong_try_again' => 'Something went wrong. please try again.',
            'unauthorized_access'       => 'You are not authorized.',
            'completed'                 => 'Session has been completed successfully.',
            'notes_update_success'      => 'Notes has been updated successfully!',
            'no_data_exists'            => 'No data is available',
            'cancelled'                 => 'Session has been cancelled.',
            'disabled_group_session'    => 'Group session is disabled, Please try again.',
            'session_update_success'    => 'Session data has been updated successfully!',
        ],
        'form'       => [
            'labels' => [
                'notes' => 'Notes',
            ],
            'labels' => [
                'notes'      => 'Notes',
                'user_notes' => 'User Notes',
            ],
        ],
        'validation' => [
            'notes_required'         => 'Notes field is required.',
            'cancel_reason_required' => 'The reason may not be greater than 1000 characters.',
        ],
    ],
    'session_details' => [
        'labels'      => [
            'booked_date'          => 'Booked Date',
            'duration'             => 'Duration',
            'minutes'              => 'Minutes',
            'user_notes'           => 'User Notes',
            'cancellation_details' => 'Cancellation Details',
            'cancelled_by'         => 'Cancelled By',
            'cancelled_at'         => 'Cancelled At',
            'cancelled_reason'     => 'Cancelled Reason',
            'reason'               => 'Reason',
            'email_body'           => 'Email Body',
            'srno'                 => 'No.',
            'datetime'             => 'Date/Time',
            'reason'               => 'Reason',
        ],
        'form'        => [
            'labels'      => [
                'no_show'    => 'No Show',
                'reason'     => 'Reason',
                'email_body' => 'Email Body',
                'email_logs' => 'Email Logs',
                'score'      => 'Score',
            ],
            'placeholder' => [
                'enter_message' => 'Enter Message',
            ],
        ],
        'buttons'     => [
            'reschedule'     => 'Reschedule',
            'join'           => 'Join',
            'add_attachment' => 'Add Attachment',
            'tooltips'       => [
                'download' => 'Download',
                'delete'   => 'Delete',
            ],
        ],
        'messages'    => [
            'uielementnotfound'         => 'Oops, an error occurred while fetching the Wellbeing Specialist availability. Please reach out to the Zevo Health admin at <a href="mailto:support@zevohealth.zendesk.com" title="support@zevohealth.zendesk.com">support@zevohealth.zendesk.com</a> to notify them about this error. ',
            'email_success'             => 'Request send to user successfully',
            'something_wrong_try_again' => 'Something went wrong. please try again.',
            'notes_length'              => 'The notes field may not be greater than 2500 characters.',
        ],
        'validation'  => [
            'email_body_required' => 'The email body field is required.',
            'email_body_lengh'    => 'The email body field may not be greater than 5000 characters.',
        ],
        'emaillogs'   => [
            'table' => [
                'srno'     => 'No.',
                'datetime' => 'Date/Time',
                'reason'   => 'Reason',
            ],
        ],
        'attachments' => [
            'labels'   => [
                'title' => 'Attachments',
            ],
            'table'    => [
                'file_name' => 'File Name',
                'datetime'  => 'Upload Date/Time',
                'action'    => 'Action',
            ],
            'messages' => [
                'uploaded'                  => 'Attachments has been uploaded successfully!',
                'deleted'                   => 'Attachment has been deleted successfully!',
                'something_wrong_try_again' => 'Something went wrong. please try again.',
                'image_valid_error'         => 'Please try again with uploading valid attachment.',
                'image_size_5M_error'       => 'Maximum allowed size for uploading attachment is 5 mb. Please try again.',
            ],
        ],
        'modal'       => [
            'delete' => [
                'title'   => 'Delete Attachment?',
                'message' => 'Are you sure you want to delete this attachment?',
            ],
            'upload' => [
                'title' => 'Add Attachment',
                'form'  => [
                    'labels'       => [
                        'attachments' => 'Attachments',
                    ],
                    'placeholders' => [
                        'attachments' => 'Choose files',
                    ],
                ],
            ],
        ],
    ],
    'client_list'     => [
        'title'           => [
            'index'           => 'Clients',
            'details'         => 'Client Details',
            'health_referral' => 'Occupational Health Referral',
        ],
        'filters'         => [
            'name'     => 'Search By Name',
            'email'    => 'Search By Email',
            'location' => 'Search By Location',
            'company'  => 'Select Company',
        ],
        'table'           => [
            'client_name'       => 'Client Name',
            'email'             => 'Email',
            'location_name'     => 'Location',
            'company_name'      => 'Company',
            'completed_session' => 'Completed',
            'cancelled_session' => 'Cancelled',
            'short_canceled'    => 'Short Cancel',
            'upcoming'          => 'Upcoming',
            'no_show'           => 'No Show',
            'action'            => 'Action',
        ],
        'buttons'         => [
            'book'     => 'Book Session',
            'cancel'   => 'Cancel',
            'join'     => 'Join',
            'back'     => 'Back',
            'tooltips' => [
                'view' => 'View',
            ],
        ],
        'messages'        => [
            'something_wrong_try_again' => 'Something went wrong. please try again.',
            'unauthorized_access'       => 'You are not authorized.',
            'no_data_exists'            => 'No data is available',
        ],

        'details'         => [
            'completed'            => 'Completed',
            'ongoing'              => 'Ongoing',
            'cancelled'            => 'Cancelled',
            'notes'                => 'Notes',
            'cm_notes'             => 'Case Manager Notes',
            'add_note'             => 'Add Notes',
            'export'               => 'Export',
            'session_notes'        => 'Session Notes',
            'user_notes'           => 'User Notes',
            'wellbeing_specialist' => 'Wellbeing Specialist',
            'consent_not_received' => 'Consent Not Received',
            'consent_received'     => 'Consent Received',
            'notify_client'        => 'Notify Client',
            'access_next_to_kin'   => 'Access Next of Kin info?',
            'filters'              => [
                'session_name'   => 'By Session Name',
                'session_status' => 'By Status',
            ],
            'table'                => [
                'session_name' => 'Session Name',
                'duration_min' => 'Duration (mins)',
                'status'       => 'Status',
                'view'         => 'View',
            ],
            'modal'                => [
                'cancel' => [
                    'title'  => 'Cancellation details',
                    'fields' => [
                        'cancelled_by'     => 'Cancelled by',
                        'cancelled_at'     => 'Cancelled at',
                        'cancelled_reason' => 'Cancelled reason',
                    ],
                ],
                'delete' => [
                    'title'   => 'Delete Note?',
                    'message' => 'Are you sure you want to delete this note?',
                ],
                'export' => [
                    'ws_notes'                  => 'Export Wellbeing Specialist Notes',
                    'user_notes'                => 'Export User Notes',
                    'email_address'             => 'Email Address',
                    'enter_email_address'       => 'Enter Email Address',
                    'report_running_background' => 'Report generation is running in background, Once it will be generated, the report will be sent to email.',
                ],
            ],
            'messages'             => [
                'no_comments'           => 'No notes were added yet',
                'loading_cm_notes'      => 'Loading case manager notes....',
                'no_cm_notes_date'      => 'No notes were found for the selected date',
                'failed_cm_notes'       => 'Failed to load case manager notes',
                'note_deleted'          => 'Note deleted',
                'unable_to_delete_note' => 'Failed to delete note',
            ],
        ],

        'health_referral' => [
            'form' => [
                'labels'      => [
                    'date'                 => 'Date',
                    'confirmation_client'  => 'Confirmation Client',
                    'confirmation_date'    => 'Confirmation Date',
                    'note'                 => 'Note',
                    'attend'               => 'Attend',
                    'wellbeing_specialist' => 'Wellbeing Specialist Name',
                ],
                'placeholder' => [
                    'date'                 => 'Select Date',
                    'confirmation_date'    => 'Secect Confirmation Date',
                    'note'                 => 'Note',
                    'wellbeing_specialist' => 'Select Wellbeing Specialist Name',
                ],
            ],
        ],
    ],
    'group_session'   => [
        'title'   => [
            'add_group_session'  => 'Add group session',
            'manage'             => 'Sessions',
            'details'            => 'Session Details',
            'edit_group_session' => 'Edit group session',
        ],
        'form'    => [
            'labels'      => [
                'service'      => 'Service',
                'sub_category' => 'Sub Category',
                'company'      => 'Company',
                'notes'        => 'Notes',
                'add_users'    => 'Add Users',
                'ws_display'   => 'WS Display',
                'date-time'    => 'Date & Time',
                'location'     => 'Location',
            ],
            'placeholder' => [
                'service'      => 'Select service',
                'sub_category' => 'Select Sub Category',
                'company'      => 'Select Company',
                'location'     => 'Select Location',
            ],
        ],
        'message' => [
            'fullscreen_mode_for_notes' => 'For the best appearance try full screen mode by clicking on button',
            'something_wrong'           => 'Something went wrong please try again.',
            'unauthorized_access'       => 'You are not authorized.',
            'minimum_user_required'     => 'Minimum 1 user is required to make the booking',
            'only_one_participate'      => 'Only one participant is allowed in the 1:1 Sessions.',
            'notes_length'              => 'The note field may not be greater than 6000 characters.',
            'data_update_success'       => 'Session details updated successfully!',
            'email_send_success'        => 'Exception email send successfully!',
            'details_page_message'      => 'On the next screen, you will be redirected to select the Date and Time of your session.',
            'ws_message'                => 'Please select service and sub category to view wellbeing specialist.',
            'loading_ws'                => 'Loading wellbeing specialist...',
            'no_result_found'           => 'No wellbeing specialist are available for your service and sub category. Please choose another and try again',
            'note_warning'              => 'These notes are shared with the user. To add session notes, please add it directly to the client record on the Clients tab.',
        ],
    ],
    'consent_form'    => [
        'title'       => [
            'questions' => 'Questions',
        ],
        'form'        => [
            'labels'      => [
                'title'       => 'Title',
                'description' => 'Description',
                'company'     => 'Company',
                'notes'       => 'Notes',
                'add_users'   => 'Add Users',
                'ws_display'  => 'WS Display',
                'date-time'   => 'Date & Time',
            ],
            'placeholder' => [
                'title'       => 'Title',
                'description' => 'Description',
                'company'     => 'Select Company',
            ],
        ],
        'buttons'     => [
            'add_question' => 'Add Question',
        ],
        'table'       => [
            'order'    => 'Order',
            'title'    => 'Title',
            'action'   => 'Action',
            'category' => 'Category',
        ],
        'static_data' => [
            'title'                => 'Counselling Consent Form',
            'description'          => 'At Zevo Health, we take your privacy seriously.<br/>  All personal data, including session attendance and the content of your sessions is always kept confidential. Anything which you disclose in the sessions will remain confidential. <br/>
It is important that during your discussions, you feel you can talk openly and that your right to privacy is protected. To ensure this, we cannot discuss you or your case with any third parties without your consent, which includes your employer. <br/>
At any time you may request in writing access to your case notes or make a request for your personal data to be deleted by emailing <a href="mailto:dpo@yopmail.com">dpo@yopmail.com</a>.',
            'question'             => 'Exceptions to confidentiality',
            'question_description' => '<p>In some circumstances, confidentiality can be broken. Although these situations are rare, you should be made aware of what they are:</p><ul><li><p>If you threaten to harm another person.&nbsp;</p></li><li><p>If you threaten to cause severe harm to yourself.&nbsp;</p></li><li><p>If there is a historical disclosure of abuse and the alleged perpetrator is still alive.</p></li><li><p>If there are reasons to believe that any child, elderly person, or incompetent person is at risk from abuse or neglect.</p></li><li><p>If information disclosed relates to criminal proceedings or is requested by a court of law in other legal proceedings In these cases, we discuss the need to break confidentiality with you and then plan the best course of action.</p></li></ul>',
        ],
        'model_popup' => [
            'title'          => 'Add Question',
            'delete_title'   => 'Delete question?',
            'delete_message' => 'Are you sure you want to remove this question?',
        ],
        'message'     => [
            'fullscreen_mode_for_description' => 'For the best appearance try full screen mode by clicking on button',
            'from_toolbar'                    => 'from toolbar.',
            'data_update_success'             => 'Consent form has been updated successfully!',
            'something_wrong_try_again'       => 'Something went wrong please try again.',
            'question_deleted'                => 'Consent form question has been deleted successfully.',
        ],
        'validation'  => [
            'description' => 'The description field is required.',
            'desc_lengh'  => 'The description field may not be greater than 7000 characters.',
            'title'       => 'The title field is required.',
            'title_lengh' => 'The title field may not be greater than 100 characters.',
        ],
    ],
];
