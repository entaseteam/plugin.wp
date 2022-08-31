<?php

namespace Entase\Plugins\WP\Hooks;

class Update
{
    public static function Check($update, $plugin_data, $plugin_file)
    {
        touch(\Entase\Plugins\WP\Conf::BasePath.'/update.log');
        static $response = false;
        
        if (empty( $plugin_data['UpdateURI'] ) || !empty($update))
            return $update;
        
        if($response === false)
            $response = wp_remote_get($plugin_data['UpdateURI']);
        
        if(empty($response['body']))
            return $update;
        
        $custom_plugins_data = json_decode($response['body'],true);
        
        if(!empty($custom_plugins_data[$plugin_file]))
            return $custom_plugins_data[$plugin_file];
        else return $update;
    }
}