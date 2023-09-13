<?php

namespace Entase\Plugins\WP\Core\Dashboard;

use \Entase\Plugins\WP\Conf;
use \Entase\Plugins\WP\Core\GeneralSettings;

class GeneralSettingsPage extends BasePage
{
    function __construct() 
    {
        parent::__construct();        
    }

    public function Load()
    {
        $api = GeneralSettings::Get('api');
        
        __('$api_sk', $api['sk'] != '' ? '********************************' : '');
        __('$api_pk', $api['pk'] != '' ? '********************************' : '');
        __('$eventPosts', GeneralSettings::Get('eventPosts'));
        __('$productionPosts', GeneralSettings::Get('productionPosts'));
        __('$enable_cron', GeneralSettings::Get('enable_cron'));
        
        self::Rend('Pages/GeneralSettings');
    }

    public static function Register()
    {
        self::AddPage('general', new GeneralSettingsPage());
    }
}
