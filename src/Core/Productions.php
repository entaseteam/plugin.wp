<?php

namespace Entase\Plugins\WP\Core;

use \Entase\Plugins\WP\Conf;
use \Entase\Plugins\WP\Utilities\Helper;
use \Entase\Plugins\WP\Utilities\Shortcodes;
use \Entase\Plugins\WP\Utilities\Ajax;
use stdClass;

class Productions
{
    public static $photo = null;

    public static function PostsMenu()
    {
        global $current_screen;
        if ($current_screen->post_type != 'production') {
            return;
        }

        Helper::OutputImportButton('productions');

        add_filter('manage_production_posts_columns' , [__CLASS__, 'TableHeader']);
        add_action('manage_production_posts_custom_column' , ['\Entase\Plugins\WP\Utilities\Helper', 'CustomTableColumns']);

        wp_enqueue_style('post-tables', Conf::CSSUrl.'/admin/post-tables.css');
    }

    public static function TableHeader($columns)
    {
        return array_merge(['entase_photo_poster' => '&nbsp;'], $columns);
    }

    public static function MetaBoxes()
    {

        wp_enqueue_script('entase-meta', Conf::JSUrl.'/admin/meta.js', ['jquery'], false, true);
        wp_enqueue_style('entase-meta', Conf::CSSUrl.'/admin/meta-boxes.css');

        $shortcodeBtn = '<a href="javascript:void(0);" class="_btnEntase_CopyValue" data-value="$1" data-type="shortcode">Shortcode</a>';
        $metaboxes = [
            [
                'name' => 'entase_story',
                'shortcode' => 'entase_story',
                'title' => 'Story - Entase',
                'context' => 'advanced'                
            ],
            [
                'name' => 'entase_photo_poster',
                'shortcode' => 'entase_photo_poster',
                'title' => 'Poster - Entase',
                'context' => 'side',
            ],
            [
                'name' => 'entase_photo_og',
                'shortcode' => 'entase_photo_og',
                'title' => 'OG - Entase',
                'context' => 'side',
            ]
        ];

        foreach ($metaboxes as $box)
        {
            add_meta_box($box['name'], $box['title'].' '.str_replace('$1', $box['shortcode'], $shortcodeBtn), [__CLASS__, 'DisplayMetaBox'], 'production', $box['context'], 'default', $box);            
        }
    }

    public static function DisplayMetaBox($post, $args=[])
    {
        $box = $args['args'];
        switch($box['name'])
        {
            case 'entase_story':
                $story = get_post_meta($post->ID, 'entase_story', true);
                echo Shortcodes::MarkupToHTML(Helper::EscapeDocument($story));
                break;
            case 'entase_photo_poster':
                self::ExtractMetaPhoto($post);
                $poster = self::$photo->poster ?? null;
                if ($poster != null) echo '<img src="'.$poster->small.'" class="entase_meta_poster" />';
                else echo '<div style="text-align:center">(not set)</div>';
                break;
            case 'entase_photo_og':
                self::ExtractMetaPhoto($post);
                $og = self::$photo->og ?? null;

                if ($og != null) echo '<img src="'.$og->large.'" class="entase_meta_og" />';
                else echo '<div style="text-align:center">(not set)</div>';
                break;
        }
    }


    public static function ExtractMetaPhoto($post)
    {
        if (self::$photo == null)
        {
            $meta = get_post_meta($post->ID, 'entase_photo', true);
            self::$photo = $meta != '' ? @json_decode($meta) : null;
        }
    }

    public static function ExtractProductionTags($production)
    {
        $tags = [];
        if ($production != null)
        {
            usort($production->tags, function($a, $b) {
                if ($a->type == $b->type) return 0;
                else return ($a->type < $b->type) ? -1 : 1;
            });
            
            foreach ($production->tags as $tag) {
                $tagName = $tag->name;
                $tagName = str_replace('\\', ' ', $tagName);
                $tagName = str_replace('  ', ' ', $tagName);
                $tagName = str_replace('  ', ' ', $tagName);
                $tags[] = $tagName;
            }
        }

        return $tags;
    }

    public static function Sync($capture=false, $fromID=null)
    {
        $entase = \Entase\Plugins\WP\Core\EntaseSDK::PrepareClient();
        
        $productions = null;
        try {
            $filter = ['extend' => 'ownerName', 'sort' => ['id' => 'asc']];
            if ($fromID != null) $filter['after'] = $fromID;

            $productions = $entase->productions->GetAll($filter); 
        }
        catch (\Entase\SDK\Exceptions\Base $ex) {}

        if ($productions != null)
        {
            $count = 0;
            $lastID = '';
            foreach($productions as $production)
            {
                $lastID = $production->id;
                $query = [
                    'post_type' => 'production',
                    'meta_key' => 'entase_id',
                    'meta_value' => $production->id,
                    'posts_per_page' => 1
                ];
                $posts = get_posts($query);
                if (count($posts) > 0) continue;

                $tags = self::ExtractProductionTags($production);
                $meta = self::PrepareMetaFromAPI($production);
                wp_insert_post([
                    'post_title' => $production->title, 
                    'post_type' => 'production', 
                    'post_content' => '',
                    'post_status' =>  'publish',
                    'meta_input' => $meta,
                    'tags_input' => $tags
                ]);

                $count++;
            }
            
            $response = ['imported' => $count, 'hasMore' => $productions->cursor->hasMore, 'lastID' => $lastID];
            if ($capture) return $response;
            else Ajax::StatusOK($response);
        }
        else {            
            if ($capture) return false;
            else Ajax::StatusERR('No productions were imported.');
        }
    }

