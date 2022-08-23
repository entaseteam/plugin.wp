<?php

/**
 * ATMF Engine. Part of ATMF core.
 * @version: ATMF-PHP Engine 1.1
 * @license: Apache-2.0 License
 * @repository: https://github.com/skito/ATMF-PHP
 */

namespace ATMF {

    class Engine
    {
        public $vars = [];
        public $eVars = [];
        public $redundancyLimit = 32;
        public $allowGlobals = false;

        private $_templates = [];
        private $_cultureFolder = 'culture';
        private $_currentCulture = 'en-US';
        private $_templateDiscoveryPath = 'templates';
        private $_templateDiscoveryExtensions = ['tpl', 'html'];

		private $_tags = [];
        private $_disableParsing = 0;
        private $_openBlocks = 0;
        private $_indexEach = [];
        private $_lastBlockID = 0;

        public static $latestInstance = null;

        /**
         * Constructor
         * @param bool $linkGlobalSelectors Whether to link gloval selector functions __ with this instance
         */
        public function __construct($linkGlobalSelectors=true)
        {
            if ($linkGlobalSelectors) self::$latestInstance = $this;
        }

		/**
		 * Parse ATMF markup
		 * @param string $str ATMF markup (including mustage quotes)
		 * @return string Processed markup
		 */
		public function ParseMarkup($str)
        {
            // ATMF Tags
            $startPos = -1;

            while(($startPos = strpos($str, '{', $startPos + 1)) !== FALSE)
            {

                $endPos = $startPos;

                // Skip escaping with backslash
                if ($startPos > 0 && substr($str, $startPos-1, 2) == '\\{')
                    continue;

                $blockStr = '';
                $blockMatch = false;
                while($endPos = strpos($str, '}', $endPos + 1))
                {
                    $blockStr = substr($str, $startPos, $endPos - $startPos + 1);
                    if (substr_count($blockStr, '{') == substr_count($blockStr, '}'))
                    {
                        $blockMatch = true;
                        break;
                    }
                }

                if (!$blockMatch) die('ATMF Error: Closing curly bracket expected!');
                elseif (strlen($blockStr) < 4) die('ATMF Error: Empty curly brackets detected!');

                // strip off top level brackets
                $blockStr = substr($blockStr, 1, strlen($blockStr) - 2);


				// Parse special functions
                if (strpos($blockStr, '#each') === 0 || strpos($blockStr, '#if') === 0)
                    $this->_openBlocks++;

                if (strpos($blockStr, '#end') === 0)
                {
                    if (isset($this->_indexEach[$this->_openBlocks]))
                    {
                        $this->EnableParsing();
                        unset($this->_indexEach[$this->_openBlocks]);
                    }
                    $this->_openBlocks--;
                }

                // Parse markup
                if ($this->ParsingIsEnabled())
                {
                    $doParseBlock = false;

                    // Good idea for caching tag outputs
                    // However this is causing issues with #if #else functions
                    // Needs additional work
                    // $blockID = base64_encode($blockStr);

                    // No caching - every block, on it's own ID
                    $blockID = $this->CreateBlockID();

                    if (!isset($this->_tags[$blockID]))
                    {
                        $tag = Tag::ParseStr($blockStr);
                        if ($tag != null)
                        {
                            $this->_tags[$blockID] = $tag;
                            $doParseBlock = true;
                        }
                    }
                    else $doParseBlock = true;

                    if ($doParseBlock) {
                        $str = substr($str, 0, $startPos).'<%'.$blockID.'%>'.substr($str, $endPos + 1);
                    }
                }

                // Until the end of the last #each
                if (strpos($blockStr, '#each') === 0)
                {
                    $this->DisableParsing();
                    $this->_indexEach[$this->_openBlocks] = true;
                }
            }

            foreach($this->_tags as $id => $tag)
            {
                if (strpos($str, '<%'.$id.'%>') !== false)
                    $str = str_replace('<%'.$id.'%>', $tag->Build($this), $str);
            }

            // IF blocks, resulted by ATMF operations
            $startPos = -1;
            while($startPos = strpos($str, '<%:block_start%>', $startPos + 1))
            {
                $endPos = $startPos;
                $blockStr = '';
                $blockMatch = false;
                while($endPos = strpos($str, '<%:block_end%>', $endPos + 1))
                {
                    $blockStr = substr($str, $startPos, $endPos - $startPos + 14);
                    if (substr_count($blockStr, '<%:block_start%>') == substr_count($blockStr, '<%:block_end%>'))
                    {
                        $blockMatch = true;
                        break;
                    }

                }

                if (!$blockMatch) die('ATMF Error: Closing function block expected!');

                $str = substr($str, 0, $startPos).$this->ParseBlocks($blockStr).substr($str, $endPos + 14);
                $startPos -= 1;
            }

            return $str;
        }

