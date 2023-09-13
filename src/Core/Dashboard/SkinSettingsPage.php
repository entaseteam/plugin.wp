<?php

namespace Entase\Plugins\WP\Core\Dashboard;

use \Entase\Plugins\WP\Conf;
use \Entase\Plugins\WP\Utilities\Ajax;
use \Entase\Plugins\WP\Core\SkinSettings;


class SkinSettingsPage extends BasePage
{
    function __construct() 
    {
        parent::__construct();        
    }

    public function Load()
    {
        $skins = SkinSettings::Get('skins');
        if (is_array($skins) && count($skins) > 0)
            __('$skins', $skins);

        self::Rend('Pages/SkinSettings');
    }

    public static function Update()
    {
        $_POST = array_map('stripslashes_deep', $_POST);

        $newSkin = [
            'id' => $_POST['id'] ?? '',
            'name' => $_POST['name'] ?? '',
            'widget' => $_POST['widget'] ?? '',
            'template' => $_POST['template'] ?? []
        ];

        $skins = [];
        $hasUpdate = false;
        $oldSkins = SkinSettings::Get('skins');
        foreach ($oldSkins as $skin)
        {
            if ($skin['id'] == $newSkin['id'])
            {
                $hasUpdate = true;
                $skin = $newSkin;
            }
            
            $skins[] = $skin;
        }

        if (!$hasUpdate)
            array_unshift($skins, $newSkin);
        
        SkinSettings::Set('skins', $skins, false);
        
        if (SkinSettings::Write()) Ajax::StatusOK();
        else Ajax::StatusERR('Settings update failed.');
    }

    public static function Delete()
    {
        $skinID = $_POST['id'] ?? '';
        $skins = [];
        $oldSkins = SkinSettings::Get('skins');
        foreach ($oldSkins as $skin)
        {
            if ($skin['id'] != $skinID)
            {
                $skins[] = $skin;
            }            
        }

        SkinSettings::Set('skins', $skins, false);
        
        if (SkinSettings::Write()) Ajax::StatusOK();
        else Ajax::StatusERR('Settings update failed.');
    }

    public static function Register()
    {
        self::AddPage('skins', new SkinSettingsPage());
    }
}
