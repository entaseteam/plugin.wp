<?php

namespace Entase\Plugins\WP\Shortcodes;

use \Entase\Plugins\WP\Conf;

class Events extends BaseShortcode
{
    public static function Do($atts, $content, $tag)
    {
     
        /* ************ */
        /* DEFAULT ARGS */
        /* ************ */
        $atts = array_merge([
            // Content
            'nostyles' => false,
            'limit' => 0,
            'sort' => 'entase_dateStart/asc',
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

        
        /* ********************* */
        /* Elementor load styles */
        /* in different manner   */
        /* ********************* */
        if (!$atts['nostyles'])
            wp_enqueue_style('entase-widget-events', Conf::CSSUrl.'/front/widgets/events-classic.css');


        /* ******************** */
        /* SANITIZE QUERY ARGS */
        /* ******************** */
        $statuses = is_string($atts['filter_status']) ? explode(',', $atts['filter_status']) : $atts['filter_status'];
        $productions = is_string($atts['filter_productions']) ? explode(',', $atts['filter_productions']) : $atts['filter_productions'];

        // Find and add current production
        if ($atts['filter_current_production'] == 'yes')
        {
            $productionID = '';
            $post = get_queried_object();
            if ($post && $post->post_type == 'production')
                $productionID = get_post_meta($post->ID, 'entase_id', true);
            else $productionID = '--';

            $productions[] = $productionID;
        }

        // Get from query string if any
        if ($atts['allow_qs_production'] == 'yes')
        {
            if (!empty($_GET['prod']))
                $productions[] = $_GET['prod'];
        }

        /* *********** */
        /* BUILD QUERY */
        /* *********** */
        $query = [
            'post_type' => 'event',
            'posts_per_page' => $limit > 0 ? $limit : -1,
            'meta_query' => [],
            'tax_query' => []
        ];

        // Add status filter
        if (count($statuses) > 0)
        {
            $query['meta_query'][] = [
                'key' => 'entase_status',
                'value' => $statuses,
                'compare' => 'IN'
            ];
        }

        // Add productions filter
        if (count($productions) > 0)
        {
            $query['meta_query'][] = [
                'key' => 'entase_productionID',
                'value' => $productions,
                'compare' => 'IN'
            ];
        }

        // Add date filter
        if ($atts['allow_qs_date'] == 'yes')
        {
            if (!empty($_GET['date']))
            {
                $dates = explode('-', $_GET['date']);
                $startDate = trim($dates[0] ?? '');
                $endDate = trim($dates[1] ?? '');

                if (strlen($startDate) == 8)
                    $startDate = strtotime(substr($startDate, 0, 4).'-'.substr($startDate, 4, 2).'-'.substr($startDate, 6, 2).' 00:00:00');
                else $startDate = 0;

                if (strlen($endDate) == 8)
                    $endDate = strtotime(substr($endDate, 0, 4).'-'.substr($endDate, 4, 2).'-'.substr($endDate, 6, 2).' 23:59:00');
                else $endDate = 0;

                if ($startDate > 0)
                    $query['meta_query'][] = [
                        'key' => 'entase_dateStart',
                        'value' => $startDate,
                        'compare' => '>='
                    ];

                if ($endDate > 0)
                    $query['meta_query'][] = [
                        'key' => 'entase_dateStart',
                        'value' => $endDate,
                        'compare' => '<='
                    ];
            }
        }

        // Add sorting
        $sortCond = explode('/', $atts['sort']);
        $sortField = $sortCond[0] ?? null;
        $sortOrder = $sortCond[1] ?? null;
        if ($sortField != null && $sortOrder != null)
        {
            $query['meta_key'] = $sortField;
            $query['order'] = $sortOrder;
            $query['orderby'] = 'meta_value_num';
        }

        /* ******************* */
        /* BUILD TEMPLATE DATA */
        /* ******************* */
        $items = [];
        $posts = get_posts($query);
        if ($posts && count($posts) > 0)
        {
            foreach($posts as $post)
            {
                $photo = null;
                $row = [];
                $itemProps = [];

                // Extract related production if needed
                $production = null;
                if($atts['targeturl'] == 'production' ||
                    count(array_intersect(['entase_title', 'entase_story', 'entase_photo_poster', 'entase_photo_og'], $atts['fields'] )) > 0)
                    $production = self::GetRelatedProduction($post);

                // Add fields
                foreach ($atts['fields'] as $field)
                {
                    switch($field)
                    {
                        case 'post_title':
                            $itemProps['post_title'] = $post->post_title;
                            break;
                        case 'post_content':
                            $itemProps['post_content'] = $post->post_content;
                            break;
                        case 'post_feature_image':
                            $itemProps['post_feature_image'] = get_the_post_thumbnail($post->ID, 'large');
                            break;
                        case 'entase_title':
                            $itemProps['entase_title'] = get_post_meta($production->ID, 'entase_title', true);
                            break;
                        case 'entase_story':
                            $itemProps['entase_story'] = get_post_meta($production->ID, 'entase_story', true);
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
                            $itemProps['entase_book'] = '<a href="javascript:void(0);" class="entase_book" data-event="'.$eventID.'">'.$atts['booklabel'].'</a>';
                            break;
                        case 'entase_photo_poster':
                            if ($photo == null)
                            {
                                $meta =  get_post_meta($production->ID, 'entase_photo', true);
                                $photo = @json_decode($meta) ?? null;
                            }
                            $itemProps['entase_photo_poster'] = $photo != null ? '<img src="'.$photo->poster->medium.'" />' : '';
                            break;
                        case 'entase_photo_og':
                            if ($photo == null)
                            {
                                $meta =  get_post_meta($production->ID, 'entase_photo', true);
                                $photo = @json_decode($meta) ?? null;
                            }
                            $itemProps['entase_photo_og'] = $photo != null ? '<img src="'.$photo->og->large.'" />' : '';
                            break;
                        default:
                            $row[] = ['key' => $field, 'val' => get_post_meta($post->ID, $field, true)];
                            break;
                    }
                }

                // Additional params
                $item = array_merge([
                    'entase_id' => get_post_meta($post->ID, 'entase_id', true),
                    'fields' => $row
                ], $itemProps);
                
                if ($atts['targeturl'] == 'book')
                {
                    $item['url'] = 'javascript:void(0);';
                    $item['allowbook'] = true;
                }
                elseif ($atts['targeturl'] == 'production')
                {                    
                    $item['url'] = $production != null ? esc_url(get_permalink($production)) : '#';
                }

                // Add item to collection
                $items[] = $item;
            }
        }

        /* ************* */
        /* REND THE GRID */
        /* ************* */
        $atmf = \ATMF\Setup::GetEngine();
        $atmf->vars['items'] = $items;

        return $atmf->RendTemplate('Widgets/Events_Classic', true);
    }
}