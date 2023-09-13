<?php

namespace Entase\Plugins\WP\Shortcodes;

use \Entase\Plugins\WP\Utilities\Helper;
use \Entase\Plugins\WP\Utilities\Shortcodes;

class Meta extends BaseShortcode
{
    public static function Do($atts, $content, $tag)
    {
        $post = get_post();
        $atts = array_merge([
            'markup2html' => true,
            'booklabel' => 'Book'
        ], is_array($atts) ? $atts : []);

        $tag = strtolower($tag);
        if ($tag == 'entase_productionid')
            $tag = 'entase_productionID';
        
        $value = '';
        if (in_array($tag, ['entase_id', 'entase_link']))
        {            
            $value = (string)get_post_meta($post->ID, $tag, true);
        }
        elseif (in_array($tag, ['entase_title', 'entase_story', 'entase_productionID']))
        {
            $production = self::GetRelatedProduction();
            if ($production != null)
            {
                if ($tag == 'entase_productionID')
                {
                    $value = (string)get_post_meta($production->ID, 'entase_id', true);
                }
                else 
                {
                    $metaValue = (string)get_post_meta($production->ID, $tag, true);
                    $value = Helper::EscapeDocument($metaValue);
                    if ($tag == 'entase_story' && $atts['markup2html'] === true)
                        $value = Shortcodes::MarkupToHTML($value, $atts);
                }
            }
        }
        elseif (in_array($tag, ['entase_book']))
        {
            if ($post->post_type == 'event')
            {
                $entaseID = get_post_meta($post->ID, 'entase_id', true);
                $entaseStatus = get_post_meta($post->ID, 'entase_status', true);
                $value = '<a href="javascript:void(0);" class="entase_book" data-event="'.$entaseID.'" data-status="'.$entaseStatus.'">'.$atts['booklabel'].'</a>';
            }
        }

        $value = apply_filters($tag, $value);

        return $value;
    }
}