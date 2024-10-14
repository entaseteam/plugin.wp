<?php

namespace Entase\Plugins\WP\Utilities;

use \DateTimeZone;
use \DateTime;

class Timezone 
{
    public static function ListAll()
    {
        $timezones = DateTimeZone::listIdentifiers();
        $results = [];

        foreach ($timezones as $timezone) 
        {
            $utcOffset = self::GetUTCOffset($timezone);
            $results[$timezone] = [
                'id' => $timezone,
                'name' => $timezone.' (UTC'.$utcOffset.')',
                //'offsetInSeconds' => $offsetInSeconds, // for sorting purposes
            ];
        }

        /*uasort($results, function($a, $b) {
            return $a['offsetInSeconds'] - $b['offsetInSeconds'];
        });*/

        return $results;
    }

    public static function IsValidIdentifier($id)
    {
        return in_array($id, array_keys(self::ListAll()));
    }

    public static function GetUTCOffset($timezone)
    {
        //Create DateTimeZone object
        $dtz = new DateTimeZone($timezone);

        // Create DateTime object, initialized to now
        $dt = new DateTime("now", $dtz);

        // Get the offset in seconds and convert to hours
        $offsetInSeconds = $dtz->getOffset($dt);
        $offsetInHours = $offsetInSeconds / 3600;

        // Format the offset
        return sprintf("%+d:%02d", $offsetInHours, abs($offsetInSeconds) % 3600 / 60);
    }

    public static function ConvertToTimezone($date, $timezone=null, $targetTimezone=null, $outputFormat="Y-m-d H:i")
    {
        if ($timezone == null || !self::IsValidIdentifier($timezone)) 
            return $date;

        if ($targetTimezone == null)
            $targetTimezone = wp_timezone_string(); //date_default_timezone_get();

        // Create a DateTime object with the given datetime and timezone
        $datetime = new DateTime($date, new DateTimeZone($timezone));

        // Convert into current script`s timezone
        $datetime->setTimezone(new DateTimeZone($targetTimezone));

        // Set converted date
        return $datetime->format($outputFormat);
    }
}