<?php

namespace Entase\Plugins\WP\Hooks;

use \Entase\Plugins\WP\Core\GeneralSettings;

class Init {

    public static function Register()
    {
        self::ProductionPosts();
        self::EventPosts();
        self::FlushRewriteRules();
        
        // Schedule crons
        Cron::Schedule();
    }

    public static function ProductionPosts()
    {
        $productionPosts = GeneralSettings::Get('productionPosts');
        if (!$productionPosts['enabled']) return;

        $supports = ['title', 'thumbnail', 'editor'];
        $slug = $productionPosts['slug'];
        
        register_post_type('production', [
            'labels' => [
                'name' => __('Productions'),
                'singular_name' => __('Production'),
            ],
            'rewrite' => ['slug' => $slug],
            'description' => __('Productions'),
            'public' => true,
            'menu_position' => 20,
            'supports' => $supports,
            'taxonomies' => ['category', 'post_tag'],
            'menu_icon' => 'dashicons-art',
        ]);
    }

    public static function EventPosts()
    {
        $eventPosts = GeneralSettings::Get('eventPosts');
        if (!$eventPosts['enabled']) return;

        $supports = ['title', 'thumbnail', 'editor'];
        $slug = $eventPosts['slug'];
        
        register_post_type('event', [
            'labels' => [
                'name' => __('Events'),
                'singular_name' => __('Event'),
            ],
            'rewrite' => ['slug' => $slug],
            'description' => __('Events'),
            'public' => true,
            'publicly_queryable' => false,
            'menu_position' => 20,
            'supports' => $supports,
            'menu_icon' => 'dashicons-calendar',
        ]);
    }

    public static function FlushRewriteRules()
    {
        $doFlushRewrite = GeneralSettings::Get('do_flush_rewrite');
        if ($doFlushRewrite) {
            flush_rewrite_rules();
            GeneralSettings::Set('do_flush_rewrite', false, true);
        }
    }
}