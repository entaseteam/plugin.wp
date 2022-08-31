<?php

namespace Entase\Plugins\WP\Hooks;

use Entase\Plugins\WP\Conf;
use Entase\Plugins\WP\Core\GeneralSettings;


class Cron 
{

    public static function Schedule()
    {
        $cronEnabled = GeneralSettings::Get('enable_cron');
        if ($cronEnabled)
        {
            // Add schedules
            add_filter('cron_schedules', [__CLASS__, 'GetSchedules']);

            // Schedule crons
            if(!wp_next_scheduled('entase_event_status_cron'))
                wp_schedule_event(time(), 'entase_10_minute', 'entase_event_status_cron');

            if(!wp_next_scheduled('entase_event_import_cron'))
                wp_schedule_event(time(), 'entase_15_minute', 'entase_event_import_cron');

            if(!wp_next_scheduled('entase_production_import_cron'))
                wp_schedule_event(time(), 'entase_25_minute', 'entase_production_import_cron');
        }
    }

    public static function Unregister()
    {
	    wp_unschedule_event(wp_next_scheduled('entase_event_status_cron'), 'entase_event_status_cron');
        wp_unschedule_event(wp_next_scheduled('entase_event_import_cron'), 'entase_event_import_cron');
        wp_unschedule_event(wp_next_scheduled('entase_production_import_cron'), 'entase_production_import_cron');
    }

    public static function GetSchedules($schedules)
    {
        $schedules['entase_10_minute'] = [
            'interval' => 600,
            'display'  => 'Entase - Every 10 minute'
        ];
        $schedules['entase_15_minute'] = [
            'interval' => 900,
            'display'  => 'Entase - Every 15 minute'
        ];
        $schedules['entase_25_minute'] = [
            'interval' => 1500,
            'display'  => 'Entase - Every 25 minute'
        ];
        return $schedules;
    }

    public static function Run($action='')
    {
        $cronEnabled = GeneralSettings::Get('enable_cron');
        if ($cronEnabled) \Entase\Plugins\WP\Core\Cron::Run($action);
    }
}