        /**
         * (For internal use) Parsing of #each, #if, #else blocks
         * @param string $blockStr - As resulted from ParseMarkup operation
         * @return string - Processed markup
         */
        public function ParseBlocks($blockStr)
        {
            if (strpos($blockStr, '<%:block_start%><%:show%>') === 0)
            {
                return substr($blockStr, 25, strlen($blockStr) - 39);
            }
            elseif (strpos($blockStr, '<%:block_start%><%:each%>') === 0)
            {
                $resultStr = '';
                $str = substr($blockStr, 25, strlen($blockStr) - 39);
                $endTagPos = strpos($str, '%>');
                $eachTag = substr($str, 3, $endTagPos - 3);
                $str = substr($str, $endTagPos + 2);
                $eachArgs = explode(':', $eachTag);
                if (count($eachArgs) == 2)
                {
                    $collection = $eachArgs[0];
                    $item = $eachArgs[1];

                    $collectionArr = Variables::Select($this, $collection);
                    if (is_array($collectionArr))
                    {
                        foreach($collectionArr as $row)
                        {
                            $this->eVars[] = [$item => $row];
                            $resultStr .= $this->ParseMarkup($str);

                            if (count($this->eVars) > 0)
                                array_pop($this->eVars);
                        }
                    }
                }
                else $resultStr = '';

                return $resultStr;
            }


            return '';
        }

        /**
         * Global selector
         * @param string $key - ATMF markup
         * @param mixed $val - Value for setting
         * @return mixed Parsed ATMF markup if key only is provided. NONE/VOID if value is provided.
         */
        public function __($key, $val=null)
        {
            $tag = Tag::ParseStr($key);
            if ($tag == null) return;

            if ($val != null) $tag->Set($this, $val);
            else return $tag->Build($this);
        }

        /**
         * Converts mustages in applicable HTML characters
         * @param mixed $str String to escape
         * @return string Escaped string
         */
        public function __escape($str)
        {
            $str = str_replace('{', '&lcub;', $str);
            $str = str_replace('}', '&rcub;', $str);
            return $str;
        }

        private function CreateBlockID() {
            $this->_lastBlockID++;
            return $this->_lastBlockID;
        }

        private function ParsingIsEnabled()
        {
            return $this->_disableParsing == 0;
        }

        private function DisableParsing()
        {
            $this->_disableParsing++;
        }

        private function EnableParsing()
        {
            if ($this->_disableParsing > 0)
                $this->_disableParsing -= 1;
        }

        /**
         * Set culture folder path
         * @param mixed $path Path to the main culture folder
         */
        public function SetCultureFolder($path)
        {
            if ($this->_cultureFolder != $path)
            {
                $this->_cultureFolder = $path;
                Culture::ResetTranslations();
            }
        }

        /**
         * Get the current culture folder
         * @return string Path to the current culture folder
         */
        public function GetCultureFolder()
        {
            return $this->_cultureFolder;
        }

        /**
         * Set current culture
         * @param mixed $culture The culture as described in the culture folder
         */
        public function SetCulture($culture='')
        {
            if ($this->_currentCulture != $culture)
            {
                if (empty($culture) ||
                    !file_exists($this->_cultureFolder) ||
                    !is_dir($this->_cultureFolder))
                    die('ATMF Error: Culture '.$culture.' not found!');

                $this->_currentCulture = $culture;
                Culture::ResetTranslations();
            }
        }

        /**
         * Get current culture
         * @return string Current culture string
         */
        public function GetCulture()
        {
            return $this->_currentCulture;
        }

        /**
         * Get template by name
         * @param string $name Template name
         * @return mixed String if the template exists. FALSE otherwise.
         */
        public function GetTemplate($name)
        {
            if (!empty( $this->_templates[$name]))
                return $this->_templates[$name];
            else {
                if ($this->DiscoverTemplate($name))
                    return $this->_templates[$name];
            }

            return false;
        }


