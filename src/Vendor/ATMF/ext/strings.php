<?php

/**
 * ATMF string core extensions. Part of ATMF core.
 * @license: Apache-2.0 License
 * @repository: https://github.com/skito/ATMF-PHP
 */

namespace ATMF\CoreExtensions;

class StrUppercase implements \ATMF\Extension
{
    public function __construct() {}
    public function Get($args)
    {
        $str = '';
        foreach($args as $arg) $str .= $arg;
        return mb_strtoupper($str);
    }

    public function Set($args, $value) {return false;}
}
\ATMF\Extensions::Register('upper', new StrUppercase());


class StrLowercase implements \ATMF\Extension
{
    public function __construct() {}
    public function Get($args)
    {
        $str = '';
        foreach($args as $arg) $str .= $arg;
        return mb_strtolower($str);
    }

    public function Set($args, $value) {return false;}
}
\ATMF\Extensions::Register('lower', new StrLowercase());


class StrUcfirst implements \ATMF\Extension
{
    public function __construct() {}
    public function Get($args)
    {
        $str = '';
        foreach($args as $arg) $str .= $arg;

        if ($str != '')
        {
            $firstChar = mb_substr($str, 0, 1);
            $then = mb_substr($str, 1, null);
            return mb_strtoupper($firstChar).$then;
        }
        else return '';
    }

    public function Set($args, $value) {return false;}
}
\ATMF\Extensions::Register('ucfirst', new StrUcfirst());


class StrLcfirst implements \ATMF\Extension
{
    public function __construct() {}
    public function Get($args)
    {
        $str = '';
        foreach($args as $arg) $str .= $arg;

        if ($str != '')
        {
            $firstChar = mb_substr($str, 0, 1);
            $then = mb_substr($str, 1, null);
            return mb_strtolower($firstChar).$then;
        }
        else return '';
    }

    public function Set($args, $value) {return false;}
}
\ATMF\Extensions::Register('lcfirst', new StrLcfirst());
