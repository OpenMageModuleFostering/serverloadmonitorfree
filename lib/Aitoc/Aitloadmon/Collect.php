<?php
 
class Aitoc_Aitloadmon_Collect
{
    const DS = DIRECTORY_SEPARATOR;

    private static $_tempDir              = 'ait_loadmon';
    private static $_settingsFilename     = 'settings.ini';
    private static $_loadLevelFilename    = 'load_level';

    private static $_adapter;
    private static $_enabled;
    private static $_loadLevel;
    private static $_pathAddon;
    private static $_timeOffset = 0;
    private static $_cacheOffsetTime = 3600;
    private static $_allowLog = true;
    private $_uniqueId;
    private $_startWritten;

    /**
     * Gets the root path of Magento installation
     *
     * @return string
     */
    private static function _getRootPath()
    {//todo think of it
        return dirname($_SERVER['SCRIPT_FILENAME']).self::$_pathAddon;
    }

    /**
     * Returns whether the module is enabled
     *
     * @return bool
     */
    public static function isModuleEnabled()
    {
        if(!isset(self::$_enabled))
        {
            self::$_enabled = false;
            $config  = simplexml_load_file(self::_getRootPath().self::DS.'app'.self::DS.'etc'.self::DS.'modules'.self::DS.'Aitoc_Aitloadmon.xml');
            if ($config){
                self::$_enabled = ('true' == (string)$config->modules->Aitoc_Aitloadmon->active);
            }
        }
        return self::$_enabled;
    }

    public static function isAllowedToSave()
    {
        return self::$_allowLog;
    }

    /**
     * Checks if module is enabled and if it is writes the start data
     */
    public function __construct($pathAddon = '')
    {
        self::$_pathAddon = $pathAddon;
        if (!self::isModuleEnabled())
        {
            return;
        }
        $this->_startWritten = 0;
        $this->_save();
        register_shutdown_function(array($this,'shutdown'));
    }

    /**
     * Gets the unique id for storing purposes
     *
     * @return string
     */
    private function _getUniqueId()
    {
        if(!isset($this->_uniqueId))
        {
            $this->_uniqueId = uniqid();
        }
        return $this->_uniqueId;
    }

    /**
     * Gets the VAR folder of Magento
     *
     * @return string
     */
    public static function getTempDir()
    {
        return self::_getRootPath().self::DS.'var'.self::DS.self::$_tempDir.self::DS;
    }

    /**
     * Gets the full path of the settings file
     *
     * @return string
     */
    private static function _getSettingsFilename()
    {
        return self::getTempDir().self::$_settingsFilename;
    }

    /**
     * Saves the data
     */
    private function _save()
    {
        if(!self::isAllowedToSave()) {
            return false;
        }
        if(!$this->_startWritten)
        {
            $saveData = array(
                'id' => $this->_getUniqueId(),
                'd'  => array(
                    'request_uri'    => $_SERVER['REQUEST_URI'],
                    'start'          => microtime(true),
                )
            );
        }
        else
        {
            $saveData = array(
                'id' => $this->_getUniqueId(),
                'd'  => array(
                    'end'          => microtime(true),
                )
            );
        }
        self::_getAdapter()->save($saveData);
        $this->_startWritten = 1;
    }

    /**
     * Checks if module is enabled and if it is writes the finish data
     */
    public function shutdown()/*__destruct()*/
    {
        if (!self::isModuleEnabled())
        {
            return;
        }
        $this->_save();
    }

