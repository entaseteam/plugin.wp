<?php

namespace Entase\Plugins\WP\Core;

use Entase\Plugins\WP\Conf;
use Entase\Plugins\WP\Core\GeneralSettings;


class Cron {

    public static function Run($action)
    {
        if ($action == 'event_status')
            self::UpdateCurrentEvents();
        elseif ($action == 'event_import')
            self::ImportEvents();
        elseif ($action == 'production_import')
            self::ImportProductions();
    }

    public static function UpdateCurrentEvents()
    {
        Events::UpdateCurrent();
    }

    public static function ImportEvents()
    {
        Events::Import(true);
    }

    public static function ImportProductions()
    {
        Productions::Import(true);
    }
}