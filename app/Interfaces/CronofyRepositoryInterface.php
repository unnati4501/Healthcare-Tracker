<?php

namespace App\Interfaces;

/**
 * Class CronofyRepositoryInterface
 */
interface CronofyRepositoryInterface
{
    /**
     * Cronofy Authenticate method
     */
    public function authenticate();

    /**
     * Cronofy Authenticate callback method
     * @param $code
     */
    public function callback($code);

    /**
     * Cronofy Authenticate refresh token method
     * @param $authentication
     */
    public function refreshToken($authentication);

    /**
     * Cronofy link calendar
     */
    public function linkCalendar();

    /**
     * Cronofy Unlink calendar
     */
    public function unlinkCalendar($profileId);

    /**
     * Cronofy update availability slot
     */
    public function updateAvailability($slots, $user, $availabilityTimezone = "", $isSequence = true, $appTimezone = '', $duration);

    /**
     * Cronofy real time scheduling
     */
    public function realTimeScheduling($user, $company, $loginUser, $serviceId, $type = '', $eventId = "", $isRescheduled = false, $healthCoachUnavailable = [], $digitalTherapySlot = [], $combinedAvailability = [], $specificAvailabilities = []);

    /**
     * Cronofy Event Id cancel
     */
    public function cancelEvent($wsId, $eventId);

    /**
     * Cronofy UI Element Token
     */
    public function dateTimePicker($userId, $currentUrl = null);

    /**
     * Cronofy Create Event
     */
    public function createEvent(array $param, array $inviteUsers = []);

    /**
     * Cronofy Custom Availablity Slot
     */
    public function customAvailability($slot, $user, $company, $serviceId);

    /**
     * Cronofy Availability Rules are remove
     */
    public function availabilityRuleRemove($user);

    /**
     * Get all events created in calender
     */
    public function getEvents($userId, $params = []);

    /**
     * Get all events created in calender
     */
    public function getFreeBuzySlots($userId, $params = []);
}
