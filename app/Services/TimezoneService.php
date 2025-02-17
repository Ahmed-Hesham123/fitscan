<?php

namespace App\Services;

use App\Models\Timezone;
use Illuminate\Support\Facades\DB;
use Jenssegers\Agent\Facades\Agent;
use Stevebauman\Location\Facades\Location;

class TimezoneService
{
    // Function to determine timezone from user's IP (You'll need a library or service for this)
    public static function getTimezoneFromLocation($ip)
    {
        try {
            // Get the location based on IP address
            $location = Location::get(ClientInfo::getIp());

            // Get the timezone from the location
            $timezoneName = $location->timezone;

            return $timezoneName;
        } catch (\Exception $e) {
        }
    }

    // Function to get the timezone format (+/-HH:MM) from timezone name
    public static function getTimezoneFormat($timezoneName)
    {
        $dateTime = new \DateTime('now', new \DateTimeZone($timezoneName));
        return $dateTime->format('P');
    }

    // Function to get the timezone from database by timezone name
    public static function getTimezoneFromDatabase($timezoneName)
    {
        $timezone = Timezone::where('timezone_name', '=', $timezoneName)->first();
        return $timezone;
    }

    // Function to set app timezone
    public static function setAppTimezone($timezoneName)
    {
        // Set the timezone
        date_default_timezone_set($timezoneName);
        config(['app.timezone' => $timezoneName]);

        // Set the timezone in the database session
        $timezone_format = TimezoneService::getTimezoneFormat($timezoneName);
        DB::statement("SET @@session.time_zone = '$timezone_format'");
    }
}
