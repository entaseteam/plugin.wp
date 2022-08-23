<?php

/**
 * ATMF variables handler. Part of ATMF core.
 * @version: ATMF-PHP Engine 1.0
 * @license: Apache-2.0 License
 * @repository: https://github.com/skito/ATMF-PHP
 */

namespace ATMF;

class Variables
{
    public static function ProcessTag($sender, $tagName, $args)
    {
        $varname = substr($tagName, 1);
        $str = self::Select($sender, $varname);

        foreach($args as $arg)
        {
            if (substr($arg, 0, 1) == '$')
            {
                $varname = substr($arg, 1);
                $str .= self::Select($sender, $varname);
            }
            else $str .= $arg;
        }

        return $str;
    }

    public static function SetTag($sender, $tagName, $args, $value)
    {
        $varname = substr($tagName, 1);
        $sender->vars[$varname] = $value;
        return true;
    }

    public static function Select($sender, $varname)
    {
        $var = null;
        $isAssigned = false;
        for($i=count($sender->eVars);$i > 0;$i--)
        {
            $eVars = $sender->eVars[$i-1];
            if (self::SelectQuery($eVars, $varname, $var)) {
                $isAssigned = true;
                break;
            }
        }

        if (!$isAssigned)
        {
            if (self::SelectQuery($sender->vars, $varname, $var)) {}
            elseif ($sender->allowGlobals && self::SelectQuery($GLOBALS, $varname, $var)) {}
        }

        return $var;
    }

    public static function SelectQuery($collection, $selector, &$var)
    {
        if (is_string($selector)) $selector = explode('.', $selector);
        if (count($selector) > 1)
        {
            $newCollection = isset($collection[$selector[0]]) && is_array($collection[$selector[0]]) ? $collection[$selector[0]] : null;
            if ($newCollection != null)
            {
                unset($selector[0]);
                return self::SelectQuery($newCollection, array_values($selector), $var);
            }
            else return false;
        }
        else
        {
            $var = $collection[$selector[0]] ?? '';
            return isset($collection[$selector[0]]);
        }
    }
}