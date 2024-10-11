<?php

namespace Entase\Plugins\WP\Utilities;

class Skins
{
    public static function BuildTemplateSource($template)
    {        
        $src = '';
        if (is_array($template))
        {
            foreach ($template as $element) 
            {
                if (!is_object($element))
                    $element = (object)$element;

                if ($element->type == 'field')
                {
                    if (isset($element->meta) && !is_object($element->meta))
                        $element->meta = (object)$element->meta;
                    elseif (isset($element->taxonomy) && !is_object($element->taxonomy))
                        $element->taxonomy = (object)$element->taxonomy;
                    
                    $key = '';
                    if ($element->name == 'meta_key') $key = 'meta_'.$element->meta->key;
                    elseif ($element->name == 'taxonomy') $key = 'taxonomy_'.$element->taxonomy->type;
                    else $key = $element->name;
                    //$key = $element->name == 'meta_key' ? 'meta_'.$element->meta->key : $element->name;

                    $src .= '\{#if $item.'.$key.'}<div class="event_'.$key.'">\{$item.'.$key.'}</div>\{#endif}';   
                }
                elseif ($element->type == 'group')
                {
                    $classAddon = trim($element->cssClass) != '' ? ' class="'.$element->cssClass.'"' : '';
                    $src .= '<div'.$classAddon.'>'.self::BuildTemplateSource($element->elements).'</div>';
                }
            }
        }

        return $src;
    }

    public static function ExtractTemplateFields($template)
    {
        $fields = [
            'fields' => [],
            'meta' => [],
            'taxonomies' => []
        ];
        
        if (is_array($template))
        {
            foreach ($template as $element) 
            {
                if ($element->type == 'field')
                {
                    if ($element->name == 'meta_key')
                    {
                        $key = $element->meta->key ?? '';
                        $context = $element->meta->context ?? '';
                        $fields['meta'][] = $key.':'.$context;
                    }
                    elseif ($element->name == 'taxonomy')
                    {
                        $type = $element->taxonomy->type ?? '';
                        $context = $element->taxonomy->context ?? '';
                        $nolinks = $element->taxonomy->nolinks ?? false;
                        $atts = $nolinks ? 'nolinks' : '';
                        $fields['taxonomies'][] = $type.':'.$context.':'.$atts;
                    }
                    else $fields['fields'][] = $element->name;
                }
                elseif ($element->type == 'group')
                {
                    $nested = self::ExtractTemplateFields($element->elements);
                    $fields['fields'] = array_merge($fields['fields'], $nested['fields']);
                    $fields['meta'] = array_merge($fields['meta'], $nested['meta']);
                    $fields['taxonomies'] = array_merge($fields['taxonomies'], $nested['taxonomies']);
                }
            }
        }

        $fields['fields']  = array_unique($fields['fields']);
        $fields['meta']  = array_unique($fields['meta']);
        $fields['taxonomies']  = array_unique($fields['taxonomies']);

        return $fields;
    }
}