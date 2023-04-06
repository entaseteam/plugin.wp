<?php

namespace Entase\Plugins\WP\Core;

class EntaseSDK 
{
    public static function PrepareClient($sk=null) 
    {
        $key = $sk;
        if ($key == null) {
            $api = GeneralSettings::Get('api');
            $key = $api['sk'] ?? '';
        }
        
        return new \Entase\SDK\Client($key);
    }
}
