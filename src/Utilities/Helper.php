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
        elseif ($column == 'entase_dateStart')
        {
            $timeStart = (int)get_post_meta($post->ID, 'entase_dateStart', true);
            // echo date('Y/m/d \a\t H:i', $timeStart);
            
            // Handling WP time zones
            echo get_date_from_gmt(date('Y-m-d H:i', $timeStart), 'Y/m/d \a\t H:i');
        }
        elseif ($column == 'entase_status')
        {
            $status = (int)get_post_meta($post->ID, 'entase_status', true);
            echo '<div class="entase_status_badge" data-status="'.$status.'">';
            switch($status)
            {
                case 0:
                    echo 'Pending';
                    break;
                case 1:
                    echo 'Open Sell';
                    break;
                case 2:
                    echo 'Closed Sell';
                    break;
                case 3:
                    echo 'Finsihed';
                    break;
                case 4:
                    echo 'Canceled';
                    break;
                case 5:
                    echo 'Rescheduling';
                    break;
            }
            echo '</div>';
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