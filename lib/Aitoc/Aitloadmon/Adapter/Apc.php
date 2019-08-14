<?php
/**
 * @copyright  Copyright (c) 2013 AITOC, Inc. 
 * @license License agreement could be found at the root folder of this package
 */
class Aitoc_Aitloadmon_Adapter_Apc extends Aitoc_Aitloadmon_Adapter_Abstract
{
    /**
     * Generate special module prefixes where data will be stored
     * 
     * @param array $settings
     * 
     * @return Aitoc_Aitloadmon_Adapter_Apc
     */
    public function __construct( $settings ) 
    {
        if(!function_exists('apc_cache_info')) {
            throw new Exception('APC is not installed on your system and can not be used as adapter for Server Load Monitor');
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
        return apc_fetch($key);
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
        return apc_store($key, $value, $ttl);
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
        return apc_add($key, $value, $ttl);
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
        return apc_inc($key);        
    }

    /**
     * Delete value from storage
     * 
     * @param string $key
     * 
     * @return mixed
     */    
    protected function _delete($key) {
        return apc_delete($key);        
    }
    
}