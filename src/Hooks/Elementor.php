<?php

namespace Entase\Plugins\WP\Hooks;

class Elementor
{
    public static function DynamicTags($tagManager)
    {
        $tagManager->register(new \Entase\Plugins\WP\ElementorTags\ProductionPhotoPoster());
        $tagManager->register(new \Entase\Plugins\WP\ElementorTags\ProductionPhotoOG());
        $tagManager->register(new \Entase\Plugins\WP\ElementorTags\ProductionTitle());
        $tagManager->register(new \Entase\Plugins\WP\ElementorTags\ProductionStory());
    }

    public static function Widgets($widgetManager)
    {
        $widgetManager->register(new \Entase\Plugins\WP\ElementorWidgets\Productions());
    }
}
