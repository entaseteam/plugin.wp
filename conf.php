<?php

namespace Entase\Plugins\WP;

abstract class Conf {
    const Version = '1.0';
    const BaseUrl = '/wp-content/plugins/entase';
    const BasePath = __DIR__;
    const VendorPath = __DIR__.'/src/Vendor';
    const TemplatesPath = __DIR__.'/src/Templates';
    const JSUrl = self::BaseUrl.'/assets/js';
    const CSSUrl = self::BaseUrl.'/assets/css';
}