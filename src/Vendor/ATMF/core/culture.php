<?php

/**
 * ATMF culture handler. Part of ATMF core.
 * @version: ATMF-PHP Engine 1.0
 * @license: Apache-2.0 License
 * @repository: https://github.com/skito/ATMF-PHP
 */

namespace ATMF;

class Culture
{
    static private $_cachedTranslations = [];
    static private $_aliases = [];

    public static function ProcessTag($sender, $tagName, $args)
    {
        $keypath = substr($tagName, 1);
        $realKeypath = $keypath;

        $cultureResources = self::$_cachedTranslations;
        if (!isset($cultureResources[$keypath]))
        {
            $hasAnAlias = false;
            foreach(self::$_aliases as $alias => $path)
            {
                if (strpos($keypath, '.') !== false || $alias != $path)
                {
                    $parts = explode('.', $keypath);
                    if ($parts[0] == $alias)
                    {
                        unset($parts[0]);
                        $realKeypath = $path.'.'.implode('.', $parts);
                        $hasAnAlias = true;
                    }
                }
                elseif (isset($cultureResources[$path.'.'.$keypath]))
                {
                    $realKeypath = $path.'.'.$keypath;
                    $hasAnAlias = true;
                }

                if ($hasAnAlias) break;
            }

            if (!$hasAnAlias)
            {
                $cultureResources = array_merge($cultureResources, self::ResolveCultureResource($sender, $keypath));
                self::$_cachedTranslations = $cultureResources;
            }
        }

        if (!isset($cultureResources[$realKeypath])) return '';

        $resource = $cultureResources[$realKeypath];
        $translation = '';
        if (is_array($resource))
        {
            $plural = isset($args[0]) && is_numeric($args[0]) && $args[0] != 1;
            $translation = !$plural && isset($resource[1]) ? $resource[1] : $resource[0];
        }
        else $translation = $resource;

        foreach($args as $key => $arg)
        {
            $translation = str_replace('$'.$key, $arg, $translation);
        }

        return $translation;
    }

    public static function SetTag($sender, $tagName, $args, $value)
    {
        $keypath = substr($tagName, 1);
        self::$_cachedTranslations[$keypath] = $value;
        return true;
    }

    public static function AddAlias($sender, $alias, $path)
    {
        self::$_aliases[$alias] = $path;
        self::$_cachedTranslations = array_merge(self::$_cachedTranslations, self::ResolveCultureResource($sender, $path));
    }

    public static function ResetTranslations()
    {
        self::$_cachedTranslations = [];
        self::$_aliases = [];
    }


    public static function ResolveCultureResource($sender, $keyname)
    {
        $cultureResources = [];
        $path = $sender->GetCultureFolder().'/'.$sender->GetCulture();
        $keypath = '';
        $keynameNS = explode('/', $keyname);
        $divider = '';
        foreach($keynameNS as $namespace)
        {
            if (strpos($namespace, '.') !== false)
            {
                $nsParts = explode('.', $namespace);
                $keypath .= $divider.$nsParts[0];
            }
            else $keypath .= $divider.$namespace;
            $divider = '/';
        }
        $path .= '/'.$keypath;

        $translations = [];
        if (file_exists($path.'.cache.php')) {
            if (filemtime($path.'.cache.php') >= filemtime($path.'.json')) {
                $translations = include($path.'.cache.php');
            }
            else $translations = self::TranslationCache($path);
        }
        elseif (file_exists($path.'.json')) {
            $translations = self::TranslationCache($path);
        }

        foreach($translations as $key => $value)
        {
            $cultureResources[$keypath.'.'.$key] = $value;
        }

        return $cultureResources;
    }

    private static function TranslationCache($path)
    {
        $translations = json_decode(file_get_contents($path.'.json'), true);
        $phpCache = '<?php'."\n".'return ['."\n";
        foreach($translations as $key => $value)
        {
            $phpCache .= "'".$key."'".' => ';
            if (is_array($value)) {
                $phpCache .= "[";
                foreach($value as $v) {
                    $phpCache .= "'".str_replace("'", "\\'", $v)."',";
                }
                $phpCache .= "],\n";
            }
            else $phpCache .= "'".str_replace("'", "\\'", $value)."',\n";
        }
        $phpCache = trim($phpCache, ",\n")."\n];";
        file_put_contents($path.'.cache.php', $phpCache);

        return $translations;
    }
}