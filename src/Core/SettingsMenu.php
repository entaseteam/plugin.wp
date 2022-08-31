<?php

namespace Entase\Plugins\WP\Core;

use Entase\Plugins\WP\Conf;
use Entase\Plugins\WP\Hooks\Cron;
use Entase\Plugins\WP\Utilities\Ajax;

class SettingsMenu
{
    public static function DisplayPage()
    {
        wp_enqueue_script('entase_settings', Conf::JSUrl.'/admin/settings.js', ['jquery'], false, true);

        $api = GeneralSettings::Get('api');

        $atmf = \ATMF\GetEngine();
        $atmf->vars['api_sk'] = $api['sk'] != '' ? '********************************' : '';
        $atmf->vars['api_pk'] = $api['pk'] != '' ? '********************************' : '';
        $atmf->vars['eventPosts'] = GeneralSettings::Get('eventPosts');
        $atmf->vars['productionPosts'] = GeneralSettings::Get('productionPosts');
        $atmf->vars['enable_cron'] = GeneralSettings::Get('enable_cron');
        $atmf->RendTemplate('Pages/GeneralSettings');
    }

    public static function Save()
    {
        $hasSave = false;
        foreach ($_POST as $key => $value) 
        {
            switch($key)
            {
                case 'api_secret_key':
                    $api = GeneralSettings::Get('api');
                    $api['sk'] = $value;
                    GeneralSettings::Set('api', $api, false);
                    $hasSave = true;
                    break;
                case 'api_public_key':
                    $api = GeneralSettings::Get('api');
                    $api['pk'] = $value;
                    GeneralSettings::Set('api', $api, false);
                    $hasSave = true;
                    break;
                case 'productions_slug';
                    $productionPosts = GeneralSettings::Get('productionPosts');
                    $productionPosts['slug'] = $value;
                    GeneralSettings::Set('productionPosts', $productionPosts, false);
                    GeneralSettings::Set('do_flush_rewrite', true, false);
                    $hasSave = true;
                case 'enable_cron':
                    $enableCron = ($value == 'enable');
                    GeneralSettings::Set('enable_cron', $enableCron, false);
                    if (!$enableCron) Cron::Unregister();
                    $hasSave = true;
                    break;
            }
        }

        if ($hasSave)
        {
            if (GeneralSettings::Write()) Ajax::StatusOK();
            else Ajax::StatusERR('Settings update failed.');
        }
        else Ajax::StatusERR('No settings were changed.');

    }
}