<?php

namespace Entase\Plugins\WP\Hooks;

use \Entase\Plugins\WP\Core\SettingsMenu;
use \Entase\Plugins\WP\Core\Dashboard\SkinSettingsPage;
use \Entase\Plugins\WP\Core\Productions;
use \Entase\Plugins\WP\Core\Events;

class Ajax 
{
    public static function Import()
    {
        if ($_POST['role'] == 'productions')
        {
            $procedure = $_POST['procedure'] ?? '';
            $fromID = $_POST['fromID'] ?? null;
            
            if ($procedure == 'sync') Productions::Sync(false, $fromID);
            else Productions::Import();
        }
        elseif ($_POST['role'] == 'events')
            Events::Import();
    }

    public static function Settings()
    {
        SettingsMenu::Save();
    }

    public static function UpdateSkin()
    {
        SkinSettingsPage::Update();
    }

    public static function DeleteSkin()
    {
        SkinSettingsPage::Delete();
    }
}
