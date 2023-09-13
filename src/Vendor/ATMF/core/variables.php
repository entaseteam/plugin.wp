<?php

/**
 * ATMF variables handler. Part of ATMF core.
 * @version: ATMF-PHP Engine 1.1
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
        if (is_string($selector)) 
            $selector = explode('.', $selector);

            
        $selectorStr = implode('.', $selector);
        if (is_array($collection) && isset($collection[$selectorStr]))
        {
            $var = $collection[$selectorStr];
            return true;
        }

        $firstSelector = $selector[0];

        if (count($selector) > 1)
        {
            $newCollection = null;

            if ((
                    is_array($collection) && 
                    isset($collection[$firstSelector]) &&
                    (is_array($collection[$firstSelector]) || is_object($collection[$firstSelector]))
                ) ||
                (
                    is_object($collection) && 
                    isset($collection->$firstSelector) &&
                    (is_array($collection->$firstSelector) || is_object($collection->$firstSelector))
                ))
            {
                $newCollection = is_array($collection) ? $collection[$firstSelector] : $collection->$firstSelector;
            }

            if ($newCollection != null)
            {
                unset($selector[0]);
                return self::SelectQuery($newCollection, array_values($selector), $var);
            }
            else return false;
        }
        else
        {
            if (is_array($collection) && isset($collection[$firstSelector]))
            {
                $var = $collection[$firstSelector];
                return true;
            }
            elseif (is_object($collection) && isset($collection->$firstSelector))
            {
                $var = $collection->$firstSelector;
                return true;
            }

            $var = '';
            return false;
        }
    }
}