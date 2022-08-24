<?php

namespace Entase\Plugins\WP\Core;

class Shortcodes
{
    public static $photo = null;

    public static function Register()
    {
        $shortCodes = [
            'entase_title' => ['\Entase\Plugins\WP\Shortcodes\Meta', 'Do'],
            'entase_story' => ['\Entase\Plugins\WP\Shortcodes\Meta', 'Do'],
            'entase_id' => ['\Entase\Plugins\WP\Shortcodes\Meta', 'Do'],
            'entase_productionid' => ['\Entase\Plugins\WP\Shortcodes\Meta', 'Do'],
            'entase_link' => ['\Entase\Plugins\WP\Shortcodes\Meta', 'Do'],
            'entase_book' => ['\Entase\Plugins\WP\Shortcodes\Meta', 'Do'],
            'entase_photo_poster' => ['\Entase\Plugins\WP\Shortcodes\PhotoPoster', 'Do'],
            'entase_photo_og' => ['\Entase\Plugins\WP\Shortcodes\PhotoOG', 'Do'],
        ];

        foreach($shortCodes as $code => $handler)
        {
            add_shortcode($code, $handler);
        }
    }
}