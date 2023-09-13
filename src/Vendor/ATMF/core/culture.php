<?php

/**
 * ATMF culture handler. Part of ATMF core.
 * @version: ATMF-PHP Engine 1.1
 * @license: Apache-2.0 License
 * @repository: https://github.com/skito/ATMF-PHP
 */

namespace ATMF;

class Culture
{
    static public $settings = null;
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


    public static function ResolveCultureResource($sender, $keyname, $locale=null)
    {

        self::LoadSettings($sender);
        
        $cultureResources = [];
        $localePath = $locale ?? $sender->GetCulture();

        $localeData = self::$settings['locale'] ?? [];
        $default = self::$settings['default'] ?? '';
        $defaultLang = $localeData[$default] ?? [];
        $langCode = isset($localeData[$localePath]) ? $localePath : $default;
        $lang = $localeData[$localePath] ?? $defaultLang;
        if (isset($lang['extend']) && isset($localeData[$lang['extend']]))
            $cultureResources = self::ResolveCultureResource($sender, $keyname, $lang['extend']);

        $path = $sender->GetCultureFolder().'/'.$langCode;
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
        $translations = json_decode(file_get_contents($path.'.json'));
        $phpCache = '<?php'."\n".'return '.trim(self::Export($translations), ", \n").";";
        $translations = json_decode(file_get_contents($path.'.json'), true);
        file_put_contents($path.'.cache.php', $phpCache);

        return include($path . '.cache.php');
    }

    private static function Export($value, $level=0) {
        $phpCache = '';
        $prefix = str_repeat("\t", $level);
        if (is_array($value) || is_object($value)) {
            $phpCache .= "[\n";
            foreach($value as $k => $v) {
                $phpCache .= $prefix;
                $phpCache .= is_object($value) ? "'".$k."' => ".self::Export($v, $level + 1) : self::Export($v, $level + 1);
            }
            $phpCache .= $prefix."],\n";
        }
        else $phpCache .= "'".str_replace("'", "\\'", $value)."',\n";

        return $phpCache;
    }

    public static function LoadSettings($sender) 
    {
        if (self::$settings != null) 
            return;

        $path = $sender->GetCultureFolder() . '/culture';
        if (file_exists($path.'.cache.php')) {
            if (filemtime($path.'.cache.php') >= filemtime($path.'.json')) {
                self::$settings = include($path.'.cache.php');
            }
            else self::$settings = self::TranslationCache($path);
        }
        elseif (file_exists($path.'.json')) {
            self::$settings = self::TranslationCache($path);
        }
    }
}