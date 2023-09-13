<?php

namespace Entase\Plugins\WP\Core;

class SkinSettings extends Settings
{
    public static $tableKey = 'entase_skins';
    public static $defaults = [
        'skins' => []
        /*'skins' => [
            'template' => '',
            'default' => false,
            'widget' => 'events'
        ]*/
    ];
    public static $data = null;
}