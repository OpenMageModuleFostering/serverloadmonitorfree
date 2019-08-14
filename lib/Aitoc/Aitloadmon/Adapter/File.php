<?php
/**
 * @copyright  Copyright (c) 2013 AITOC, Inc. 
 * @license License agreement could be found at the root folder of this package
 */
class Aitoc_Aitloadmon_Adapter_File extends Aitoc_Aitloadmon_Adapter_Abstract
{
    const DS = DIRECTORY_SEPARATOR;

    /**
     * @var string
     */
    private static $_dataDir  = 'data';

    /**
     * @var string
     */
    private static $_filename = 'data_';

    /**
     * @var string
     */
    private static $_storename = 'store';

    /**
     * @var int
     */
    private $_handleId = null;
    
    /**
     * @var string
     */
    private $_dataDirPath = null;
    
    /**
     * Amount of files used to store data. The bigger this value will be - the less is probability that concurrency for the file write permissions between requests will appear
     * 
     * @var int
     */
    private $_concurencyFileCount = 10;

    /**
     * Pointer to the file to which exceded data should be stored
     * @var resource
     */
    private $_storeFile = null;

    /**
     * Contain last id of case store file
     * @var int
     */
    private $_storeId = null;

    /**
     * Var to contain data from cache while it's readed
     * @var array
     */
    private $_data = array();

    /**
     * Total amount of elements in _data array
     * @var int
     */
    private $_totalElements = 0;

    /**
     * @param array $settings
     * 
     * @return Aitoc_Aitloadmon_Adapter_File
     */
    public function __construct( $settings ) 
    {
        if(!file_exists($this->_getDataDir())) {
            mkdir($this->_getDataDir());
        }
        parent::__construct($settings);
    }

    /**
     * Get value from a file
     * 
     * @param string $file file name
     * 
     * @return mixed
     */
    protected function _get($file) 
    {
        $file = $this->_getFilePath($file);
        if(!file_exists($file)) {
            return false;
        }
        return file_get_contents($file);
    }
    
    /**
     * Store value to the $handle file. If file name is given - open this file and put values inside it, then close
     * 
     * @param mixed $handle File pointer resource or a string file name
     * @param mixed $params string or array
     * @param int $ttl
     * 
     * @return bool
     */
    protected function _set($handle, $params, $ttl) 
    {
        $close = false;
        if(!is_resource($handle)) {
            $close = true;
            $handle = $this->_openHandle( $this->_getFilePath($handle) );
        }
        if(is_array($params)) {
            $params = serialize($params);
        }
        $return = fwrite($handle, $params.chr(0x0D).chr(0x0A));
        if($close) {
            $this->_closeHandle($handle);
        }
        return (bool)$return;
    }
    
    /**
     * @param mixed $handle
     * @param mixed $params
     * @param int $ttl
     * 
     * @return bool
     */
    protected function _add($key, $value, $ttl) 
    {
        return $this->_set($key, $value, $ttl);
    }
    
    /**
     * @param string $key
     */
    protected function _inc($key) 
    {
        // not used because getHandle is overrided
    }      

    /**
     * Delete value from storage
     * 
     * @param string $key
     * 
     * @return bool
     */    
    protected function _delete($file) 
    {
        return unlink($file);        
    }

    /**
     * Move data from $source to $target
     * 
     * @param string $source Source file name
     * @param string $target Target file name
     *
     * @return bool
     */
    protected function _move($source, $target)
    {
        return rename($source, $target);
    }
    
    /**
     * Gets the path to the directory for the data files
     * 
     * @return string
     */
    private function _getDataDir()
    {
        if(is_null($this->_dataDirPath)) {
            $this->_dataDirPath = Aitoc_Aitloadmon_Collect::getTempDir().self::$_dataDir.self::DS;
        }
        return $this->_dataDirPath;
    }
    
    /**
     * Get full path to the file
     * 
     * @param string $file
     * 
     * @return string
     */
    protected function _getFilePath( $file ) {
        return $this->_getDataDir() . $file;
    }
    
    /**
     * Get data file name for current pool
     * 
     * @return string
     */
    protected function _getDataFile() 
    {
        return $this->_getDataDir() . self::$_filename . $this->_getCurrentPool(). '_';
    }

    protected function _getCacheStoreFile()
    {
        return $this->_getDataDir() . self::$_storename . '_';
    }
    
