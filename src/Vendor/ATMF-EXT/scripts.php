<?php

namespace ATMF\CoreExtensions;

use Entase\Plugins\WP\Conf;

class Script implements \ATMF\Extension
{
    public function __construct() {}
    public function Get($args)
    {
        if (!is_array($args))
            return '';

        $src = $args[0] ?? '';
        $type = $args[1] ?? 'text/javascript';
        $opts = $args[2] ?? '';
        
        return $src != '' ? '<script type="'.$type.'" src="'.Conf::JSUrl.'/'.$src.'" '.$opts.'></script>' : '';
    }

    public function Set($args, $value) {return false;}
}
\ATMF\Extensions::Register('script', new Script());


class Style implements \ATMF\Extension
{
    public function __construct() {}
    public function Get($args)
    {
        if (!is_array($args))
            return '';

        $src = $args[0] ?? '';
        $opts = $args[1] ?? '';

        return $src != '' ? '<link rel="stylesheet" href="'.Conf::CSSUrl.'/'.$src.'" '.$opts.' />' : '';
    }

    public function Set($args, $value) {return false;}
}
\ATMF\Extensions::Register('style', new Style());