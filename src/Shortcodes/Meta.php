<?php

namespace Entase\Plugins\WP\Shortcodes;

use \Entase\Plugins\WP\Utilities\Helper;
use \Entase\Plugins\WP\Utilities\Shortcodes;

class Meta extends BaseShortcode
{
    public static function Do($atts, $content, $tag)
    {
        $post = get_post($post);
        $atts = array_merge([
            'markup2html' => true
        ], is_array($atts) ? $atts : []);

        $tag = strtolower($tag);
        if ($tag == 'entase_productionid')
            $tag = 'entase_productionID';
        
        $value = '';
        if (in_array($tag, ['entase_id', 'entase_productionID']))
        {
            $value = Helper::EscapeDocument((string)get_post_meta($post->ID, $tag, true));
        }
        elseif (in_array($tag, ['entase_title', 'entase_story']))
        {
            $production = self::GetRelatedProduction();
            if ($production != null)
            {
                $value = Helper::EscapeDocument((string)get_post_meta($production->ID, $tag, true));
                if ($tag == 'entase_story' && $atts['markup2html'] === true)
                    $value = Shortcodes::MarkupToHTML($value, $atts);
            }
        }

        return $value;
    }
}