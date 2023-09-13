<?php

/**
 * ATMF extensions manager. Part of ATMF core.
 * @version: ATMF-PHP Engine 1.1
 * @license: Apache-2.0 License
 * @repository: https://github.com/skito/ATMF-PHP
 */

namespace ATMF;

class Extensions
{
    private static $_extensions = [];

    /**
     * Register custom ATMF extension
     * @param string $name ATMF tag name which will trigger the extension
     * @param mixed $handler Extension handler
     */
    public static function Register($name, $handler)
    {
        if (trim($name) == '') die('ATMF extension handler must have a name!');

        if ($handler instanceof Extension)
            self::$_extensions[trim($name)] = $handler;
        else die('ATMF extension handler must inherit the Extension interface!');
    }

    /**
     * Get all extensions enabled
     * @return Extension[]
     */
    public static function GetAll()
    {
        return self::$_extensions;
    }

    /**
     * Get extension by name
     * @param string $name Extension name
     * @return Extension|null Extension object if found. NULL otherwise.
     */
    public static function GetByName($name)
    {
        if (isset(self::$_extensions[$name]))
            return self::$_extensions[$name];
        else return null;
    }

    /**
     * Process extension tag
     * @param mixed $sender ATMF engine
     * @param mixed $tagName Extension name
     * @param mixed $args Args passed to the extension
     * @return mixed
     */
    public static function ProcessTag($sender, $tagName, $args)
    {

        $extname = substr($tagName, 1);
        $handler = self::GetByName($extname);
        if ($handler == null) return '';

        $str = $handler->Get($args);

        return $str;
    }

    /**
     * Send value to extension tag
     * @param mixed $sender ATMF engine
     * @param mixed $tagName Extension name
     * @param mixed $args Args passed to the extension
     * @param mixed $value Value to set
     * @return mixed
     */
    public static function SetTag($sender, $tagName, $args, $value)
    {
        $extname = substr($tagName, 1);
        $handler = self::GetByName($extname);
        if ($handler == null) return false;

        return $handler->Set($args, $value);
    }
}

/**
 * Extensinsion interface for custom ATMF extensions.
 */
interface Extension
{
    public function Get($args);
    public function Set($args, $value);
}