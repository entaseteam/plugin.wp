<?php

namespace Entase\Plugins\WP\Shortcodes;

class PhotoPoser extends BaseShortcode
{
    public static function Do($atts, $content, $tag)
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
}