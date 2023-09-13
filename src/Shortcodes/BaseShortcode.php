<?php

namespace Entase\Plugins\WP\Shortcodes;

class BaseShortcode
{
    public static $photos = [];


    public static function Do($atts, $content, $tag)
    {
        return '';
    }

    public static function GetRelatedProduction($post=null)
    {
        global $wp_query;

        //echo get_the_ID()."\r\n\r\n<br><br>";
        //echo get_queried_object_id()."\r\n\r\n<br><br>";
        //print_r($wp_query->post->ID);
        //echo "\r\n\r\n<br><br>";
        //print_r(get_post()->ID);

        //echo '___________'."\r\n\r\n<br><br>";

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
        if (!isset(self::$photos[$post->ID]))
        {
            $meta = get_post_meta($post->ID, 'entase_photo', true);
            self::$photos[$post->ID] = $meta != '' ? @json_decode($meta) : null;
        }
        
        return self::$photos[$post->ID];
    }
}