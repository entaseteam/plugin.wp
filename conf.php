<?php

namespace Entase\Plugins\WP;

abstract class Conf {
    const Version = '1.0';

    // Backend
    const BasePath = __DIR__;
    const VendorPath = self::BasePath.'/src/Vendor';
    const ImagePath = self::BasePath.'/assets/img';
    const TemplatesPath = self::BasePath.'/src/Templates';

    // Frontend
    const BaseUrl = '/wp-content/plugins/entase';
    const JSUrl = self::BaseUrl.'/assets/js';
    const CSSUrl = self::BaseUrl.'/assets/css';
    const ImageURL = self::BaseUrl.'/assets/img';
}