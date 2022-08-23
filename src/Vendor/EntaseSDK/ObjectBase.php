<?php

namespace Entase\SDK;

class ObjectBase
{
    public static function Cast($obj)
    {
        $calledClass = get_called_class();
        $a = new $calledClass();
        foreach ($obj as $property => $value)
        {
            if (property_exists($a, $property))
            {
                $subcastValue = null;
                $subcastType = null;
                if ($value != null && (is_object($value) || is_array($value)))
                    $subcastType = ((array)$value)['::'] ?? null;
                
                if ($subcastType != null && class_exists('\\Entase\\'.$subcastType))
                    $a->$property = call_user_func(['\\Entase\\'.$subcastType, 'Cast'], $value);
                else $a->$property = $value;
            }
        }

        return $a;
    }
}