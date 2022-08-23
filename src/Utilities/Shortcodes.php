<?php

namespace Entase\Plugins\WP\Utilities;

class Shortcodes
{
    static function MarkupToHTML($markup, $options=[])
    {
        $options = array_merge([
            'searchurl' => 'https://www.entase.bg/?search=$tag',
            'linktarget' => '_blank' 
        ], $options);
        // (<ts-person>)(.*?)(\\)(.*?)(<\/ts-person>)

        $options['searchurl'] = str_replace('$tag', '$4', $options['searchurl']);

        $html = $markup;
        $html = preg_replace('/(\#[a-zA-Z0-9\p{L}]+)/umx', '<a class="tag hashtag">$1</a>', $html);
        $html = preg_replace('/(@[a-zA-Z0-9-_\.\/\p{L}\\\]+)/umx', '<a class="tag person">$1</a>', $html);
        $html = self::preg_replace_all('/(<a.*class="tag.*person".*>)(.*?)(\\\)(.*?)(<\/a>)/um', '$1$2 $4$5', $html);
        $html = preg_replace('/(<a class="tag.*?)(>)(@|#)(.*?)(<\/a>)/um', '$1 target="'.$options['linktarget'].'" href="'.$options['searchurl'].'"$2$3$4$5', $html);
        return $html;
    }

    private static function preg_replace_all($regex, $replace, $str)
    {
        while (preg_match($regex, $str))
            $str = preg_replace($regex, $replace, $str);

        return $str;
    }
}