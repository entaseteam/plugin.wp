<?php

namespace Entase\SDK;

function AutoLoader($className) 
{
    if (strpos($className, 'Entase\\SDK\\') !== 0)
        return;

    $calledClass = $className;
    $className = str_replace('Entase\\SDK\\', '', $className);

    $fileName = '';
    $namespace = '';

    $includePath = dirname(__FILE__).DIRECTORY_SEPARATOR;
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
    else die('Class "'.$calledClass.'" does not exist.');
}

\spl_autoload_register('Entase\\SDK\\AutoLoader'); // Registers the autoloader