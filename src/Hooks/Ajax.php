<?php

namespace Entase\Plugins\WP\Hooks;

use \Entase\Plugins\WP\Core\Productions;

class Ajax 
{
    public static function Import()
    {
        if ($_POST['role'] == 'productions')
            Productions::Import();
        /*elseif ($_POST['role'] == 'productions')
            Events::Import();*/
    }
}
