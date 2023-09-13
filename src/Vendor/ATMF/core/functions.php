<?php

/**
 * ATMF functions handler. Part of ATMF core.
 * @version: ATMF-PHP Engine 1.1
 * @license: Apache-2.0 License
 * @repository: https://github.com/skito/ATMF-PHP
 */

namespace ATMF;

class Functions
{
    private static $_lastConditionResults = [];

    public static function ProcessTag($sender, $tagName, $args)
    {
        switch($tagName)
        {
            case '#template':
                $name = isset($args[0]) ? $args[0] : '';
                $label = isset($args[1]) ? $args[1] : '';
                $template = $sender->GetTemplate($name);
                if (!empty($template) && $label != '')
                {
                    preg_match_all('/\{\#label\s([a-z0-9-_\.]+)\}(.*)\{\#endlabel\}/imsxU', $template, $labels);
                    foreach($labels[0] as $key => $match)
                    {
                        $lblname = $labels[1][$key];
                        if ($lblname == $label)
                            return $labels[2][$key];
                    }
                    return '';
                }
                return $template;
            case '#label':
                $hidden = $args[1] ?? '';
                return $hidden != 'hidden' ? '<%:block_start%><%:show%>' : '<%:block_start%><%:hide%>';
            case '#endlabel':
                return '<%:block_end%>';
            case '#use':
                $path = isset($args[0]) ? $args[0] : '';
                $operator = isset($args[1]) ? $args[1]: 'as';
                if ($path != '' && $operator == 'as')
                {
                    $keypath = (strlen($path) > 1) ? trim(substr($path, 1)) : '';
                    $alias = isset($args[2]) ? $args[2]: $keypath;
                    if ($keypath != '')
                    {
                        Culture::AddAlias($sender, $alias, $keypath);
                        return '';
                    }
                }
                return '';
            case '#if':
                $result = false;
                $matchAll = !in_array('||', $args);
                foreach ($args as $arg)
                {
                    $argns = trim($arg);
                    if (in_array($argns, ['', '&&', '||'])) continue;

                    $result = false;
                    $cmd = substr($argns, 0, 1);
                    $reverseCond = ($cmd == '!');
                    $argValue = $reverseCond ? substr($argns, 1) : $argns;
                    if ($reverseCond && strlen($argValue) > 1) $cmd = substr($argValue, 0, 1);

                    if ($argValue == '') continue;
                    elseif ($cmd == '$')
                        $result = Variables::ProcessTag($sender, $argValue, []);
                    elseif ($cmd == '@')
                        $result = Culture::ProcessTag($sender, $argValue, []);
                        

                    if ($reverseCond)
                        $result = !$result;

                    if ($matchAll && !$result)
                        break;
                }

                self::$_lastConditionResults[] = !$result;
                return $result ? '<%:block_start%><%:show%>' : '<%:block_start%><%:hide%>';
            case '#endif':
            case '#end':
                if (count(self::$_lastConditionResults) > 0)
                    array_pop(self::$_lastConditionResults);

                return '<%:block_end%>';
            case '#else':
                $count = count(self::$_lastConditionResults);
                $lastCondResult = ($count > 0) ? self::$_lastConditionResults[$count-1] : false;
                return $lastCondResult ? '<%:block_end%><%:block_start%><%:show%>' : '<%:block_end%><%:block_start%><%:hide%>';
            case '#each':
                self::$_lastConditionResults[] = false; // Hollow push, it will be removed at #end match
                if (count($args) == 3 && in_array(trim($args[1]), ['as', 'in']))
                {
                    $operator = trim($args[1]);
                    $collection = trim($operator == 'as' ? $args[0] : $args[2]);
                    $item = trim($operator == 'as' ? $args[2] : $args[0]);

                    if (strlen($collection) < 2 || strlen($item) < 2) die('ATMF Error: Wrong #each syntax!');

                    return '<%:block_start%><%:each%><%:'.substr($collection, 1).':'.substr($item, 1).'%>';
                }
                else die('ATMF Error: Wrong #each syntax!');
        }
    }

    public static function SetTag($sender, $tagName, $args, $value)
    {
        switch($tagName)
        {
            case '#template':
                $name = isset($args[0]) ? $args[0] : '';
                if ($name != '')
                {
                    $sender->SetTemplate($name, $value);
                    return true;
                }
                else return false;
        }

        return false;
    }
}