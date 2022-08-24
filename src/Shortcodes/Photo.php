<?php

namespace Entase\Plugins\WP\Shortcodes;

class Photo extends BaseShortcode
{
    public static function Do($atts, $content, $tag)
    {
        $atts = array_merge([
            'size' => 'large',
            'srconly' => false
        ], is_array($atts) ? $atts : []);

        if ($tag == 'entase_photo_og') return self::PhotoOG($atts);
        elseif ($tag == 'entase_photo_poster') return self::PhotoPoster($atts);
        else return '';
    }

    public static function PhotoOG($atts)
    {
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

    public static function PhotoPoster($atts)
    {
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
}