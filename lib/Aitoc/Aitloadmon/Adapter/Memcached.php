<?php
/**
 * @copyright  Copyright (c) 2013 AITOC, Inc. 
 * @license License agreement could be found at the root folder of this package
 */
class Aitoc_Aitloadmon_Adapter_Memcached extends Aitoc_Aitloadmon_Adapter_Abstract
{
    private $_cache;
    
    /**
     * Initiates the connection to memcache and generate special module prefixes where data will be stored
     *
     * @param array $settings
     */
    public function __construct( $settings ) 
    {
        if(!class_exists('Memcache', false)) {
            throw new Exception('Memcache is not installed on your system and can not be used as adapter for Server Load Monitor');
        }
        $this->_cache = new Memcache;
        $result = $this->_cache->connect($settings['host'], $settings['port']);
        if(!$result) {
            throw new Exception('Memcache connections s not initialized. Please check your settings');
        }
        parent::__construct($settings);
        $this->_storeParams = true;
        $this->_dataPrefix = $this->_getPrefix() . Aitoc_Aitloadmon_Adapter_Abstract::$DATA_KEY;
    }

    /**
     * Get value from a storage by $key
     * 
     * @param string $key
     * 
     * @return mixed
     */
    protected function _get($key) 
    {
        return $this->_cache->get($key);        
    }
    
    /**
     * Store value to the storage by $key, replace old values
     * 
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     * 
     * @return bool
     */
    protected function _set($key, $value, $ttl) 
    {
        return $this->_cache->set($key, $value, false, $ttl);
    }
    
    /**
     * Add value to the storage by key, return false if value exists
     * 
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     * 
     * @return bool
     */
    protected function _add($key, $value, $ttl) 
    {
        return $this->_cache->add($key, $value, false, $ttl);
    }
    
    /**
     * Get valuee from the storage and increase it's count by 1
     * 
     * @param string $key
     * 
     * @return int
     */
    protected function _inc($key) 
    {
        return $this->_cache->increment($key);        
    }
    
    /**
     * Delete value from storage
     * 
     * @param string $key
     * 
     * @return mixed
     */    
    protected function _delete($key) 
    {
        return $this->_cache->delete($key);        
    }
    
}