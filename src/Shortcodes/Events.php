<?php

namespace Entase\Plugins\WP\Shortcodes;

use \Entase\Plugins\WP\Conf;

use \Entase\Plugins\WP\Core\GeneralSettings;
use \Entase\Plugins\WP\Core\SkinSettings;

use \Entase\Plugins\WP\Utilities\Shortcodes;
use \Entase\Plugins\WP\Utilities\Timezone;
use \Entase\Plugins\WP\Utilities\Skins;


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
            'metafields' => [],
            'skin' => 'classic',
            'cssnames' => [],
            'taxonomies' => [],

            // Query
            'filter_status' => [1],
            'filter_productions' => [],
            'filter_categories' => [],
            'filter_cohosting' => '',
            'filter_sameowner' => '',
            'filter_current_production' => 'no',
            'allow_qs_production' => 'no',
            'allow_qs_date' => 'no',

            // Customize
            'targeturl' => 'book',
            'booklabel' => 'Book',
            'dateformat' => 'd/m',
            'timeformat' => 'H:i',
            'contentchars' => 200
            
        ], is_array($atts) ? $atts : []);

        
        /* ********************* */
        /* Elementor load styles */
        /* in different manner   */
        /* ********************* */
        if (!$atts['nostyles'])
            wp_enqueue_style('entase-widget-events', Conf::CSSUrl.'/front/widgets/events-classic.css');

        /* *************** */
        /* SANITIZE FIELDS */
        /* *************** */
        $atts['fields'] = is_string($atts['fields']) ? explode(',', $atts['fields']) : $atts['fields'];
        $atts['cssnames'] = is_string($atts['cssnames']) ? explode(',', $atts['cssnames']) : $atts['cssnames'];
        if (is_string($atts['metafields']))
        {
            $arr = [];
            $metafields = explode(',', $atts['metafields']);
            foreach ($metafields as $metafield) {
                if ($metafield == '') continue;
                list($field, $context) = explode(':', $metafield);
                $arr[] = ['field' => $field, 'context' => $context, 'hide_if_empty' => 'yes'];
            }
            $atts['metafields'] = $arr;
        }        
        
        $hasProductionContext = false;
        foreach ($atts['metafields'] as $arr) 
        {
            if ($arr['context'] == 'production')
            {
                $hasProductionContext = true;
                break;
            }
        }

        if (is_string($atts['taxonomies']))
        {
            $arr = [];
            $taxonomies = explode(',', $atts['taxonomies']);
            foreach ($taxonomies as $taxonomy) {
                if ($taxonomy == '') continue;
                list($type, $context, $attsStr) = explode(':', $taxonomy);
                $taxAtts = explode('|', $attsStr);
                $arr[] = ['type' => $type, 'context' => $context, 'atts' => $taxAtts, 'hide_if_empty' => 'yes'];
            }
            $atts['taxonomies'] = $arr;
        }

        foreach ($atts['taxonomies'] as $key => $taxonomy) 
        {
            if (!isset($taxonomy['context']))
                $taxonomy['context'] = ['both'];

            if (!isset($taxonomy['atts']))
                $taxonomy['atts'] = [];

            if ($taxonomy['show_links'] == 'yes')
                unset($taxonomy[array_search('nolink', $taxonomy)]);
            else $taxonomy['atts'][] = 'nolink';

            $atts['taxonomies'][$key] = $taxonomy;

            if (in_array($taxonomy['context'], ['production', 'both']))
                $hasProductionContext = true;
            
        }

        //$atts['metafields'] = is_string($atts['metafields']) ? explode(',', $atts['fields']) : $atts['metafields'];

        /* ******************** */
        /* SANITIZE QUERY ARGS */
        /* ******************** */
        $limit = (int)$atts['limit'];
        $statuses = is_string($atts['filter_status']) ? explode(',', $atts['filter_status']) : $atts['filter_status'];
        $productions = is_string($atts['filter_productions']) ? explode(',', $atts['filter_productions']) : $atts['filter_productions'];
        $categories = is_string($atts['filter_categories']) ? explode(',', $atts['filter_categories']) : $atts['filter_categories'];
        $atts['contentchars'] = (int)$atts['contentchars'];
        
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

        if (in_array('_current', $categories)) 
        {
            $categories = array_diff($categories, ['_current']);
            if (is_category()) {
                $category = get_queried_object();
                if ($category && isset($category->term_id)) {
                    $categories[] = $category->term_id;
                }
            }
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

        // Add productions filter
        if (count($categories) > 0)
        {
            $query['tax_query'][] = [
                'taxonomy' => 'category',
                'field' => 'term_id',
                'terms' => $categories,
                'operator' => 'IN'
            ];
        }

        // Filter Cohosting
        if (in_array($atts['filter_cohosting'], ['yes', 'no']))
        {
            $query['meta_query'][] = [
                'key' => 'entase_cohosting',
                'value' => ($atts['filter_cohosting'] == 'yes'),
                'compare' => '='
            ];
        }

        // Filter same owner
        if (in_array($atts['filter_sameowner'], ['yes', 'no']))
        {
            
            $query['meta_query'][] = [
                'key' => 'entase_ownerRef',
                'value' => 'Partner:'.GeneralSettings::Get('partnerID'),
                'compare' => ($atts['filter_sameowner'] == 'yes') ? '=' : '!='
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
        /* CUSTOM QUERY FILTER */
        /* ******************* */
        if(has_filter('entase_events_query')) {
            $query = apply_filters('entase_events_query', $query);
        }

        /* ******************* */
        /* BUILD TEMPLATE DATA */
        /* ******************* */
        $items = [];
        $posts = $query != null ? get_posts($query) : null;
        if ($posts && count($posts) > 0)
        {
            foreach($posts as $post)
            {
                $photo = null;
                $row = [];
                $itemProps = [];

                // Extract related production if needed
                $production = null;
                if($hasProductionContext || $atts['targeturl'] == 'production' ||
                    count(array_intersect([
                        'production_post_title', 
                        'production_post_content', 
                        'production_post_feature_image', 
                        'entase_title', 
                        'entase_story', 
                        'entase_photo_poster', 
                        'entase_photo_og',
                        'entase_ownerName'
                    ], $atts['fields'] )) > 0)
                    $production = self::GetRelatedProduction($post);


                $entaseID = get_post_meta($post->ID, 'entase_id', true);
                $entaseStatus = get_post_meta($post->ID, 'entase_status', true);
                $entaseCohosting = get_post_meta($post->ID, 'entase_cohosting', true);
                $entaseOwnerRef = get_post_meta($post->ID, 'entase_ownerRef', true);

                // Add fields
                foreach ($atts['fields'] as $field)
                {
                    switch($field)
                    {
                        case 'production_post_title':
                            $itemProps['production_post_title'] = $production->post_title;
                            break;
                        case 'production_post_content':
                            $content = $production->post_content;
                            $itemProps['production_post_content'] = mb_strlen($content) > $atts['contentchars'] ? mb_substr($content, 0, $atts['contentchars']).'...' : $content;
                            break;
                        case 'production_post_feature_image':
                            $itemProps['production_post_feature_image'] = get_the_post_thumbnail($production->ID, 'large');
                            break;
                        case 'post_title':
                            $itemProps['post_title'] = $post->post_title;
                            break;
                        case 'post_content':
                            $content = $post->post_content;
                            $itemProps['post_content'] = mb_strlen($content) > $atts['contentchars'] ? mb_substr($content, 0, $atts['contentchars']).'...' : $content;
                            break;
                        case 'post_feature_image':
                            $itemProps['post_feature_image'] = get_the_post_thumbnail($post->ID, 'large');
                            break;
                        case 'entase_title':
                            $itemProps['entase_title'] = apply_filters('entase_title', get_post_meta($production->ID, 'entase_title', true), $post);
                            break;
                        case 'entase_story':
                            $story = apply_filters('entase_story', Shortcodes::MarkupToHTML(get_post_meta($production->ID, 'entase_story', true), ['searchurl' => '']), $post);
                            $itemProps['entase_story'] = mb_strlen($story) > $atts['contentchars'] ? mb_substr($story, 0, $atts['contentchars']).'...' : $story;
                            break;
                        case 'entase_datestart':
                            $time = (int)get_post_meta($post->ID, 'entase_dateStart', true);
                            $tz = get_post_meta($post->ID, 'entase_location_timezone', true);
                            if ($tz == '') $tz = 'Europe/Sofia';
                            
                            // Handling WP time zones
                            $datestr = get_date_from_gmt(date('Y-m-d H:i', $time), $atts['dateformat'].' - '.$atts['timeformat']);
                            //$datestr = Timezone::ConvertToTimezone(date('Y-m-d H:i', $time), $tz, null, $atts['dateformat'].' - '.$atts['timeformat']);
                            $datestr = apply_filters('entase_datestart', $datestr, $post);

                            $row[] = ['key' => 'entase_datestart', 'val' => $datestr];
                            $itemProps['entase_datestart'] = $datestr;
                            break;
                        case 'entase_dateonly':
                            $time = (int)get_post_meta($post->ID, 'entase_dateStart', true);
                            $tz = get_post_meta($post->ID, 'entase_location_timezone', true);
                            if ($tz == '') $tz = 'Europe/Sofia';
                            
                            // Handling WP time zones
                            $datestr = get_date_from_gmt(date('Y-m-d H:i', $time), $atts['dateformat']);
                            //$datestr = Timezone::ConvertToTimezone(date('Y-m-d H:i', $time), $tz, null, $atts['dateformat']);
                            $datestr = apply_filters('entase_dateonly', $datestr, $post);

                            $row[] = ['key' => 'entase_dateonly', 'val' => $datestr];
                            $itemProps['entase_dateonly'] = $datestr;
                            break;
                        case 'entase_timeonly':
                            $time = (int)get_post_meta($post->ID, 'entase_dateStart', true);
                            $tz = get_post_meta($post->ID, 'entase_location_timezone', true);
                            if ($tz == '') $tz = 'Europe/Sofia';

                            // Handling WP time zones
                            $datestr = get_date_from_gmt(date('Y-m-d H:i', $time), $atts['timeformat']);
                            //$datestr = Timezone::ConvertToTimezone(date('Y-m-d H:i', $time), $tz, null, $atts['timeformat']);
                            $datestr = apply_filters('entase_timeonly', $datestr, $post);

                            $row[] = ['key' => 'entase_timeonly', 'val' => $datestr];
                            $itemProps['entase_timeonly'] = $datestr;
                            break;
                        case 'entase_book':
                            $itemProps['entase_book'] = apply_filters('entase_book', '<a href="javascript:void(0);" class="entase_book" data-event="'.$entaseID.'" data-status="'.$entaseStatus.'">'.$atts['booklabel'].'</a>', $post);
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
                            $val = get_post_meta($post->ID, $field, true);
                            $val = apply_filters($field, $val, $post);
                            $row[] = ['key' => $field, 'val' => $val];
                            $itemProps[$field] = $val;
                            break;
                    }
                }

                // Additional custom meta fields
                if (isset($atts['metafields']) && is_array($atts['metafields']))
                {
                    $blockProductionQuery = false;
                    foreach($atts['metafields'] as $field)
                    {
                        $fieldName = trim($field['field']);
                        $context = strtolower(trim($field['context']));
                        $hideIfEmpty = strtolower(trim($field['hide_if_empty'])) == 'yes';

                        if ($fieldName == '') continue;
                        elseif (!$blockProductionQuery && $context == 'production' && $production == null)
                        {
                            $production = self::GetRelatedProduction($post);
                            $blockProductionQuery = true;
                        }

                        $contextID = $context == 'production' ? $production->ID : $post->ID;
                        $meta_value = get_post_meta($contextID, $fieldName, true);
                        if ($hideIfEmpty && trim($meta_value) == '') continue;

                        $val = $field['prefix'].$meta_value.$field['suffix'];
                        $val = apply_filters('entase_meta_'.$fieldName, $val, $post);

                        $row[] = ['key' => 'meta_'.$fieldName, 'val' => $val];
                        $itemProps['meta_'.$fieldName] = $val;
                    }
                }


                // Query taxanomies
                $queryCategories = false;
                $cssCategories = false;
                $showCategories = false;
                $showCategoriesOpts = [];

                $queryTags = false;
                $cssTags = false;
                $showTags = false;
                $showTagsOpts = [];
                
                if (isset($atts['cssnames']) && in_array('category', $atts['cssnames']))
                {
                    $queryCategories = true;
                    $cssCategories = true;
                }

                if (isset($atts['cssnames']) && in_array('tag', $atts['cssnames']))
                {
                    $queryTags = true;
                    $cssTags = true;
                }
                
                if (isset($atts['taxonomies']) && is_array($atts['taxonomies']))
                {
                    foreach ($atts['taxonomies'] as $taxonomy) 
                    {
                        if ($taxonomy['type'] == 'category')
                        {
                            $queryCategories = true;
                            $showCategories = true;
                            $showCategoriesOpts = $taxonomy;
                        }
                        elseif ($taxonomy['type'] == 'tag')
                        {
                            $queryTags = true;
                            $showTags = true;
                            $showTagsOpts = $taxonomy;
                        }
                    }
                }
                

                // Do query taxonomies
                $cssnames = '';
                
                if ($queryCategories)
                {
                    $eventCategories = wp_get_post_categories($post->ID, ['fields' => 'all']);
                    $productionCategories = [];
                    if ($production != null)
                        $productionCategories = wp_get_post_categories($production->ID, ['fields' => 'all']);

                        
                    $taxonomyVals = [];
                    $categories = array_merge($eventCategories, $productionCategories);
                    foreach ($categories as $category) 
                    {
                        if ($cssCategories)
                            $cssnames .= ' category-'.$category->slug;
                    }

                    if ($showCategories)
                    {
                        if (in_array($showCategoriesOpts['context'], ['event', 'both']))
                        {
                            foreach ($eventCategories as $category) 
                            {
                                $val = in_array('nolink', $showCategoriesOpts['atts']) ? $category->name : '<a href="'.get_category_link($category->term_id).'">'.$category->name.'</a>';
                                $taxonomyVals[] = '<span class="category-label-'.$category->slug.'">'.$val.'</span>';
                            }
                        }

                        if (in_array($showCategoriesOpts['context'], ['production', 'both']))
                        {
                            foreach ($productionCategories as $category) 
                            {
                                $val = in_array('nolink', $showCategoriesOpts['atts']) ? $category->name : '<a href="'.get_category_link($category->term_id).'">'.$category->name.'</a>';
                                $taxonomyVals[] = '<span class="category-label-'.$category->slug.'">'.$val.'</span>';
                            }
                        }
                    }
                    
                    if (!isset($itemProps['taxonomy_category']))
                        $itemProps['taxonomy_category'] = '';
                    
                    $itemProps['taxonomy_category'] .= implode(' ', $taxonomyVals);
                }


                if ($queryTags)
                {
                    $eventTags = wp_get_post_tags($post->ID);
                    $productionTags = [];
                    if ($production != null)
                        $productionTags = wp_get_post_tags($production->ID);

                    $taxonomyVals = [];
                    $tags = array_merge($eventTags, $productionTags);
                    $tagIDs = [];
                    foreach ($tags as $tag) 
                    {
                        if (!in_array($tagID, $tagIDs)) $tagIDs[] = $tag->term_id;
                        else continue;

                        if ($cssTags)
                            $cssnames .= ' tag-'.$tag->term_id;
                    }

                    if ($showTags)
                    {
                        $tagIDs = [];

                        if (in_array($showTagsOpts['context'], ['event', 'both']))
                        {
                            foreach ($eventTags as $tag) 
                            {
                                if (!in_array($tagID, $tagIDs)) $tagIDs[] = $tag->term_id;
                                else continue;

                                $val = in_array('nolink', $showTagsOpts['atts']) ? $tag->name : '<a href="'.get_tag_link($tag->term_id).'">'.$tag->name.'</a>';
                                $taxonomyVals[] = '<span class="tag-label-'.$tag->slug.'">'.$val.'</span>';
                            }
                        }

                        if (in_array($showTagsOpts['context'], ['production', 'both']))
                        {
                            foreach ($productionTags as $tag) 
                            {
                                if (!in_array($tagID, $tagIDs)) $tagIDs[] = $tag->term_id;
                                else continue;
                                
                                $val = in_array('nolink', $showTagsOpts['atts']) ? $tag->name : '<a href="'.get_tag_link($tag->term_id).'">'.$tag->name.'</a>';
                                $taxonomyVals[] = '<span class="tag-label-'.$tag->slug.'">'.$val.'</span>';
                            }
                        }
                    }

                    if (!isset($itemProps['taxonomy_tag']))
                        $itemProps['taxonomy_tag'] = '';

                    $itemProps['taxonomy_tag'] = implode(' ', $taxonomyVals);
                    
                }

                
                $itemProps['cssnames'] = $cssnames;


                // Additional params
                $item = array_merge([
                    'entase_id' => $entaseID,
                    'entase_status' => $entaseStatus,
                    'entase_cohosting' => $entaseCohosting ? 'true' : 'false',
                    'entase_sameowner' => $entaseOwnerRef == 'Partner:'.GeneralSettings::Get('partnerID') ? 'true' : 'false',
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
                
                
                /*if (isset($atts['cssnames']) || 
                    in_array('categories', $atts['fields']) ||
                    in_array('tags', $atts['fields']))
                {
                    $cssnames = '';
                    $cssCategories = in_array('category', $atts['cssnames']);
                    $cssTags = in_array('tag', $atts['cssnames']);

                    if ($cssCategories)
                    {
                        $eventCategories = wp_get_post_categories($post->ID, ['fields' => 'slugs']);
                        if ($production != null)
                            $productionCategories = wp_get_post_categories($production->ID, ['fields' => 'slugs']);

                        $categories = array_unique(array_merge($eventCategories, $productionCategories));
                        foreach ($categories as $slug) $cssnames .= ' category-'.$slug;
                    }

                    if ($cssTags)
                    {
                        $eventTags = wp_get_post_tags($post->ID, ['fields' => 'ids']);
                        if ($production != null)
                            $productionTags = wp_get_post_tags($production->ID, ['fields' => 'ids']);

                        $tags = array_unique(array_merge($eventTags, $productionTags));
                        foreach ($tags as $slug) $cssnames .= ' tag-'.$slug;
                    }

                    $item['cssnames'] = $cssnames;
                }*/

                // Add item to collection
                $items[] = $item;
            }
        }

        /* ************* */
        /* REND THE GRID */
        /* ************* */
        $atmf = \ATMF\Setup::GetEngine();
        $atmf->vars['items'] = $items;

        $templatePreview = $atmf->__('$_template_preview');
        if ($templatePreview != '')
        {
            $atmf->SetTemplate('_Widget_Custom', $templatePreview);
            //echo $templatePreview;exit;
            return $atmf->RendTemplate('_Widget_Custom', true);
        }
        else 
        {
            if ($atts['skin'] != 'classic')
            {
                $skins = SkinSettings::Get('skins');
		        foreach ($skins as $skin) 
                {
                    if ($skin['id'] == $atts['skin'])
                    {
                        $atmf->__('$_widget_src', Skins::BuildTemplateSource($skin['template']));
                        $template = $atmf->RendTemplate('Widgets/Events_Custom', true);

                        $atmf->SetTemplate('_Widget_Custom', $template);
                        return $atmf->RendTemplate('_Widget_Custom', true);
                    }
                }
            }

            return $atmf->RendTemplate('Widgets/Events_Classic', true);
        }
    }
}