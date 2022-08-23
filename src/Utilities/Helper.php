<?php

namespace Entase\Plugins\WP\Utilities;

use Entase\Plugins\WP\Conf;

class Helper
{
    public static function OutputImportButton($role)
    {
        $atmf = \ATMF\GetEngine();
        $atmf->vars['role'] = $role;
        $atmf->RendTemplate('Snippets/ImportBtn');

        wp_enqueue_script('entase-import', Conf::JSUrl.'/admin/import.js', ['jquery'], false, true);
    }

    public static function CustomTableColumns($column)
    {
        global $post, $wp_query;
        if ($column == 'entase_photo_poster')
        {
            $meta = get_post_meta($post->ID, 'entase_photo', true);
            $photo = $meta != '' ? @json_decode($meta) : null;
            if ($photo != null) echo '<img src="'.$photo->poster->small.'" style="width:150px" />';
        }
    }

    public static function EscapeDocument($str='', $options=[]) 
    {
        $defaults = [
            'nl2br' => true
            ];
        $options = array_merge($defaults, $options);

        $str = htmlentities($str);
        if (!empty($options['nl2br']) && $options['nl2br'] === true) $str = nl2br($str);
        $str = preg_replace('/\s+/', ' ', $str);

        return $str;
    }

}