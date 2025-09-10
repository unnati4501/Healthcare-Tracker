<?php

namespace App\Providers;

use App\Events\AdminRegisterEvent;
use App\Events\BookingReportCompanyWiseExportEvent;
use App\Events\BookingReportDetailExportEvent;
use App\Events\ChallengeActivityReportExportEvent;
use App\Events\ChallengeUserActivityReportExportEvent;
use App\Events\ClientUserNotesExportEvent;
use App\Events\ContactUsEvent;
use App\Events\AdminAlertEvent;
use App\Events\CompanyArchivedEvent;
use App\Events\ContentReportExportEvent;
use App\Events\CreditHistoryExportEvent;
use App\Events\CounsellorFeedbackReportExportEvent;
use App\Events\DepartmentExportEvent;
use App\Events\DigitaltherapyExceptionHandlingEvent;
use App\Events\DigitalTherapyExportEvent;
use App\Events\EventBookedEvent;
use App\Events\EventExpiredEvent;
use App\Events\EventPendingEvent;
use App\Events\EventRejectedEvent;
use App\Events\EventStatusChangeEvent;
use App\Events\EventUpdatedEvent;
use App\Events\ExportBookingsEvent;
use App\Events\IntercompanyChallengeExportEvent;
use App\Events\InterCopmanyReportExportEvent;
use App\Events\InviteExistingWellbeingConsultantEvent;
use App\Events\LocationExportEvent;
use App\Events\MasterclassFeedbackReportExportEvent;
use App\Events\NpsExportReportEvent;
use App\Events\NpsProjectExportEvent;
use App\Events\SendDataExportEmailEvent;
use App\Events\SendEmailConsentEvent;
use App\Events\SendEventCancelledEvent;
use App\Events\SendEventEmailNotesEvent;
use App\Events\SendEventReminderEvent;
use App\Events\SendMcSurveyReportExportEvent;
use App\Events\SendProjectSurveyEvent;
use App\Events\SendSessionBookedEvent;
use App\Events\SendSessionCancelledEvent;
use App\Events\SendSessionRescheduledEvent;
use App\Events\SendSingleUseCodeEvent;
use App\Events\SendZcSurveyReportExportEvent;
use App\Events\SendZCUserSurveyEvent;
use App\Events\TeamExportEvent;
use App\Events\UpcomingSessionEmailEvent;
use App\Events\UserActivityExportEvent;
use App\Events\UserChangePasswordEvent;
use App\Events\UserForgotPasswordEvent;
use App\Events\UserImportStatusEvent;
use App\Events\UserRegisterEvent;
use App\Events\UserRegistrationReportExportEvent;
use App\Events\SendSessionNotesReminderEvent;
use App\Events\UserTrackerHistoryExportEvent;
use App\Events\OccupationalHealthReportExportEvent;
use App\Events\ChallengeDetailExportEvent;
use App\Events\DigitalTherapyClientExportEvent;
use App\Events\UsageReportExportEvent;
use App\Events\RealtimeAvailabilityEvent;
use App\Listeners\SendDigitalTherapyClientExportListener;
use App\Listeners\DigitaltherapyExceptionHandlingListener;
use App\Listeners\EventEmailListener;
use App\Listeners\InviteExistingWellbeingConsultantListener;
use App\Listeners\SendAdminRegisterEmail;
use App\Listeners\SendBookingReportCompanyWiseExportListener;
use App\Listeners\SendBookingReportDetailExportListener;
use App\Listeners\SendBookingsExportListener;
use App\Listeners\SendChallengeActivityReportExportListener;
use App\Listeners\SendChallengeUserActivityReportExportListener;
use App\Listeners\SendClientUserNotesExportListener;
use App\Listeners\SendContactUsEmailListener;
use App\Listeners\AdminAlertEmailListener;
use App\Listeners\CompanyArchivedEmailListener;
use App\Listeners\SendContentReportListener;
use App\Listeners\SendCreditHistoryExportListener;
use App\Listeners\SendCounsellorFeedbackReportExportListener;
use App\Listeners\SendDataExportEmailListener;
use App\Listeners\SendDepartmentExportListener;
use App\Listeners\SendDigitalTherapyExportListener;
use App\Listeners\SendIntercompanyChallengeEmail;
use App\Listeners\SendInterCompanyReportExportListener;
use App\Listeners\SendLocationExportListener;
use App\Listeners\SendMasterclassFeedbackReportExportListener;
use App\Listeners\SendNpsExportReportEmail;
use App\Listeners\SendNpsProjectExportListener;
use App\Listeners\SendProjectSurvey;
use App\Listeners\SendSingleUseCodeListener;
use App\Listeners\SendTeamExportListener;
use App\Listeners\SendUserActivityExportListener;
use App\Listeners\SendUserChangePassword;
use App\Listeners\SendUserForgotPassword;
use App\Listeners\SendUserImportStatus;
use App\Listeners\SendUserRegisterEmail;
use App\Listeners\SendUserRegistrationReportExportListener;
use App\Listeners\SendUserTrackerHistoryExportListener;
use App\Listeners\SessionEmailListener;
use App\Listeners\UpcomingSessionEmailListener;
use App\Listeners\ZcSurveyEmailListener;
use App\Listeners\SendOccupationalHealthReportListener;
use App\Listeners\ChallengeDetailExportEmailListener;
use App\Listeners\SendUsageReportListener;
use App\Listeners\RealtimeAvailabilityListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event; 

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class                             => [
            SendEmailVerificationNotification::class,
        ],
        UserRegisterEvent::class                      => [
            SendUserRegisterEmail::class,
        ],
        UserForgotPasswordEvent::class                => [
            SendUserForgotPassword::class,
        ],
        UserChangePasswordEvent::class                => [
            SendUserChangePassword::class,
        ],
        UserImportStatusEvent::class                  => [
            SendUserImportStatus::class,
        ],
        SendZCUserSurveyEvent::class                  => [
            ZcSurveyEmailListener::class,
        ],
        SendProjectSurveyEvent::class                 => [
            SendProjectSurvey::class,
        ],
        IntercompanyChallengeExportEvent::class       => [
            SendIntercompanyChallengeEmail::class,
        ],
        SendEventCancelledEvent::class                => [
            EventEmailListener::class,
        ],
        EventBookedEvent::class                       => [
            EventEmailListener::class,
        ],
        EventUpdatedEvent::class                      => [
            EventEmailListener::class,
        ],
        SendEventReminderEvent::class                 => [
            EventEmailListener::class,
        ],
        SendEventEmailNotesEvent::class               => [
            EventEmailListener::class,
        ],
        SendZcSurveyReportExportEvent::class          => [
            ZcSurveyEmailListener::class,
        ],
        SendMcSurveyReportExportEvent::class          => [
            ZcSurveyEmailListener::class,
        ],
        ContentReportExportEvent::class               => [
            SendContentReportListener::class,
        ],
        ContactUsEvent::class                         => [
            SendContactUsEmailListener::class,
        ],
        AdminAlertEvent::class                         => [
            AdminAlertEmailListener::class,
        ],
        CompanyArchivedEvent::class => [
            CompanyArchivedEmailListener::class,
        ],
        EventPendingEvent::class                      => [
            EventEmailListener::class,
        ],
        EventRejectedEvent::class                     => [
            EventEmailListener::class,
        ],
        EventExpiredEvent::class                      => [
            EventEmailListener::class,
        ],
        EventStatusChangeEvent::class                 => [
            EventEmailListener::class,
        ],
        AdminRegisterEvent::class                     => [
            SendAdminRegisterEmail::class,
        ],
        SendSingleUseCodeEvent::class                 => [
            SendSingleUseCodeListener::class,
        ],
        NpsExportReportEvent::class                   => [
            SendNpsExportReportEmail::class,
        ],
        NpsProjectExportEvent::class                  => [
            SendNpsProjectExportListener::class,
        ],
        UserRegistrationReportExportEvent::class      => [
            SendUserRegistrationReportExportListener::class,
        ],
        CounsellorFeedbackReportExportEvent::class    => [
            SendCounsellorFeedbackReportExportListener::class,
        ],
        MasterclassFeedbackReportExportEvent::class   => [
            SendMasterclassFeedbackReportExportListener::class,
        ],
        UserTrackerHistoryExportEvent::class          => [
            SendUserTrackerHistoryExportListener::class,
        ],
        BookingReportDetailExportEvent::class         => [
            SendBookingReportDetailExportListener::class,
        ],
        DepartmentExportEvent::class                  => [
            SendDepartmentExportListener::class,
        ],
        LocationExportEvent::class                    => [
            SendLocationExportListener::class,
        ],
        TeamExportEvent::class                        => [
            SendTeamExportListener::class,
        ],
        InterCopmanyReportExportEvent::class          => [
            SendInterCompanyReportExportListener::class,
        ],
        UserActivityExportEvent::class                => [
            SendUserActivityExportListener::class,
        ],
        ChallengeActivityReportExportEvent::class     => [
            SendChallengeActivityReportExportListener::class,
        ],
        BookingReportCompanyWiseExportEvent::class    => [
            SendBookingReportCompanyWiseExportListener::class,
        ],
        ChallengeUserActivityReportExportEvent::class => [
            SendChallengeUserActivityReportExportListener::class,
        ],
        SendSessionCancelledEvent::class              => [
            SessionEmailListener::class,
        ],
        SendSessionBookedEvent::class                 => [
            SessionEmailListener::class,
        ],
        SendSessionRescheduledEvent::class            => [
            SessionEmailListener::class,
        ],
        DigitalTherapyExportEvent::class              => [
            SendDigitalTherapyExportListener::class,
        ],
        SendEmailConsentEvent::class                  => [
            SessionEmailListener::class,
        ],
        DigitaltherapyExceptionHandlingEvent::class   => [
            DigitaltherapyExceptionHandlingListener::class,
        ],
        ExportBookingsEvent::class                    => [
            SendBookingsExportListener::class,
        ],
        InviteExistingWellbeingConsultantEvent::class => [
            InviteExistingWellbeingConsultantListener::class,
        ],
        ClientUserNotesExportEvent::class             => [
            SendClientUserNotesExportListener::class,
        ],
        UpcomingSessionEmailEvent::class              => [
            UpcomingSessionEmailListener::class,
        ],
        SendDataExportEmailEvent::class               => [
            SendDataExportEmailListener::class,
        ],
        SendSessionNotesReminderEvent::class              => [
            SessionEmailListener::class,
        ],
        OccupationalHealthReportExportEvent::class              => [
            SendOccupationalHealthReportListener::class,
        ],
        ChallengeDetailExportEvent::class       => [
            ChallengeDetailExportEmailListener::class,
        ],
        CreditHistoryExportEvent::class               => [
            SendCreditHistoryExportListener::class,
        ],
        DigitalTherapyClientExportEvent::class              => [
            SendDigitalTherapyClientExportListener::class,
        ],
        UsageReportExportEvent::class              => [
            SendUsageReportListener::class,
        ],
        RealtimeAvailabilityEvent::class              => [
            RealtimeAvailabilityListener::class,
        ],
    ];
}
