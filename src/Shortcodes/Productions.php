<?php

namespace Entase\Plugins\WP\Shortcodes;

use Entase\Plugins\WP\Conf;

class Productions extends BaseShortcode
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
            'fields' => ['entase_photo_poster', 'post_title', 'post_tags'],
            'contentchars' => 200,
            'cssnames' => []

        ], is_array($atts) ? $atts : []);



        /* ********************* */
        /* Elementor load styles */
        /* in different manner   */
        /* ********************* */
        if (!$atts['nostyles'])
            wp_enqueue_style('entase-widget-productions', Conf::CSSUrl.'/front/widgets/productions-classic.css');


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
                list($field, $context) = explode(':', $metafield);
                $arr[] = ['field' => $field, 'context' => $context, 'hide_if_empty' => 'yes'];
            }
            $atts['metafields'] = $arr;
        }
        $atts['metafields'] = is_string($atts['metafields']) ? explode(',', $atts['fields']) : $atts['metafields'];

        /* ******************** */
        /* SANITIZE QUERY ARGS */
        /* ******************** */
        $limit = $atts['limit'] ?? 0;
        $categories = $atts['filter_categories'] ?? [];
        $tags = $atts['filter_tags'] ?? [];
        $fields = $atts['fields'] ?? [];
        $multisourceImage = $atts['multisource_image'] ?? [];
        $contentChars = (int)$atts['contentchars'];        

        if (is_string($categories)) $categories = explode(',', $categories);
        if (is_string($tags)) $tags = explode(',', $tags);
        if (is_string($fields)) $fields = explode(',', $fields);
        if (is_string($multisourceImage)) $multisourceImage = explode(',', $multisourceImage);
        

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


        /* *********** */
        /* BUILD QUERY */
        /* *********** */
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


        /* ******************* */
        /* BUILD TEMPLATE DATA */
        /* ******************* */
        $items = [];
        $productions = get_posts($query);
        if ($productions && count($productions) > 0)
        {
            foreach($productions as $production)
            {
                $photo = null;
                $row = [];
                $itemProps = [];

                foreach ($fields as $field)
                {
                    switch($field)
                    {
                        case 'post_title':
                            $row[] = ['key' => 'post_title', 'val' => $production->post_title];
                            $itemProps['post_title'] = $production->post_title;
                            break;
                        case 'post_content':
                            $content = $production->post_content;
                            $contentExport = mb_strlen($content) > $contentChars ? mb_substr($content, 0, $contentChars).'...' : $content;
                            $row[] = ['key' => 'post_content', 'val' => $contentExport];
                            $itemProps['post_content'] = $contentExport;
                            break;
                        case 'post_feature_image':
                            $img = get_the_post_thumbnail($production->ID, 'large');
                            $row[] = ['key' => 'post_feature_image', 'val' => $img];
                            $itemProps['post_feature_image'] = $img;
                            break;
                        case 'post_tags':
                            $tags = get_tags(['object_ids' => $production->ID, 'orderby' => 'count', 'order' => 'DESC']);
                            $tagsArr = [];
                            foreach($tags as $key => $tag) $tagsArr[] = $tag->name;
                            if (count($tagsArr) > 3) array_splice($tagsArr, 3);

                            $row[] = ['key' => 'post_tags', 'val' => implode(', ', $tagsArr)];
                            $itemProps['post_tags'] = implode(', ', $tagsArr);
                            break;
                        case 'entase_title':
                            $title = get_post_meta($production->ID, 'entase_title', true);
                            $title = apply_filters('entase_title', $title);

                            $row[] = ['key' => 'entase_title', 'val' => $title];
                            $itemProps['entase_title'] = $title;
                            break;
                        case 'entase_story':
                            $content = get_post_meta($production->ID, 'entase_story', true);
                            $contentExport = mb_strlen($content) > $contentChars ? mb_substr($content, 0, $contentChars).'...' : $content;
                            $contentExport = apply_filters('entase_story', $contentExport);

                            $row[] = ['key' => 'entase_story', 'val' => $contentExport];
                            $itemProps['entase_story'] = $contentExport;
                            break;
                        case 'entase_photo_poster':
                            if ($photo == null)
                            {
                                $meta =  get_post_meta($production->ID, 'entase_photo', true);
                                $photo = @json_decode($meta) ?? null;
                            }
                            $img = $photo != null ? '<img src="'.$photo->poster->medium.'" />' : '';
                            $img = apply_filters('entase_photo_poster', $img);

                            $row[] = ['key' => 'entase_photo_poster', 'val' => $img];
                            $itemProps['entase_photo_poster'] = $img;
                            break;
                        case 'entase_photo_og':
                            if ($photo == null)
                            {
                                $meta =  get_post_meta($production->ID, 'entase_photo', true);
                                $photo = @json_decode($meta) ?? null;
                            }
                            
                            $img = $photo != null ? '<img src="'.$photo->og->large.'" />' : '';
                            $img = apply_filters('entase_photo_og', $img);
                            
                            $row[] = ['key' => 'entase_photo_og', 'val' => $img];
                            $itemProps['entase_photo_poster'] = $img;
                            break;
                        case 'multisource_image':
                            if (is_array($multisourceImage))
                            {
                                $source = '';
                                foreach($multisourceImage as $image)
                                {
                                    if ($source != '') break;
                                    
                                    $imageSource = is_array($image) ? $image['source'] : $image;
                                    if ($imageSource == 'post_feature_image')
                                    {
                                        $source = get_the_post_thumbnail($production->ID, 'large');
                                    }
                                    elseif ($imageSource == 'entase_photo_poster')
                                    {
                                        if ($photo == null)
                                        {
                                            $meta =  get_post_meta($production->ID, 'entase_photo', true);
                                            $photo = @json_decode($meta) ?? null;
                                        }
                                        $source = $photo != null ? $photo->poster->medium : '';
                                    }
                                    elseif ($imageSource == 'entase_photo_og')
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
                                    $img = apply_filters('entase_multisource_image', $img);

                                    $row[] = ['key' => 'multisource_image', 'val' => $img];
                                    $itemProps['multisource_image'] = $img;
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
                        
                        $fieldName = $field['field'];
                        $fieldValue = get_post_meta($production->ID, $fieldName, true);
                        $fieldValue = apply_filters('entase_meta_'.$fieldName, $fieldValue);

                        $row[] = ['key' => 'meta_'.$field['field'], 'val' => $fieldValue];
                        $itemProps['meta_'.$fieldName] = $fieldValue;
                    }
                }

                if (isset($atts['cssnames']))
                {
                    $cssnames = '';
                    $cssCategories = in_array('category', $atts['cssnames']);
                    $cssTags = in_array('tag', $atts['cssnames']);

                    if ($cssCategories)
                    {
                        $categories = wp_get_post_categories($production->ID, ['fields' => 'slugs']);
                        foreach ($categories as $slug) $cssnames .= ' category-'.$slug;
                    }

                    if ($cssTags)
                    {
                        $tags = wp_get_post_tags($production->ID, ['fields' => 'ids']);
                        foreach ($tags as $slug) $cssnames .= ' tag-'.$slug;
                    }

                    $itemProps['cssnames'] = $cssnames;
                }
                
                $items[] = array_merge(['url' => esc_url(get_permalink($production)), 'fields' => $row], $itemProps);
            }
        }

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
                        $template = $atmf->RendTemplate('Widgets/Productions_Custom', true);

                        $atmf->SetTemplate('_Widget_Custom', $template);
                        return $atmf->RendTemplate('_Widget_Custom', true);
                    }
                }
            }

            return $atmf->RendTemplate('Widgets/Productions_Classic', true);
        }
    }
}