        /**
         * Set template
         * @param mixed $name Name of the template
         * @param mixed $src Template source
         */
        public function SetTemplate($name, $src)
        {
            $this->_templates[$name] = $src;
        }

        /**
         * Set master template
         * @param mixed $src Template source
         */
        public function SetMasterTemplate($src)
        {
            $this->SetTemplate('master', $src);
        }

        /**
         * Set folder to auto discover templates
         * @param mixed $filepath Templates folder repository
         * @param mixed $extensions Extensions to loo for
         */
        public function SetTemplateDiscoveryPath($filepath=null, $extensions=['tpl', 'html'])
        {
            if (!is_array([$extensions]))
                $extensions = [$extensions];

            $this->_templateDiscoveryPath = $filepath;
            $this->_templateDiscoveryExtensions = $extensions;
        }

        public function DiscoverTemplate($name)
        {
            if ($this->_templateDiscoveryPath != null) {
                foreach($this->_templateDiscoveryExtensions as $ext) {
                    $filepath = $this->_templateDiscoveryPath.'/'.$name.'.'.$ext;
                    if (file_exists($filepath)) {
                        $this->_templates[$name] = file_get_contents($filepath);
                        return true;
                    }
                }
            }

            return false;
        }

        /**
         * Render specific template
         * @param string $name Name of the template
         * @param bool $capture Whether to return or write final output
         * @return string Final output
         */
        public function RendTemplate($name, $capture=false)
        {
            return $this->Rend($capture, $name);
        }

        /**
         * Render the final output
         * @param bool $capture Whether to return or write final output
         * @return string Final output
         */
        public function Rend($capture=false, $baseTemplate=null)
        {
            $output = '';
            if ($baseTemplate != null)
            {
                $output = $this->GetTemplate($baseTemplate);
                if (!$output) {
                    die('ATMF Warning: Rend failed! Template "'.$baseTemplate.'" not found!'."\r\n");
                }
            }
            else
            {
                $output = $this->GetTemplate('master');
                if (!$output) $output = $this->GetTemplate('page');
                if (!$output) {
                    die('ATMF Warning: Rend failed! Page and/or master template must be set!'."\r\n");
                }
            }

            $output = ''.$output;
            $redundancy = 0;
            while(
                substr_count($output, '{$') > substr_count($output, '\{$') ||
                substr_count($output, '{@') > substr_count($output, '\{@') ||
                substr_count($output, '{#') > substr_count($output, '\{#') ||
                substr_count($output, '{/') > substr_count($output, '\{/'))
            {
                if ($redundancy > 32)
                    die('ATMF Warning: Template redundancy limit has reached!'."\r\n");

                $output = $this->ParseMarkup($output);
                $redundancy++;
            }

            // Replace escaped tags
            $output = str_replace(['\{$', '\{@', '\{#', '\{/'], ['{$', '{@', '{#', '{/'], $output);

            if ($capture) return $output;
            else echo $output;
        }

        /**
         * Load the ATMF engine dependencies
         * @param mixed $folder Folder path with .PHP dependencies
         */
        public static function LoadDependencies($folder='')
        {
            if (in_array(substr($folder, strlen($folder)-1), ['/', '\\']))
                $folder = substr($folder, 0, strlen($folder)-1);

            if (is_dir($folder))
            {
                foreach(scandir($folder) as $file)
                {
                    if (!in_array($file, ['.','..']))
                    {
                        $path = $folder.DIRECTORY_SEPARATOR.$file;
                        $pathLength = strlen($path);
                        if (is_file($path) &&
                            $pathLength > 4 &&
                            stripos($path, '.php') == $pathLength - 4)
                        {
                            require_once($path);
                        }
                        elseif (is_dir($path))
                        {
                            self::LoadDependencies($path);
                        }
                    }
                }
            }
        }
    }
}

namespace {

    \ATMF\Engine::LoadDependencies(__DIR__.'/core');
    \ATMF\Engine::LoadDependencies(__DIR__.'/ext');

    if (!function_exists('__')) {
        function __($key, $val=null) {
            if (\ATMF\Engine::$latestInstance != null)
                return \ATMF\Engine::$latestInstance->__($key, $val);
            else die('No ATMF instances found.');
        }
    }

    if (!function_exists('__escape')) {
        function __escape($str='') {
            if (\ATMF\Engine::$latestInstance != null)
                return \ATMF\Engine::$latestInstance->__escape($str);
            else die('No ATMF instances found.');
        }
    }
}