    /**
     * Gets the data storage adapter
     *
     * @return Aitoc_Aitloadmon_Adapter_Memcached|Aitoc_Aitloadmon_Adapter_File|Aitoc_Aitloadmon_Adapter_Apc
     * @throws Exception
     */
    private static function _getAdapter()
    {
        if(!isset(self::$_adapter))
        {
            $settings = self::_getSettings();
            if(!isset($settings['cron_date'])) {
                $settings['cron_date'] = time();
                self::_saveConfig($settings);
            }
            if($settings['cron_date'] + self::$_cacheOffsetTime < time()) {
                self::$_allowLog = false;
            }

            if(!isset($settings['adapter']))
            {
                throw new Exception('No adapter specified in settings');
            }
            $abstractClassFile = self::_getRootPath().self::DS.'lib'.self::DS.'Aitoc'.self::DS.'Aitloadmon'.self::DS.'Adapter'.self::DS.'Abstract.php';
            if(!file_exists($abstractClassFile))
            {
                throw new Exception('Abstract does not exists: Aitoc_Aitloadmon_Adapter_Abstract');
            }

            include_once($abstractClassFile);

            $adapterName = ucfirst(strtolower($settings['adapter']));
            $adapterClassFile = self::_getRootPath().self::DS.'lib'.self::DS.'Aitoc'.self::DS.'Aitloadmon'.self::DS.'Adapter'.self::DS.$adapterName.'.php';

            if(!file_exists($adapterClassFile))
            {
                throw new Exception('Adapter does not exists');
            }

            include_once($adapterClassFile);

            $adapterClass = 'Aitoc_Aitloadmon_Adapter_'.$adapterName;
            self::$_adapter = new $adapterClass($settings);
        }
        return self::$_adapter;
    }

    /**
     * Save array to config ini file
     *
     * @param array $settings 
     * 
     * @return bool
     */
    private static function _saveConfig($settings)
    {
        $settingsString = '';
        foreach($settings as $key => $value) {
            $settingsString .= $key.' = "'.$value."\"\r\n";
        }
        if(!file_exists(self::getTempDir()))
        {
            mkdir(self::getTempDir());
        }
        return (bool)file_put_contents(self::_getSettingsFilename(),$settingsString);
    }

    /**
     * Gets the settings array
     *
     * @return array
     */
    private static function _getSettings()
    {
        if(!file_exists(self::_getSettingsFilename()))
        {

            $config  = simplexml_load_file(self::_getRootPath().self::DS.'app'.self::DS.'etc'.self::DS.'local.xml');
            $cacheEngine = null;
            if ($config){
                $cacheEngine = (string)$config->global->cache->backend;
            }
            $settings = array();
            switch($cacheEngine)
            {
                case 'memcached':
                    $settings['adapter']    = 'memcached';
                    $settings['host']       = (string)$config->global->cache->memcached->servers->server->host;
                    $settings['port']       = (string)$config->global->cache->memcached->servers->server->port;
                    break;

                case 'apc':
                    $settings['adapter']    = 'apc';
                    $settings['host']       = (string)$config->global->cache->prefix;
                    break;

                default:
                    $settings['adapter']    = 'file';

            }
            $settings['cron_date'] = time(); //first clean run of this script
            self::_saveConfig($settings);
        }
        
        $settings = parse_ini_file(self::_getSettingsFilename());
        
        return $settings;
    }

    /**
     * Gets the data that was collected and stored
     *
     * @return array
     */
    public static function getData()
    {
        $adapter = self::_getAdapter();
        $data = $adapter->getData();
        return $data;
    }

    /**
     * Set up time when cron was started last time in config.ini file
     * 
     * @return bool
     */
    public static function saveCronStartedFlag()
    {
        $settings = self::_getSettings();
        $settings['cron_date'] = time();
        return self::_saveConfig($settings);
    }

    /**
     * Gets the full path of a file with current load level
     *
     * @return string
     */
    private static function _getLoadLevelFilename()
    {
        return self::getTempDir().self::$_loadLevelFilename;
    }

    /**
     * Sets current load level
     *
     * @param int $loadLevel
     */
    public static function setLoadLevel($loadLevel)
    {
        self::$_loadLevel = $loadLevel;
        $filename = self::_getLoadLevelFilename();
        file_put_contents($filename,$loadLevel);

    }

    /**
     * Gets current load level
     *
     * @return null|int
     */
    public static function getLoadLevel()
    {
        if(!isset(self::$_loadLevel))
        {
            $filename = self::_getLoadLevelFilename();
            if(file_exists($filename))
            {
                self::$_loadLevel = file_get_contents($filename);
            }
            else
            {
                self::$_loadLevel = null;
            }
        }
        return self::$_loadLevel;
    }
    
    /**
     * Gets time offset
     *
     * @return int
     */    
    public static function getTimeOffset()
    {
        return self::$_timeOffset;
    }
    
    /**
     * Sets time offset
     *
     */    
    public static function setTimeOffset($offset)
    {
        self::$_timeOffset = $offset;
    }

    /**
     * Adds time offset
     *
     */        
    public static function addTimeOffset($offset)
    {
        self::$_timeOffset += $offset;
    }
    
}