<?php

/**
 * ATMF date core extensions. Part of ATMF core.
 * @license: Apache-2.0 License
 * @repository: https://github.com/skito/ATMF-PHP
 */

namespace ATMF\CoreExtensions;

class DateExtension implements \ATMF\Extension
{
    public $config = ['format' => 'Y-m-d H:i'];

    public function __construct() {}

    public function Get($args)
    {
        if (isset($args[0]) && $args[0] == 'config:format')
            return $this->config['format'];

        $format = isset($args[0]) && $args[0] != '_' ? $args[0] : $this->config['format'];
        $timestamp = isset($args[1]) && $args[1] != '_' ? (int)$args[1] : time();
        return date($format, $timestamp);
    }

    public function Set($args, $value)
    {
        $name = isset($args[0]) ? $args[0] : '';
        if (isset($this->config[$name]))
        {
            $this->config[$name] = $value;
            return true;
        }
        else return false;
    }
}
\ATMF\Extensions::Register('date', new DateExtension());
