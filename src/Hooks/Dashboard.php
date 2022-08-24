<?php

namespace Entase\Plugins\WP\Hooks;

use \Entase\Plugins\WP\Core\Productions;
use \Entase\Plugins\WP\Core\Events;

class Dashboard 
{
    public static function AdminMenu()
    {
        // Add settings menu
        add_submenu_page(
            'options-general.php',
            'Entase',
            'Entase',
            'manage_options',
            'entase-settings',
            ['Entase\Plugins\WP\Hooks\Dashboard', 'SettingsMenu']
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

    public static function SavePost($id)
    {
        if (!current_user_can('edit_post', $postID)) return;
        if (wp_is_post_autosave($postID)) return;
        if (wp_is_post_revision($postID)) return;

		$post = get_post($postID);
		if ($post == null) return;

		if ($post->post_type == 'production') Productions::Save($post);
        //elseif ($post->post_type == 'event') Events::Save($post);
    }
}