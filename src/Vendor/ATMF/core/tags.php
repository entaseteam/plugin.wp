<?php

/**
 * ATMF tags handler. Part of ATMF core.
 * @version: ATMF-PHP Engine 1.0
 * @license: Apache-2.0 License
 * @repository: https://github.com/skito/ATMF-PHP
 */

namespace ATMF;

class Tag
{
    public $name;
    public $args;

    function __construct($name='', $args=[]) {
        $this->name = $name;
        $this->args = $args;
    }

    private function BuildArgValues($sender)
    {
        $argValues = [];
        foreach($this->args as $arg)
        {
            $argValues[] = is_object($arg) ? $arg->Build($sender) : $arg;
        }

        return $argValues;
    }

    public function Build($sender)
    {
        $argValues = $this->BuildArgValues($sender);
        switch(substr($this->name, 0, 1))
        {
            case '$':
                return Variables::ProcessTag($sender, $this->name, $argValues);
            case '@':
                return Culture::ProcessTag($sender, $this->name, $argValues);
            case '#':
                return Functions::ProcessTag($sender, $this->name, $argValues);
            case '/':
                return Extensions::ProcessTag($sender, $this->name, $argValues);
        }

        return '';
    }

    public function Set($sender, $value)
    {
        $argValues = $this->BuildArgValues($sender);
        switch(substr($this->name, 0, 1))
        {
            case '$':
                return Variables::SetTag($sender, $this->name, $argValues, $value);
            case '@':
                return Culture::SetTag($sender, $this->name, $argValues, $value);
            case '#':
                return Functions::SetTag($sender, $this->name, $argValues, $value);
            case '/':
                return Extensions::SetTag($sender, $this->name, $argValues, $value);
        }

        return false;
    }

    public static function ParseStr($str='')
    {
        if (strlen($str) < 2 || !in_array(substr($str, 0, 1), ['#','$','@', '/']))
            return null;

        //preg_match_all('/(\{(.*)\}|"(.*)"|\b(\w+)\b)/iUmx', $str, $cmdMatches);
        preg_match_all('/(\{(.*)\}|"(.*)"|\s?([a-z0-9\.\$\@\#\/_\-\!\|\&]+)(?:\s+|$))/imxU', $str, $cmdMatches);

        $tag = new Tag();
        $tag->name = explode(' ', $str)[0];
        foreach($cmdMatches[1] as $key => $match)
        {
            if ($key > 0)
            {
                $resolve = !in_array($tag->name, ['#use', '#if', '#elseif', '#each']);

                if (substr($match, 0, 1) == '"') {
                    $tag->args[] = trim($match, '"');
                }
                elseif (substr($match, 0, 1) == '{') {
                    //$tag->args[] = $resolve ? self::ParseStr(trim($match, '{}')) : trim($match, '{}');
                    $tag->args[] = self::ParseStr(trim($match, '{}'));
                }
                elseif (in_array(substr($match, 0, 1), [' ', '$', '@', '#', '/'])) {
                    $cmd = substr($match, 0, 1) == ' ' ? substr($match, 1) : $match;
                    $tag->args[] = $resolve ? self::ParseStr($cmd) : $cmd;
                }
                else $tag->args[] = trim($match);
            }
        }

        return $tag;

    }

}