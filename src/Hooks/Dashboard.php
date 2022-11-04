<?php

namespace Entase\Plugins\WP\Hooks;

use \Entase\Plugins\WP\Conf;
use \Entase\Plugins\WP\Core\Productions;
use \Entase\Plugins\WP\Core\Events;

class Dashboard 
{
    public static function AdminMenu()
    {
        // Add settings menu
        $iconb64 = file_exists(Conf::ImagePath.'/entaseicon.svg') ? base64_encode(file_get_contents(Conf::ImagePath.'/entaseicon.svg')) : '';
        add_menu_page(
            'Entase',
            'Entase',
            'manage_options',
            'entase-settings',
            ['Entase\Plugins\WP\Hooks\Dashboard', 'SettingsMenu'],
            'data:image/svg+xml;base64,'.$iconb64,
            plugins_url( 'myplugin/images/icon.png' ),
            80
        );
    }

    public static function SettingsMenu()
    {
        if (!current_user_can('manage_options'))
            wp_die(__('You do not have sufficient permissions to access this page.'));

        \Entase\Plugins\WP\Core\SettingsMenu::DisplayPage();
    }

    public static function PostsMenu()
    {
        Productions::PostsMenu();
        Events::PostsMenu();
    }    

    public static function MetaBoxes()
    {
        Productions::MetaBoxes();
        Events::MetaBoxes();
    }

    public static function SavePost($postID)
    {
        if (!current_user_can('edit_post', $postID)) return;
        if (wp_is_post_autosave($postID)) return;
        if (wp_is_post_revision($postID)) return;

		$post = get_post($postID);
		if ($post == null) return;

		if ($post->post_type == 'production') Productions::Save($post);
        elseif ($post->post_type == 'event') Events::Save($post);
    }

    public static function GlobalScriptsBE()
    {
        wp_enqueue_script('entaseglobal', Conf::JSUrl.'/admin/global.js', ['jquery'], false, true);
        wp_enqueue_style('entaseglobal', Conf::CSSUrl.'/admin/global.css');
    }
}