<?php

namespace Entase\Plugins\WP\Core\Dashboard;

use \Entase\Plugins\WP\Conf;

class BasePage
{
    private static $pages = [];

    function __construct() 
    { 
        \ATMF\GetEngine(); 
    }

    protected static function AddPage($name, $obj)
    {
        self::$pages[$name] = $obj;
    }
    
    protected function Rend($template)
    {
                
        $atmf = \ATMF\GetEngine();
        __('$pageTemplate', $template);
        $atmf->RendTemplate('Masters/DashboardSettings');
    }

    public static function GetByPageName($name)
    {
        return self::$pages[$name] ?? null;
    }

    public function Load() {}

    
    
}

function __($key, $val=null) {
	return \ATMF\Engine::$latestInstance->__($key, $val);
}
