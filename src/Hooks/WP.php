<?php

namespace Entase\Plugins\WP\Hooks;

use Entase\Plugins\WP\Conf;
use Entase\Plugins\WP\Core\Shortcodes;
use Entase\Plugins\WP\Core\FEPages;


class WP {

    public static function Register()
    {
        self::GlobalScriptsFE();
        Shortcodes::Register();
        FEPages::Load();
    }

    public static function GlobalScriptsFE()
    {
        wp_enqueue_script('entaseclient', Conf::JSUrl.'/entaseclient.js', ['jquery'], false, true);
        wp_enqueue_script('entase', Conf::JSUrl.'/front/entase.js', ['jquery', 'entaseclient'], false, true);
    }

}