<?php

namespace Entase\Plugins\WP\Shortcodes;

class BaseShortcode
{
    public static $photo = null;

    public static function Do($atts, $content, $tag)
    {
        return '';
    }

    public static function GetRelatedProduction($post=null)
    {        
        if ($post == null) 
            $post = get_post();

        if ($post)
        {
            if ($post->post_type == 'production')
                return $post;
            elseif ($post->post_type == 'event')
            {
                $entasePID = get_post_meta($post->ID, 'entase_productionID', true);
                $posts = get_posts([
                    'post_type' => 'production',
                    'meta_key' => 'entase_id',
                    'meta_value' => $entasePID
                ]);
                
                if ($posts) 
                    return $posts[0];
            }
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
}