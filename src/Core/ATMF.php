<?php

namespace Entase\Plugins\WP\Core 
{
    class ATMF 
    {
        public static function _() {}
    }
}

namespace ATMF {
    class Setup
    {
        public static $atmf = null;
        public static function GetEngine()
        {
            if (self::$atmf == null)
            {
                require_once(\Entase\Plugins\WP\Conf::VendorPath.'/ATMF/engine.php');
                self::$atmf = new \ATMF\Engine();
                self::$atmf->SetTemplateDiscoveryPath(\Entase\Plugins\WP\Conf::TemplatesPath);
                
                //self::$atmf->SetCultureFolder(__DIR__.'/culture'); //Default: culture
                //self::$atmf->SetCulture('bg-BG'); // Default: en-US
            }

            return self::$atmf;
        }
    }

    function GetEngine() 
    {
        return Setup::GetEngine();
    }

    function __($key, $val=null)
    {
        return Setup::GetEngine()->__($key, $val);
    }

    function __escape($str='') 
    {
        return Setup::GetEngine()->__escape($str);
    }

    
}