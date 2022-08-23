<?php

namespace Entase\Plugins\WP\Core;

use \Entase\Plugins\WP\Utilities\Helper;

class Shortcodes
{
    public static $photo = null;

    public static function Register()
    {
        $shortCodes = [
            'entase_title' => [__CLASS__, 'Title'],
            'entase_story' => [__CLASS__, 'Story'],
            'entase_photo_poster' => [__CLASS__, 'PhotoPoster'],
            'entase_photo_og' => [__CLASS__, 'PhotoOG'],
        ];

        foreach($shortCodes as $code => $handler)
        {
            add_shortcode($code, $handler);
        }
    }

    public static function GetRelatedProduction()
    {
        $post = get_post($post);
        if ($post)
        {
            if ($post->post_type == 'production')
                return $post;
        }

        return null;
    }

    public static function ExtractMetaPhoto($post)
    {
        if (self::$photo == null)
        {
            $meta = get_post_meta($post->ID, 'entase_photo', true);
            self::$photo = $meta != '' ? @json_decode($meta) : null;
        }
    }

    public static function Title($atts)
    {
        //$atts = shortcode_atts([], $atts);
        $post = self::GetRelatedProduction();
        if ($post != null)
        {
            $story = get_post_meta($post->ID, 'entase_title', true);
            return Helper::EscapeDocument($story);
        }

        return '';
    }

    public static function Story($atts)
    {
        $atts = array_merge([
            'markup2html' => true
        ], is_array($atts) ? $atts : []);

        $post = self::GetRelatedProduction();
        if ($post != null)
        {
            $story = get_post_meta($post->ID, 'entase_story', true);
            $story = Helper::EscapeDocument($story);
            if ($atts['markup2html'] === true)
                $story = \Entase\Plugins\WP\Utilities\Shortcodes::MarkupToHTML($story, $atts);

            return $story;
        }

        return '';
    }

    public static function PhotoPoster($atts)
    {
        $atts = array_merge([
            'size' => 'medium',
            'srconly' => false,
        ], is_array($atts) ? $atts : []);

        $post = self::GetRelatedProduction();
        if ($post != null)
        {
            self::ExtractMetaPhoto($post);
            
            $poster = self::$photo->poster ?? null;
            if ($poster != null) 
            {
                $size = in_array($atts['size'], ['small', 'medium', 'large']) ? $atts['size'] : 'medium';
                return $atts['srconly'] ? $poster->$size : '<img src="'.$poster->$size.'" class="entase_meta_poster" />';
            }
        }

        return '';
    }

    public static function PhotoOG($atts)
    {
        $atts = array_merge([
            'size' => 'large',
            'srconly' => false
        ], is_array($atts) ? $atts : []);

        $post = self::GetRelatedProduction();
        if ($post != null)
        {
            self::ExtractMetaPhoto($post);
            
            $og = self::$photo->og ?? null;
            if ($og != null) 
            {
                $size = in_array($atts['size'], ['large']) ? $atts['size'] : 'large';
                return $atts['srconly'] ? $og->$size : '<img src="'.$og->$size.'" class="entase_meta_og" />';
            }
        }

        return '';
    }
}