    /**
     * Override parent, because file handles are different from APC increments
     * 
     * @return resource
     */
    protected function _getHandle() 
    {
        $file_path = $this->_getDataFile();
        if(is_null($this->_handleId)) {
            $max = 50;
            while($max > 0) {
                $rand = rand(0,$this->_concurencyFileCount - 1);
                $f = fopen($file_path.$rand, 'a');
                if(flock($f, LOCK_EX | LOCK_NB)) {
                    $this->_handleId = $rand;
                    break;
                }
                fclose($f);
                $max--;
            }
            if(!is_resource($f)) {
                $this->_handleId = $this->_concurencyFileCount;
            }
        }
        if(!isset($f) || !is_resource($f)) {
            $f = $this->_openHandle($file_path . $this->_handleId, 'a');
        }
        return $f;
    }
    
    /**
     * Open exclusive connection to the file
     * 
     * @param string $file
     * @param string $mode
     * 
     * @return resource
     */
    protected function _openHandle($file, $mode = 'w') 
    {
        $handle = fopen($file, $mode);
        flock($handle, LOCK_EX); //should wait until lock is aquired        
        return $handle;
    }
    
    /**
     * Close resource connection to the file
     * 
     * @param resource $handle
     */
    protected function _closeHandle($handle) 
    {
        flock($handle, LOCK_UN);
        fclose($handle);
    }

    /**
     * Read links log file and put data into $this->_data array. Returns $flag_store 
     *
     * @param string $file_name
     * @param boolean $flag_store
     * @param boolean $force_full_file
     *
     * @return boolean
     */
    protected function _readLogFile($file_name, $flag_store, $force_full_file = false)
    {
        $fileHandle = $this->_openHandle($file_name, 'r');

        while($string = fgets($fileHandle)) {
            $string = trim($string);
            if($string == '') continue;
            $element = unserialize($string);
            if(isset($this->_data[$element['id']])) {
                $this->_data[$element['id']] = array_merge($this->_data[$element['id']], $element['d']);
            } else {
                if($flag_store && $force_full_file == false) {
                    $this->_storeLog($string);
                    continue;
                }
                $this->_data[$element['id']] = $element['d'];
                $this->_totalElements ++;
                if($this->_totalElements >= $this->getRowsLimit()) {
                    $flag_store = true;
                }
            }
        }
        $this->_closeHandle($fileHandle);
        return $flag_store;
    }

    /**
     * Override parent because files have a little different structure than apc/memcache
     * 
     * @return array
     */
    protected function _getData() 
    {
        $file_mask = $this->_getDataFile();
        $files = glob($file_mask.'*');
        $this->_data = array();
        $this->_totalElements = 0;
        $flag_store = false;
        foreach($files as $file) {
            if($flag_store) {
                $this->_move( $file, $this->getNewStoreFilename() );
                continue;
            }
            $flag_store = $this->_readLogFile($file, $flag_store);
            $this->_delete($file);
        }

        if($this->_totalElements < $this->getRowsLimit() / 3) {
            $this->_getStoreData();
        }
        //moving links to processor and not storing them inside class
        $data = $this->_data;
        $this->_data = array();

        return $data;
    }

    /**
     * Apply data from store cache files if there are any
     */
    protected function _getStoreData()
    {
        $file_mask = $this->_getCacheStoreFile();
        $files = glob($file_mask.'*');
        $flag_store = false;
        foreach($files as $file) {
            //flag to force read complete file
            $force_full_file = false;
            $file_size= filesize($file);
            if($file_size < 200 * 1024) {
                $force_full_file = true;
            }
            $flag_store = $this->_readLogFile($file, $flag_store, $force_full_file);
            $this->_delete($file);
            if($flag_store) {
                break;
            }
        }
    }

    /**
     * Move log line info store log file
     *
     * @param string $logLine
     *
     * @return bool
     */
    protected function _storeLog($logLine)
    {
        return $this->_set($this->getStoreFile(), $logLine, 0);
    }

    /**
     * Close store file handle if it was created
     *
     * @return Aitoc_Aitloadmon_Adapter_File
     */
    protected function closeStoreFile()
    {
        if(is_resource($this->_storeFile)) {
            $this->_closeHandle($this->_storeFile);
        }
        return $this;
    }

    /**
     * @return resource
     */
    public function getStoreFile()
    {
        if(is_null($this->_storeFile)) {
            $this->_storeFile = $this->_openHandle( $this->getNewStoreFilename() );
        }
        return $this->_storeFile;
    }

    /**
     * @return string
     */
    protected function getNewStoreFilename()
    {
        $file_mask = $this->_getCacheStoreFile();
        if(is_null($this->_storeId)) {
            $files = glob($file_mask.'*');
            $this->_storeId = count($files);
        }
        $this->_storeId ++;
        while(file_exists($file_mask . $this->_storeId)) {
            $this->_storeId ++ ;
        }
        return $file_mask . $this->_storeId;
    }
}