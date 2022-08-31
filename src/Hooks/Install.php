<?php

namespace Entase\Plugins\WP\Hooks;

class Install 
{
    public static function Register()
    {
        // Nothing to do here yet...
    }

    public static function Unregister()
    {
        Cron::Unregister();
    }
}
