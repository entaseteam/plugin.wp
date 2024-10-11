<?php

namespace Entase\Plugins\WP\Core;

use \Entase\Plugins\WP\Utilities\Skins;

class FEPages
{
    public static function Load()
    {
        if (stripos($_SERVER['REQUEST_URI'], '/entase/previewskin') === 0) {
            self::PreviewSkin();
        }
    }

    

    public static function PreviewSkin()
    {
        
        http_response_code(200);
        $atmf = \ATMF\GetEngine();

        $fields = [];
        
        $_POST = array_map('stripslashes_deep', $_POST);
        $widget = $_POST['widget'] ?? '';
        $templateJson = $_POST['template'] ?? '';
        $template = @json_decode($templateJson) ?? null;
        if ($template != null) 
        {
            $atmf->__('$_widget_src', Skins::BuildTemplateSource($template));
            $fields = Skins::ExtractTemplateFields($template);

            if ($widget == 'events')
                $fields['fields'][] = 'entase_book'; // Append booking button at the end
        }
        else die('Empty template.');
        
        //echo '[entase_events fields="'.implode(',', $fields).'"]';exit;
        echo '<html><head>';
        wp_head();
        echo '</head><body class="entase-skin-preview">';
        
        $fieldsStr = implode(',', $fields['fields']);
        $metaStr = implode(',', $fields['meta']);
        $taxonomiesStr = implode(',', $fields['taxonomies']);
        $multiSourceImgStr = implode(',', ['post_feature_image', 'entase_photo_poster', 'entase_photo_og']);
        
        if ($widget == 'events')
        {
            $atmf->__('$_template_preview', $atmf->RendTemplate('Widgets/Events_Custom', true));
            echo do_shortcode('[entase_events limit="1" status="0,1" fields="'.$fieldsStr.'" metafields="'.$metaStr.'" taxonomies="'.$taxonomiesStr.'"]');
        }
        else if ($widget == 'productions')
        {
            $atmf->__('$_template_preview', $atmf->RendTemplate('Widgets/Productions_Custom', true));
            echo do_shortcode('[entase_productions limit="1" fields="'.$fieldsStr.'" metafields="'.$metaStr.'" multisource_image="'.$multiSourceImgStr.'"]');
        }

        wp_footer();
        echo '</body></html>';

        exit;
    }
}