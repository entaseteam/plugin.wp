<?php

namespace Entase\Plugins\WP\Hooks;

use Entase\Plugins\WP\Core\Shortcodes;

class WP {

    public static function Register()
    {
        self::ShortCodes();
    }

    public static function ShortCodes()
    {
        Shortcodes::Register();
    }


}