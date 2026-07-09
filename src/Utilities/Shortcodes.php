<?php

namespace Entase\Plugins\WP\Utilities;

class Shortcodes
{
    /**
     * Render an Entase story into HTML.
     *
     * The Entase API now delivers stories as Markdown, but older stories
     * (and legacy content) still rely on the custom markup: hashtags
     * (#foo) and person tags (@Person\Name\Family). Both must keep working.
     *
     * The legacy tags are extracted into placeholders before the Markdown
     * is parsed (so Parsedown does not treat "#foo" as a heading or eat the
     * backslashes in a person tag) and are restored as links afterwards.
     */
    static function MarkupToHTML($markup, $options=[])
    {
        $options = array_merge([
            'searchurl' => 'https://www.entase.com/?search=$tag',
            'linktarget' => '_blank'
        ], is_array($options) ? $options : []);

        $markup = (string)$markup;
        if (trim($markup) === '')
            return '';

        // 1. Protect legacy hashtags / person tags from the Markdown parser.
        $tokens = [];
        $markup = self::ProtectLegacyTags($markup, $options, $tokens);

        // 2. Parse the Markdown into HTML.
        $html = self::ParseMarkdown($markup);

        // 3. Restore the legacy tags as their rendered links.
        if (!empty($tokens))
            $html = strtr($html, $tokens);

        // 4. Keep backwards compatibility with the old BBCode-style markup.
        $html = self::LegacyBBCodeToHTML($html);

        return $html;
    }

    private static function ParseMarkdown($markup)
    {
        // The Parsedown helper lives outside the plugin namespace and is not
        // handled by the autoloader, so it is required explicitly here.
        require_once __DIR__.'/Parsedown.php';

        $parser = new \Utilities\Parsedown();

        return $parser->text($markup, \Utilities\Parsedown::$headingFromH2);
    }

    private static function ProtectLegacyTags($markup, $options, &$tokens)
    {
        $prefix = 'entasetoken'.substr(md5(uniqid('', true)), 0, 12);
        $index = 0;

        // A legacy tag only starts at the beginning of the text or right after
        // whitespace. The (?<!\S) guard keeps "#" and "@" that live inside URLs,
        // emails, inline code, etc. (e.g. "example.com/page#section") untouched
        // so the Markdown parser can handle them normally.

        // Person tags: @Person\Name\Family (may contain backslashes/slashes).
        $markup = preg_replace_callback('/(?<!\S)@[\p{L}0-9._\/\\\\-]+/u', function($match) use (&$tokens, &$index, $prefix, $options) {
            $token = $prefix.($index++).'z';
            $tokens[$token] = self::RenderPersonTag($match[0], $options);
            return $token;
        }, $markup);

        // Hashtags: #foo. A "#" immediately followed by a word character is a
        // legacy hashtag; "# " (with a space) stays a Markdown heading.
        $markup = preg_replace_callback('/(?<!\S)#[\p{L}0-9]+/u', function($match) use (&$tokens, &$index, $prefix, $options) {
            $token = $prefix.($index++).'z';
            $tokens[$token] = self::RenderHashtag($match[0], $options);
            return $token;
        }, $markup);

        return $markup;
    }

    private static function RenderPersonTag($raw, $options)
    {
        $name = substr($raw, 1);
        $name = str_replace('\\', ' ', $name);
        $name = trim(preg_replace('/\s+/u', ' ', $name));

        return self::RenderTagLink('person', '@', $name, $options);
    }

    private static function RenderHashtag($raw, $options)
    {
        return self::RenderTagLink('hashtag', '#', substr($raw, 1), $options);
    }

    private static function RenderTagLink($type, $symbol, $label, $options)
    {
        if ($label === '')
            return $symbol;

        if ($options['searchurl'] === '')
            return $symbol.$label;

        $href = str_replace('$tag', $label, $options['searchurl']);

        return '<a class="tag '.$type.'" target="'.$options['linktarget'].'" href="'.$href.'">'.$symbol.$label.'</a>';
    }

    private static function LegacyBBCodeToHTML($html)
    {
        $html = preg_replace('/(\[b\])(.*?)(\[\/b\])/m', '<b>$2</b>', $html);
        $html = preg_replace('/(\[i\])(.*?)(\[\/i\])/m', '<i>$2</i>', $html);
        for ($level = 1; $level <= 5; $level++)
            $html = preg_replace('/(\[h'.$level.'\])(.*?)(\[\/h'.$level.'\])/m', '<h'.$level.'>$2</h'.$level.'>', $html);

        return $html;
    }
}
