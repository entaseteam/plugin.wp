<?php

namespace Entase\Plugins\WP;

function AutoLoader($className) 
{
    if (strpos($className, 'Entase\\Plugins\\WP\\') !== 0)
        return;

    $calledClass = $className;
    $className = str_replace('Entase\\Plugins\\WP\\', '', $className);

    $fileName = '';
    $namespace = '';

    $includePath = dirname(__FILE__).DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR;
    if (false !== ($lastNsPos = strripos($className, '\\'))) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $fileName = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }
    
    $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
    $fullFilePath = $includePath.DIRECTORY_SEPARATOR.$fileName;

    if (file_exists($fullFilePath)) {
        require_once $fullFilePath;
    }
    else { echo $fullFilePath."\r\n\r\n"; die('Class "'.$calledClass.'" does not exist.'); }
}

\spl_autoload_register('Entase\\Plugins\\WP\\AutoLoader'); // Registers the autoloader