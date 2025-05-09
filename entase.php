<?php

/*
Plugin Name: Entase
Description: Sell event tickets directly from your website with Entase integrated API.
Author: Entase, Inc.
Version: 1.7
Author URI: http://www.entase.com
Plugin URI: https://github.com/entaseteam/plugin.wp/
Update URI: https://github.com/entaseteam/plugin.wp/releases/latest/download/package.json
Icon: assets/img/icon128.png
*/

namespace Entase\Plugins\WP;

require_once(dirname(__FILE__).'/autoloader.php');
require_once(dirname(__FILE__).'/conf.php');
require_once(dirname(__FILE__).'/src/Vendor/EntaseSDK/autoloader.php');

Core\ATMF::_();
Hooks\Register::Init(__FILE__);