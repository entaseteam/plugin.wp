<?php

namespace Entase\Plugins\WP\Shortcodes;

class PhotoOG extends BaseShortcode
{
    public static function Do($atts, $content, $tag)
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