    public static function Import($capture=false, $id=null)
    {
        $settings = GeneralSettings::Get('productionPosts');
        //$settings['lastIDSync'] = ''; // Debug

        $filter = ['extend' => 'ownerName', 'sort' => ['id' => 'asc']];
        if (trim($settings['lastIDSync']) != '')
            $filter['after'] = $settings['lastIDSync'];

        $entase = \Entase\Plugins\WP\Core\EntaseSDK::PrepareClient();
        
        $productions = null;
        try {
            if ($id !== null) { // Import one
                $production = $entase->productions->GetByID($id);
                if ($production != null) $productions = [$production];
            }
            else $productions = $entase->productions->GetAll($filter); 
        }
        catch (\Entase\SDK\Exceptions\Base $ex) {}

        if ($productions != null)
        {
            $count = 0;
            foreach($productions as $production)
            {
                $tags = self::ExtractProductionTags($production);
                $meta = self::PrepareMetaFromAPI($production);

                wp_insert_post([
                    'post_title' => $production->title, 
                    'post_type' => 'production', 
                    'post_content' => '',
                    'post_status' =>  'publish',
                    'meta_input' => $meta,
                    'tags_input' => $tags
                ]);

                // If it's single post import
                // don't make any setting updates
                // just leave the function
                if ($id !== null) return true;

                $settings['lastIDSync'] = $production->id;
                $count++;
                //break; // Import by one for debugging
            }

            GeneralSettings::Set('productionPosts', $settings);

            $response = ['imported' => $count, 'hasMore' => $productions->cursor->hasMore];
            if ($capture) return $response;
            else Ajax::StatusOK($response);
        }
        else {            
            if ($capture) return false;
            else Ajax::StatusERR('No productions were imported.');
        }
    }

    public static function Save($post)
    {
        $productionID = get_post_meta($post->ID, 'entase_id', true);
        if ($productionID == '') return '';

        $entase = \Entase\Plugins\WP\Core\EntaseSDK::PrepareClient();
        $production = null;
        try { $production = $entase->productions->GetByID($productionID); }
        catch (\Entase\SDK\Exceptions\Base $ex) {}

        if ($production != null)
        {
            $meta = self::PrepareMetaFromAPI($production, true);
            foreach ($meta as $key => $val) 
            {
                if ($key == 'entase_id') continue;
                update_post_meta($post->ID, $key, $val);
            }
            
            wp_set_post_tags($post->ID, self::ExtractProductionTags($production));
        }
    }

    public static function PrepareMetaFromAPI($production, $resolveOwnerName=false)
    {
        $entase = \Entase\Plugins\WP\Core\EntaseSDK::PrepareClient();
        
        $photos = null;
        try {
            $photos = $entase->photos->GetByObject('Production:'.$production->id, [
                'serve' => ['100x100', '300x300', '900x900']
            ]);
        } catch (\Entase\SDK\Exceptions\Base $ex) {}
        
        $metaPhoto = [];
        if ($photos != null)
        {
            foreach ($photos as $photo) 
            {
                if ($photo->type == 'poster')
                {
                    $metaPhoto['poster'] = [
                        'small' => $photo->urls[0],
                        'medium' => $photo->urls[1],
                        'large' => $photo->urls[2],
                    ];
                }
                elseif ($photo->type == 'og')
                {
                    $metaPhoto['og'] = [
                        'large' => $photo->urls[2]
                    ];
                }
            }
        }

        $meta = [
            'entase_id' => $production->id,
            'entase_title' => $production->title,
            'entase_story' => str_replace('\\', '\\\\', $production->story), // Because of the Tags markup
            'entase_photo' => json_encode($metaPhoto, JSON_UNESCAPED_UNICODE),
            'entase_cohosting' => $production->cohosting,
            'entase_ownerRef' => $production->ownerRef
        ];

        if ($resolveOwnerName && $production->cohosting) {
            $partner = null;
            try { $partner = $entase->partners->GetByID($production->ownerRef); } 
            catch (\Entase\SDK\Exceptions\Base $ex) {}

            if ($partner != null) {
                $meta['entase_ownerName'] = $partner->name;
            }
        }

        return $meta;
    }
}