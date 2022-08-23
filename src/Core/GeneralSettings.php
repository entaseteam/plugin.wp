<?php

namespace Entase\Plugins\WP\Core;

class GeneralSettings extends Settings
{
    public static $tableKey = 'entase_general_settings';
    public static $defaults = [
        'eventPosts' => [
            'enabled' => true,
            'slug' => 'events',
            'lastIDSync' => ''
        ],
        'productionPosts' => [
            'enabled' => true,
            'slug' => 'productions',
            'lastIDSync' => ''
        ],
        'do_flush_rewrite' => true
    ];
    public static $data = null;
}