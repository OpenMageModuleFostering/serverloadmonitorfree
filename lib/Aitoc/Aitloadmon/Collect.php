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
                self::$_enabled = (string)$config->modules->Aitoc_Aitloadmon->active;
            }
        }
        return self::$_enabled;
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
        if(!$this->_startWritten)
        {
            $saveData = array(
                $this->_getUniqueId() => array(
                    'request_uri'    => $_SERVER['REQUEST_URI'],
                    'start'          => microtime(true),
                )
            );
        }
        else
        {
            $microtime = microtime(true) + self::$_timeOffset;
            $saveData = array(
                $this->_getUniqueId() => array(
                    'end'          => $microtime,
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

            if(!isset($settings['adapter']))
            {
                throw new Exception('No adapter specified in settings');
            }

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
            switch($cacheEngine)
            {
                case 'memcached':
                    $settingsString = 'adapter = "memcached"'."\r\n".'host = "'.(string)$config->global->cache->memcached->servers->server->host.'"'."\r\n".'port = "'.(string)$config->global->cache->memcached->servers->server->port.'"';
                    break;

                case 'apc':
                    $settingsString = 'adapter = "apc"';
                    break;

                default:
                    $settingsString = 'adapter = "file"';

            }
            if(!file_exists(self::getTempDir()))
            {
                mkdir(self::getTempDir());
            }
            file_put_contents(self::_getSettingsFilename(),$settingsString);

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