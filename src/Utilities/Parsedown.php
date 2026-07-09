<?php

namespace Utilities;

require_once __DIR__ .'/../Vendor/ParseDown/Parsedown.php';

class Parsedown extends \ParseDown\Parsedown
{
    private const PURE_LINK_REL = 'noopener noreferrer nofollow ugc';
    private const PURE_LINK_DISPLAY_MAX_LENGTH = 90;
    private const PURE_LINK_DISPLAY_HEAD_LENGTH = 60;
    private const PURE_LINK_DISPLAY_TAIL_LENGTH = 24;

    public const headingFromH2 = array(
        'headings' => array(
            1 => 'h2',
            2 => 'h3',
            3 => 'h4',
            4 => 'h5',
            5 => 'h6',
            6 => 'h6',
        ),
    );

    public static $headingFromH2 = array(
        'headings' => array(
            1 => 'h2',
            2 => 'h3',
            3 => 'h4',
            4 => 'h5',
            5 => 'h6',
            6 => 'h6',
        ),
    );

    protected $_elementMap = array();

    function __construct()
    {
        // Render raw HTML tags as text and sanitize unsafe link schemes.
        $this->setMarkupEscaped(true);
        $this->setSafeMode(true);
    }

    function text($text, $options = null)
    {
        $this->_elementMap = $this->_BuildElementMap($options);
        $text = $this->_NormalizeListHeadingSpacing($text);

        return parent::text($text);
    }

    protected function element(array $Element)
    {
        if (isset($Element['name']))
        {
            $sourceElementName = strtolower($Element['name']);
            $mappedElementName = $this->_elementMap[$sourceElementName] ?? null;

            if ($mappedElementName !== null)
            {
                $Element['name'] = $mappedElementName;
            }
        }

        return parent::element($Element);
    }

    protected function inlineUrl($Excerpt)
    {
        $inline = parent::inlineUrl($Excerpt);

        return $this->_EnhancePureLinkInline($inline);
    }

    protected function inlineUrlTag($Excerpt)
    {
        $inline = parent::inlineUrlTag($Excerpt);

        return $this->_EnhancePureLinkInline($inline);
    }

    private function _EnhancePureLinkInline($inline)
    {
        if (!is_array($inline) || !isset($inline['element']) || !is_array($inline['element']))
        {
            return $inline;
        }

        if (($inline['element']['name'] ?? null) !== 'a')
        {
            return $inline;
        }

        $href = $inline['element']['attributes']['href'] ?? null;

        if (!is_string($href))
        {
            return $inline;
        }

        $href = trim($href);

        if ($href === '')
        {
            return $inline;
        }

        if (!isset($inline['element']['attributes']) || !is_array($inline['element']['attributes']))
        {
            $inline['element']['attributes'] = array();
        }

        $inline['element']['attributes']['href'] = $href;
        $inline['element']['attributes']['target'] = '_blank';
        $inline['element']['attributes']['rel'] = self::PURE_LINK_REL;
        $inline['element']['text'] = $this->_GetPureLinkDisplayText($href);

        return $inline;
    }

    private function _GetPureLinkDisplayText($href)
    {
        $href = trim((string) $href);

        if ($href === '')
        {
            return $href;
        }

        if (mb_strlen($href, 'UTF-8') <= self::PURE_LINK_DISPLAY_MAX_LENGTH)
        {
            return $href;
        }

        return mb_substr($href, 0, self::PURE_LINK_DISPLAY_HEAD_LENGTH, 'UTF-8')
            . '...'
            . mb_substr(
                $href,
                mb_strlen($href, 'UTF-8') - self::PURE_LINK_DISPLAY_TAIL_LENGTH,
                self::PURE_LINK_DISPLAY_TAIL_LENGTH,
                'UTF-8'
            );
    }

    private function _BuildElementMap($options)
    {
        if (!is_array($options))
        {
            return array();
        }

        $map = array();

        if (isset($options['elements']) && is_array($options['elements']))
        {
            $this->_ApplyElementMappings($map, $options['elements']);
        }

        if (isset($options['headings']) && is_array($options['headings']))
        {
            $this->_ApplyHeadingMappings($map, $options['headings']);
        }

        foreach ($options as $optionName => $optionValue)
        {
            if ($optionName === 'elements' || $optionName === 'headings')
            {
                continue;
            }

            $headingLevel = $this->_ExtractHeadingLevel($optionName);

            if ($headingLevel !== null)
            {
                $targetElement = $this->_NormalizeTagName($optionValue);

                if ($targetElement !== null)
                {
                    $map['h' . $headingLevel] = $targetElement;
                }

                continue;
            }

            $sourceElement = $this->_NormalizeTagName($optionName);
            $targetElement = $this->_NormalizeTagName($optionValue);

            if ($sourceElement !== null && $targetElement !== null)
            {
                $map[$sourceElement] = $targetElement;
            }
        }

        return $map;
    }

