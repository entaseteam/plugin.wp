<?php

namespace Entase\Plugins\WP\Hooks;

class Register {

    public static function Init($sender)
    {
        // On install flush rules
        register_activation_hook($sender, ['Entase\Plugins\WP\Hooks\Install', 'Register']);

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

            // Hook to posts menus
            add_action('admin_head-edit.php', ['Entase\Plugins\WP\Hooks\Dashboard', 'PostsMenu']);

            // Hook to posts edit
            add_action('add_meta_boxes', ['Entase\Plugins\WP\Hooks\Dashboard', 'MetaBoxes']);
            add_action('save_post', ['Entase\Plugins\WP\Hooks\Dashboard', 'SavePost']);

            // AJAX
            add_action('wp_ajax_entase_import', ['Entase\Plugins\WP\Hooks\Ajax', 'Import']);
            //add_action( 'wp_ajax_nopriv_addItemAJAX', ['Entase\Plugins\WP\Hooks\Ajax', 'Import']);  
            //add_action( 'wp_ajax_addItemAJAX', ['Entase\Plugins\WP\Hooks\Ajax', 'Import']);
        }


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