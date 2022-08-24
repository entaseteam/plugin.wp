<?php

namespace Entase\Plugins\WP\Core;

use \Entase\Plugins\WP\Conf;
use \Entase\Plugins\WP\Utilities\Helper;
use \Entase\Plugins\WP\Utilities\Shortcodes;
use \Entase\Plugins\WP\Utilities\Ajax;


class Events
{
    public static $photo = null;

    public static function PostsMenu()
    {
        global $current_screen;
        if ($current_screen->post_type != 'event') {
            return;
        }

        Helper::OutputImportButton('events');

        add_filter('manage_event_posts_columns' , [__CLASS__, 'TableHeader']);
        add_action('manage_event_posts_custom_column' , ['\Entase\Plugins\WP\Utilities\Helper', 'CustomTableColumns']);

        wp_enqueue_style('post-tables', Conf::CSSUrl.'/admin/post-tables.css');
    }

    public static function TableHeader($columns)
    {
        unset($columns['date']);
        return array_merge([
            'entase_photo_poster' => '&nbsp;'
        ], $columns, [
            'entase_dateStart' => 'Date Start',
            'entase_status' => 'Status'
        ]);
    }

    public static function MetaBoxes()
    {
        wp_enqueue_script('entase-meta', Conf::JSUrl.'/admin/meta.js', ['jquery'], false, true);
        wp_enqueue_style('entase-meta', Conf::CSSUrl.'/admin/meta-boxes.css');

        $shortcodeBtn = '<a href="javascript:void(0);" class="_btnEntase_CopyShortcode" data-shortcode="$1">Shortcode</a>';
        $metaboxes = [
            [
                'name' => 'entase_info',
                'shortcode' => '',
                'title' => 'Info - Entase',
                'context' => 'advanced',
                'priority' => 'default'                
            ],
            [
                'name' => 'entase_status',
                'shortcode' => '',
                'title' => 'Status - Entase',
                'context' => 'side',
                'priority' => 'default'
            ],
            [
                'name' => 'entase_photo_poster',
                'shortcode' => 'entase_photo_poster',
                'title' => 'Poster - Entase',
                'context' => 'side',
                'priority' => 'default'
            ],
            [
                'name' => 'entase_photo_og',
                'shortcode' => 'entase_photo_og',
                'title' => 'OG - Entase',
                'context' => 'side',
                'priority' => 'default'
            ]
        ];

        foreach ($metaboxes as $box)
        {
            add_meta_box($box['name'], $box['title'].' '.($box['shortcode'] != '' ? str_replace('$1', $box['shortcode'], $shortcodeBtn) : ''), [__CLASS__, 'DisplayMetaBox'], 'event', $box['context'], $box['priority'], $box);
        }
    }

    public static function DisplayMetaBox($post, $args=[])
    {
        $box = $args['args'];
        switch($box['name'])
        {
            case 'entase_info':
                $properties = [
                    [
                        'name' => 'Event ID',
                        'value' => get_post_meta($post->ID, 'entase_id', true),
                        'shortcode' => '[entase_id]'
                    ],
                    [
                        'name' => 'Production ID',
                        'value' => get_post_meta($post->ID, 'entase_productionID', true),
                        'shortcode' => '[entase_productionid]'
                    ],
                ];

                $atmf = \ATMF\Setup::GetEngine();
                $atmf->vars['properties'] = $properties;
                $atmf->RendTemplate('Snipptes/EventInfoBox');
                break;
            case 'entase_status':
                echo '<div><label>Start:</label> '; Helper::CustomTableColumns('entase_dateStart'); echo '</div>';
                echo '<div><label>Status:</label> '; Helper::CustomTableColumns('entase_status'); echo '</div>';
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

    public static function Import()
    {
        $settings = GeneralSettings::Get('eventPosts');
        $settings['lastIDSync'] = ''; // Debug

        $filter = ['extend' => 'productionTitle', 'sort' => ['id' => 'asc']];
        if (trim($settings['lastIDSync']) != '')
            $filter['after'] = $settings['lastIDSync'];

        $entase = \Entase\Plugins\WP\Core\EntaseSDK::PrepareClient();
        
        $events = null;
        try { $events = $entase->events->GetAll($filter); }
        catch (\Entase\SDK\Exceptions\Base $ex) {}

        if ($events != null)
        {
            foreach($events as $event)
            {
                $meta = self::PrepareMetaFromAPI($event);
                wp_insert_post([
                    'post_title' => $event->extend->productionTitle, 
                    'post_type' => 'event', 
                    'post_content' => '',
                    'post_status' =>  'publish',
                    'meta_input' => $meta
                ]);

                $settings['lastIDSync'] = $event->id;
                break;
            }

            GeneralSettings::Set('productionPosts', $settings);
        }

        Ajax::StatusOK(['hasMore' => $events->hasMore]);
    }

    public static function Save($post)
    {
        $eventID = get_post_meta($post->ID, 'entase_id', true);
        if ($eventID == '') return '';

        $entase = \Entase\Plugins\WP\Core\EntaseSDK::PrepareClient();
        $event = null;
        try { $event = $entase->events->GetByID($eventID); }
        catch (\Entase\SDK\Exceptions\Base $ex) {}

        if ($event != null)
        {
            /*$meta = self::PrepareMetaFromAPI($production);
            foreach ($meta as $key => $val) 
            {
                if ($key == 'entase_id') continue;
                update_post_meta($post->ID, $key, $val);
            }*/
        }
    }

    public static function PrepareMetaFromAPI($event)
    {
        $entase = \Entase\Plugins\WP\Core\EntaseSDK::PrepareClient();
        $photos = null;
        try {
            $photos = $entase->photos->GetByObject('Production:'.$event->productionID, [
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

        return [
            'entase_id' => $event->id,
            'entase_productionID' => $event->productionID,
            'entase_dateStart' => $event->dateStart,
            'entase_status' => $event->status,
            'entase_photo' => json_encode($metaPhoto, JSON_UNESCAPED_UNICODE),
            'entase_location_countryCode' => $event->location->countryCode,
            'entase_location_countryName' => $event->location->countryName,
            'entase_location_cityName' => $event->location->cityName,
            'entase_location_postCode' => $event->location->postCodeName,
            'entase_location_address' => $event->location->address,
            'entase_location_placeName' => $event->location->placeName,
            'entase_location_lat' => $event->location->lat,
            'entase_location_lng' => $event->location->lng
        ];
    }
}