    private function _NormalizeListHeadingSpacing($text)
    {
        if (!is_string($text) || $text === '')
        {
            return $text;
        }

        $text = str_replace(array("\r\n", "\r"), "\n", $text);
        $lines = explode("\n", $text);

        $normalizedLines = array();
        $isInList = false;
        $fenceMarker = null;

        foreach ($lines as $line)
        {
            $trimmedLine = ltrim($line);
            $lineFenceMarker = $this->_DetectFenceMarker($trimmedLine);
            if ($lineFenceMarker !== null)
            {
                if ($fenceMarker === null)
                {
                    $fenceMarker = $lineFenceMarker;
                    $isInList = false;
                }
                else if ($fenceMarker === $lineFenceMarker)
                {
                    $fenceMarker = null;
                }

                $normalizedLines[] = $line;
                continue;
            }

            if ($fenceMarker !== null)
            {
                $normalizedLines[] = $line;
                continue;
            }

            if (
                $this->_IsHeadingLine($line)
                && $isInList
                && count($normalizedLines) > 0
                && trim($normalizedLines[count($normalizedLines) - 1]) !== ''
            ) {
                $normalizedLines[] = '';
            }

            $normalizedLines[] = $line;

            if ($this->_IsBlankLine($line))
            {
                $isInList = false;
                continue;
            }

            if ($this->_IsListMarkerLine($line))
            {
                $isInList = true;
                continue;
            }

            if ($isInList && $this->_IsListContinuationLine($line))
            {
                continue;
            }

            $isInList = false;
        }

        return implode("\n", $normalizedLines);
    }

    private function _ApplyElementMappings(array &$map, array $elements)
    {
        foreach ($elements as $sourceElement => $targetElement)
        {
            $sourceElement = $this->_NormalizeTagName($sourceElement);
            $targetElement = $this->_NormalizeTagName($targetElement);

            if ($sourceElement !== null && $targetElement !== null)
            {
                $map[$sourceElement] = $targetElement;
            }
        }
    }

    private function _ApplyHeadingMappings(array &$map, array $headings)
    {
        foreach ($headings as $headingLevel => $targetElement)
        {
            $headingLevel = $this->_ExtractHeadingLevel($headingLevel);

            if ($headingLevel === null)
            {
                continue;
            }

            $targetElement = $this->_NormalizeTagName($targetElement);

            if ($targetElement !== null)
            {
                $map['h' . $headingLevel] = $targetElement;
            }
        }
    }

    private function _ExtractHeadingLevel($value)
    {
        if (is_int($value) || (is_string($value) && ctype_digit($value)))
        {
            $headingLevel = (int) $value;

            return $headingLevel >= 1 && $headingLevel <= 6 ? $headingLevel : null;
        }

        if (!is_string($value))
        {
            return null;
        }

        if (preg_match('/^(?:headings?_?)?level_?([1-6])$/i', $value, $matches))
        {
            return (int) $matches[1];
        }

        if (preg_match('/^(?:headings?_?)?h([1-6])$/i', $value, $matches))
        {
            return (int) $matches[1];
        }

        if (preg_match('/^headings?_([1-6])$/i', $value, $matches))
        {
            return (int) $matches[1];
        }

        return null;
    }

    private function _NormalizeTagName($tagName)
    {
        if (!is_string($tagName) && !is_int($tagName))
        {
            return null;
        }

        $tagName = strtolower(trim((string) $tagName));
        $tagName = trim($tagName, " \t\n\r\0\x0B<>/");

        if ($tagName === '')
        {
            return null;
        }

        if (!preg_match('/^[a-z][a-z0-9-]*$/', $tagName))
        {
            return null;
        }

        return $tagName;
    }

    private function _DetectFenceMarker($line)
    {
        if (!is_string($line) || $line === '')
        {
            return null;
        }

        if (preg_match('/^(?:`{3,}|~{3,})/', $line, $matches))
        {
            return $matches[0][0];
        }

        return null;
    }

    private function _IsBlankLine($line)
    {
        return trim((string) $line) === '';
    }

    private function _IsHeadingLine($line)
    {
        return preg_match('/^\s{0,3}#{1,6}\s+/', (string) $line) === 1;
    }

    private function _IsListMarkerLine($line)
    {
        return preg_match('/^\s{0,3}(?:[*+-]|\d+[.)])\s+/', (string) $line) === 1;
    }

    private function _IsListContinuationLine($line)
    {
        return preg_match('/^\s{2,}\S/', (string) $line) === 1;
    }
}
