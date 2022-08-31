<?php

namespace Entase\Plugins\WP\Hooks;

use \Entase\Plugins\WP\Core\SettingsMenu;
use \Entase\Plugins\WP\Core\Productions;
use \Entase\Plugins\WP\Core\Events;

class Ajax 
{
    public static function Import()
    {
        if ($_POST['role'] == 'productions')
            Productions::Import();
        elseif ($_POST['role'] == 'events')
            Events::Import();
    }

    public static function Settings()
    {
        SettingsMenu::Save();
    }
}
