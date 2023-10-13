<?php

namespace ATMF\CoreExtensions;

use Entase\Plugins\WP\Conf;

class Version implements \ATMF\Extension
{
    public function __construct() {}
    public function Get($args)
    {
        return Conf::Version;
    }

    public function Set($args, $value) {return false;}
}
\ATMF\Extensions::Register('version', new Version());