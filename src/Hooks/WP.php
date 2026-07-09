<?php

namespace Entase\Plugins\WP\Hooks;

use Entase\Plugins\WP\Conf;
use Entase\Plugins\WP\Core\Shortcodes;
use Entase\Plugins\WP\Core\FEPages;
use Entase\Plugins\WP\Core\GeneralSettings;


class WP {

    public static function Register()
    {
        self::GlobalScriptsFE();
        Shortcodes::Register();
        FEPages::Load();
    }

    public static function GlobalScriptsFE()
    {
        wp_enqueue_script('entaseclient', 'https://js.entase.com/client.js', ['jquery'], false, true);
        wp_enqueue_script('entase', Conf::JSUrl.'/front/entase.js', ['jquery', 'entaseclient'], false, true);

        $api = GeneralSettings::Get('api');
        $publicKey = '';
        if (is_array($api) && isset($api['pk'])) $publicKey = $api['pk'];

        wp_localize_script('entase', 'entaseWPSettings', [
            'pk' => $publicKey
        ]);
    }

}