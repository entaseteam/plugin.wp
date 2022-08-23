<?php

namespace Entase\Plugins\WP\Core;

class Settings
{
    protected static $tableKey = '';
    protected static $defaults = [];
    protected static $data = null;

    public static function Read($reload=false)
    {
        $class = get_called_class();
        if ($reload || $class::$data == null)
        {
            $rawData = get_option($class::$tableKey);
            $decodedData = json_decode($rawData, true) ?? [];
            if ($rawData != '') $class::$data = array_merge($class::$defaults, $decodedData);
            else $class::$data = $class::$defaults;
        }

        return $class::$data;
    }

    public static function Write()
    {

        $class = get_called_class();
        if ($class::$data == null) $class::Read();
        if ($class::$data == null) return false;

        update_option($class::$tableKey, json_encode($class::$data));
        return true;
    }

    public static function Get($key)
    {
        $class = get_called_class();
        if ($class::$data == null) $class::Read();
        if ($class::$data == null) return null;

        return $class::$data[$key] ?? null;
    }

    public static function Set($key, $value, $writeNow=true)
    {
        $class = get_called_class();
        if ($class::$data == null) $class::Read();
        if ($class::$data == null) return false;

        $class::$data[$key] = $value;
        if ($writeNow) return $class::Write();
        else return true;
    }
}