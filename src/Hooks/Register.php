<?php

namespace Entase\Plugins\WP\Hooks;

class Register {

    public static function Init($sender)
    {
        // On install flush rules
        register_activation_hook($sender, ['Entase\Plugins\WP\Hooks\Install', 'Register']);
        register_deactivation_hook($sender,  ['Entase\Plugins\WP\Hooks\Install', 'Unregister']);

        // General
        add_action('init', ['Entase\Plugins\WP\Hooks\Init', 'Register']);
        add_action('wp', ['Entase\Plugins\WP\Hooks\WP', 'Register']);

        // Hook to Elementor
        add_action('elementor/dynamic_tags/register', ['Entase\Plugins\WP\Hooks\Elementor', 'DynamicTags']);
        add_action('elementor/widgets/register', ['Entase\Plugins\WP\Hooks\Elementor', 'Widgets']);

        // Admin side
        if (is_admin()) {

            // Add settings menu
            add_action('admin_menu', ['Entase\Plugins\WP\Hooks\Dashboard', 'AdminMenu']);
            add_action('admin_enqueue_scripts', ['Entase\Plugins\WP\Hooks\Dashboard', 'GlobalScriptsBE']);

            // Hook to posts menus
            add_action('admin_head-edit.php', ['Entase\Plugins\WP\Hooks\Dashboard', 'PostsMenu']);

            // Hook to posts edit
            add_action('add_meta_boxes', ['Entase\Plugins\WP\Hooks\Dashboard', 'MetaBoxes']);
            add_action('save_post', ['Entase\Plugins\WP\Hooks\Dashboard', 'SavePost']);

            // AJAX
            add_action('wp_ajax_entase_import', ['Entase\Plugins\WP\Hooks\Ajax', 'Import']);
            add_action('wp_ajax_entase_settings', ['Entase\Plugins\WP\Hooks\Ajax', 'Settings']);
            add_action('wp_ajax_entase_updateskin', ['Entase\Plugins\WP\Hooks\Ajax', 'UpdateSkin']);
            add_action('wp_ajax_entase_deleteskin', ['Entase\Plugins\WP\Hooks\Ajax', 'DeleteSkin']);
            //add_action( 'wp_ajax_nopriv_addItemAJAX', ['Entase\Plugins\WP\Hooks\Ajax', 'Import']);  
            //add_action( 'wp_ajax_addItemAJAX', ['Entase\Plugins\WP\Hooks\Ajax', 'Import']);
        }        

        // Register crons
        add_action('entase_event_status_cron', function() { \Entase\Plugins\WP\Hooks\Cron::Run('event_status'); });
        add_action('entase_event_import_cron', function() { \Entase\Plugins\WP\Hooks\Cron::Run('event_import'); });
        add_action('entase_production_import_cron', function() { \Entase\Plugins\WP\Hooks\Cron::Run('production_import'); });

        // Register update channel
        add_filter('update_plugins_github.com', ['Entase\Plugins\WP\Hooks\Update', 'Check'], 10, 3);
        

        /*register_activation_hook($sender, ['\FTAPI\Hooks\Install', 'Register']);
        add_action('init', ['\FTAPI\Hooks\Init', 'Register']);
        add_action('wp', ['\FTAPI\Hooks\WP', 'Register']);

        add_action('after_setup_theme', ['\FTAPI\Hooks\Images', 'Register']);
        
        if (is_admin()) {
            add_action( 'admin_menu', ['\FTAPI\Hooks\Dashboard', 'AdminMenu'] );
            add_action( 'add_meta_boxes', ['\FTAPI\Hooks\Dashboard', 'MetaBoxes'] );
            add_action( 'save_post', ['\FTAPI\Hooks\Dashboard', 'SaveMeta'] );
        }*/

    }
}