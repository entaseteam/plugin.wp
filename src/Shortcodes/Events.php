<?php

namespace Entase\Plugins\WP\Shortcodes;

use \Entase\Plugins\WP\Conf;

class Events extends BaseShortcode
{
    public static function Do($atts, $content, $tag)
    {
        
        $atts = array_merge([
            // Content
            'nostyles' => false,
            'limit' => 0,
            'fields' => ['entase_photo_poster', 'post_title', 'entase_dateonly', 'entase_timeonly', 'entase_book'],

            // Query
            'filter_status' => [1],
            'filter_productions' => [],
            'filter_current_production' => 'no',
            'allow_qs_production' => 'no',
            'allow_qs_date' => 'no',

            // Customize
            'targeturl' => 'book',
            'booklabel' => 'Book',
            'dateformat' => 'd/m',
            'timeformat' => 'H:i',
            
        ], is_array($atts) ? $atts : []);

        if (!$atts['nostyles'])
            wp_enqueue_style('entase-widget-events', Conf::CSSUrl.'/front/widgets/events-classic.css');

        $query = [
            'post_type' => 'event',
            'posts_per_page' => $limit > 0 ? $limit : -1,
            'tax_query' => []
        ];

        $items = [];
        $posts = get_posts($query);
        if ($posts && count($posts) > 0)
        {
            foreach($posts as $post)
            {
                $photo = null;
                $row = [];

                $production = null;
                if($atts['targeturl'] == 'production' ||
                    count(array_intersect(['entase_title', 'entase_story', 'entase_photo_poster', 'entase_photo_og'], $atts['fields'] )) > 0)
                    $production = self::GetRelatedProduction($post);

                foreach ($atts['fields'] as $field)
                {
                    switch($field)
                    {
                        case 'post_title':
                            $row[] = ['key' => 'post_title', 'val' => $post->post_title];
                            break;
                        case 'post_content':
                            $row[] = ['key' => 'post_content', 'val' => $post->post_content];
                            break;
                        case 'post_feature_image':
                            $row[] = ['key' => 'post_feature_image', 'val' => get_the_post_thumbnail($post->ID, 'large')];
                            break;
                        case 'entase_title':
                            $row[] = ['key' => 'entase_title', 'val' => get_post_meta($production->ID, 'entase_title', true)];
                            break;
                        case 'entase_story':
                            $row[] = ['key' => 'entase_story', 'val' => get_post_meta($production->ID, 'entase_story', true)];
                            break;
                        case 'entase_datestart':
                            $time = (int)get_post_meta($post->ID, 'entase_dateStart', true);
                            $datestr = date($atts['dateformat'].' - '.$atts['timeformat'], $time);
                            $row[] = ['key' => 'entase_datestart', 'val' => $datestr];
                            break;
                        case 'entase_dateonly':
                            $time = (int)get_post_meta($post->ID, 'entase_dateStart', true);
                            $datestr = date($atts['dateformat'], $time);
                            $row[] = ['key' => 'entase_dateonly', 'val' => $datestr];
                            break;
                        case 'entase_timeonly':
                            $time = (int)get_post_meta($post->ID, 'entase_dateStart', true);
                            $datestr = date($atts['timeformat'], $time);
                            $row[] = ['key' => 'entase_timeonly', 'val' => $datestr];
                            break;
                        case 'entase_book':
                            $eventID = get_post_meta($post->ID, 'entase_id', true);
                            $row[] = ['key' => 'entase_dateonly', 'val' => '<a href="javascript:void(0);" class="entase_book" data-event="'.$eventID.'">'.$atts['booklabel'].'</a>'];
                            break;
                        case 'entase_photo_poster':
                            if ($photo == null)
                            {
                                $meta =  get_post_meta($production->ID, 'entase_photo', true);
                                $photo = @json_decode($meta) ?? null;
                            }
                            $row[] = ['key' => 'entase_photo_poster', 'val' => $photo != null ? '<img src="'.$photo->poster->medium.'" />' : ''];
                            break;
                        case 'entase_photo_og':
                            if ($photo == null)
                            {
                                $meta =  get_post_meta($production->ID, 'entase_photo', true);
                                $photo = @json_decode($meta) ?? null;
                            }
                            $row[] = ['key' => 'entase_photo_og', 'val' => $photo != null ? '<img src="'.$photo->og->large.'" />' : ''];
                            break;
                    }
                }

                $item = [
                    'entase_id' => get_post_meta($post->ID, 'entase_id', true),
                    'fields' => $row
                ];
                
                if ($atts['targeturl'] == 'book')
                {
                    $item['url'] = 'javascript:void(0);';
                    $item['allowbook'] = true;
                }
                elseif ($atts['targeturl'] == 'production')
                {                    
                    $item['url'] = $production != null ? esc_url(get_permalink($production)) : '#';
                }

                $items[] = $item;
            }
        }

        $atmf = \ATMF\Setup::GetEngine();
        $atmf->vars['items'] = $items;

        return $atmf->RendTemplate('Widgets/Events_Classic', true);
    }
}