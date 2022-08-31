<?php

namespace Entase\Plugins\WP\Core;

class EntaseSDK 
{
    public static function PrepareClient() 
    {
        $api = GeneralSettings::Get('api');
        return new \Entase\SDK\Client($api['sk']);
    }
}
