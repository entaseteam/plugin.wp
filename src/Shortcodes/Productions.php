<?php

namespace Entase\Plugins\WP\Shortcodes;

use Entase\Plugins\WP\Conf;

class Productions extends BaseShortcode
{
    public static function Do($atts, $content, $tag)
    {
        $atts = array_merge([
            'nostyles' => false,
            'limit' => 0,
            'fields' => ['entase_photo_poster', 'post_title', 'post_tags']
        ], is_array($atts) ? $atts : []);

        if (!$atts['nostyles'])
            wp_enqueue_style('entase-widget-productions', Conf::CSSUrl.'/front/widgets/productions-classic.css');

        $limit = $atts['limit'] ?? 0;
        $categories = $atts['filter_categories'] ?? [];
        $tags = $atts['filter_tags'] ?? [];
        $fields = $atts['fields'] ?? [];

        if (is_string($categories)) $categories = explode(',', $categories);
        if (is_string($tags)) $tags = explode(',', $tags);
        if (is_string($fields)) $fields = explode(',', $fields);

        foreach ($categories as $key => $value) 
            if ($value == '') unset($categories[$key]);

        foreach ($tags as $key => $value) 
            if ($value == '') unset($tags[$key]);

        if ($atts['filter_current_categories'] == 'yes')
        {
            $obj = get_queried_object();
            if ($obj)
            {
				if (is_category())
				{
					$categories[] = $obj->term_id;
				}
				else 
				{
					$terms = get_the_terms($obj->ID, 'category');
                	foreach($terms as $term) $categories[] = $term->term_id;
				}
            }
        }

        if ($atts['filter_current_tags'] == 'yes')
        {
            $post = get_queried_object();
            if ($post)
            {
                $terms = get_the_terms($post->ID, 'post_tag');
                foreach($terms as $term) $tags[] = $term->term_id;
            }
        }

        $query = [
            'post_type' => 'production',
            'posts_per_page' => $limit > 0 ? $limit : -1,
            'tax_query' => []
        ];

        if (count($categories) > 0)
        {            
            $query['tax_query'][] = [
                  'taxonomy' => 'category',
                  'field' => 'term_id', 
                  'terms' => $categories,
                  'include_children' => true
            ];
        }

        if (count($tags) > 0)
        {
            $query['tax_query'][] = [
                  'taxonomy' => 'post_tag',
                  'field' => 'term_id', 
                  'terms' => $tags,
                  'include_children' => true
            ];
        }

        $items = [];
        $productions = get_posts($query);
        if ($productions && count($productions) > 0)
        {
            foreach($productions as $production)
            {
                $photo = null;
                $row = [];
                foreach ($fields as $field)
                {
                    switch($field)
                    {
                        case 'post_title':
                            $row[] = ['key' => 'post_title', 'val' => $production->post_title];
                            break;
                        case 'post_content':
                            $row[] = ['key' => 'post_content', 'val' => $production->post_content];
                            break;
                        case 'post_feature_image':
                            $row[] = ['key' => 'post_feature_image', 'val' => get_the_post_thumbnail($production->ID, 'large')];
                            break;
                        case 'post_tags':
                            //$tags = get_the_tags($production->ID);
                            $tags = get_tags(['object_ids' => $production->ID, 'orderby' => 'count', 'order' => 'DESC']);
                            $tagsArr = [];
                            foreach($tags as $key => $tag) $tagsArr[] = $tag->name;
                            if (count($tagsArr) > 3) array_splice($tagsArr, 3);
                            $row[] = ['key' => 'post_tags', 'val' => implode(', ', $tagsArr)];
                            break;
                        case 'entase_title':
                            $row[] = ['key' => 'entase_title', 'val' => get_post_meta($production->ID, 'entase_title', true)];
                            break;
                        case 'entase_story':
                            $row[] = ['key' => 'entase_story', 'val' =>  get_post_meta($production->ID, 'entase_story', true)];
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
                        case 'multisource_image':
                            if (isset($atts['multisource_image']) && is_array($atts['multisource_image']))
                            {
                                $source = '';
                                foreach($atts['multisource_image'] as $image)
                                {
                                    if ($source != '') break;
                                    elseif ($image['source'] == 'post_feature_image')
                                    {
                                        $source = get_the_post_thumbnail($production->ID, 'large');
                                    }
                                    elseif ($image['source'] == 'entase_photo_poster')
                                    {
                                        if ($photo == null)
                                        {
                                            $meta =  get_post_meta($production->ID, 'entase_photo', true);
                                            $photo = @json_decode($meta) ?? null;
                                        }
                                        $source = $photo != null ? $photo->poster->medium : '';
                                    }
                                    elseif ($image['source'] == 'entase_photo_og')
                                    {
                                        if ($photo == null)
                                        {
                                            $meta =  get_post_meta($production->ID, 'entase_photo', true);
                                            $photo = @json_decode($meta) ?? null;
                                        }
                                        $source = $photo != null ? $photo->og->large : '';
                                    }

                                }

                                if ($source != '')
                                {
                                    $img = stripos($source, '<img') !== false ? $source : '<img src="'.$source.'" />';
                                    $row[] = ['key' => 'multisource_image', 'val' => $img];
                                }
                            }
                            break;
                    }
                }

                // Additional custom meta fields
                if (isset($atts['metafields']) && is_array($atts['metafields']))
                {                    
                    foreach($atts['metafields'] as $field)
                    {
                        if (trim($field['field']) == '') continue;
                        $row[] = ['key' => 'entase_'.$field['field'], 'val' => get_post_meta($production->ID, $field['field'], true)];
                    }
                }

                $items[] = ['url' => esc_url(get_permalink($production)), 'fields' => $row];
            }
        }

        $atmf = \ATMF\Setup::GetEngine();
        $atmf->vars['items'] = $items;

        return $atmf->RendTemplate('Widgets/Productions_Classic', true);
    }
}