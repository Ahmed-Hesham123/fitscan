<?php

namespace App\Services;

use Jenssegers\Agent\Facades\Agent;
use Stevebauman\Location\Facades\Location;

class ClientInfo
{
    public static function getLocation()
    {
        $result = "";

        // get location
        $location = Location::get(self::getIp());

        if ($location) {
            // add county if exist
            if ($location->countryName) {
                $result .= $location->countryName;
            }

            // add region if exist
            if ($location->regionName) {
                $result = self::addComma($result);
                $result .= $location->regionName;
            }

            // add city if exist
            if ($location->cityName) {
                $result = self::addComma($result);
                $result .= $location->cityName;
            }
        }

        // if no information exist
        if ($result == '') {
            if (app()->isLocale('ar')) {
                $result = "غير معروف";
            } else {
                $result = "unknown";
            }
        } else {
            if (app()->isLocale('ar')) {
                $result = "بالقرب من " . $result;
            } else {
                $result = "Near " . $result;
            }
        }

        return $result;
    }

    public static function getAgent()
    {
        $result = "";

        $device = Agent::device();
        $browser = Agent::browser();
        $browserVersion = Agent::version($browser);
        $platform = Agent::platform();
        $platformVersion = Agent::version($platform);

        // add device if exist
        if ($device) {
            $result = self::addComma($result);
            $result .= $device;
        }
        // add browser if exist
        if ($browser) {
            $result = self::addComma($result);
            $result .= $browser;
        }
        // add browser version if exist
        if ($browserVersion) {
            $result .= ' ' . $browserVersion;
        }
        // add platform if exist
        if ($platform) {
            $result = self::addComma($result);
            $result .= $platform;
        }
        // add platform version if exist
        if ($platformVersion) {
            $result .= ' ' . $platformVersion;
        }

        // if no information exist
        if ($result == '') {
            if (app()->isLocale('ar')) {
                $result = "غير معروف";
            } else {
                $result = "unknown";
            }
        }

        return $result;
    }

    public static function addComma($str)
    {
        if ($str != '') {
            $str .= ', ';
        }

        return $str;
    }

    public static function getIp()
    {
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    // just to be safe
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return null; // it will return the server IP if the client IP is not found using this method.
    }
}
