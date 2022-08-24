<?php

namespace Entase\Plugins\WP\Hooks;

use Entase\Plugins\WP\Conf;
use Entase\Plugins\WP\Core\Shortcodes;


class WP {

    public static function Register()
    {
        self::ShortCodes();
        self::GlobalScriptsFE();
    }

    public static function ShortCodes()
    {
        Shortcodes::Register();
    }

    public static function GlobalScriptsFE()
    {
        wp_enqueue_script('entaseclient', Conf::JSUrl.'/entaseclient.js', ['jquery'], false, true);
        wp_enqueue_script('entase', Conf::JSUrl.'/front/entase.js', ['jquery', 'entaseclient'], false, true);
    }


}