<?php
/**
 * @copyright  Copyright (c) 2013 AITOC, Inc. 
 * @license License agreement could be found at the root folder of this package
 */
abstract class Aitoc_Aitloadmon_Adapter_Abstract
{
    protected static $DATA_KEY        = 'aitlm_d';
    protected static $DATA_NUM_KEY    = 'aitlm_d_k';
    protected static $CURR_POOL_INDEX = 'aitlm_p_i';
    protected static $TTL             = 7200;
    
    //prefix from settings
    /**
     * @var string
     */
    protected $_prefix      = '';
    
    /**
     * @var array
     */
    protected $_pools       = array('a','b');
    
    /**
     * @var string
     */
    protected $_currentPool = '';
    
    /**
     * @var string
     */
    protected $_poolIndex   = '';
    
    /**
     * Flag to show that some data were stored in current request, used by some adapter to update data
     * 
     * @var bool
     */
    protected $_dataStored  = false;

    /**
     * Amount of rows that should be read from cache
     * @var int
     */
    protected $_rowsLimit = 50000;
    
    /**
     * @var string
     */
    protected $_dataPrefix  = '';
     
    /**
     * Key to store value in apc/memcache
     * 
     * @var string
     */
    protected $_handle     = null;

    /**
     * Variable to store data from first insert to apc
     * @var array
     */
    protected $_storedParams = array();
    
    /**
     * Flag, that we need to save previous data stored cache and we need to update it in next request to save, instead of creating 2 different rows
     * 
     * @var bool
     */
    protected $_storeParams = false;
    

    /**
     * Get value from a storage by $key
     * 
     * @param string $key
     * 
     * @return mixed
     */
    abstract protected function _get($key);
    
    /**
     * Store value to the storage by $key, replace old values
     * 
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     * 
     * @return bool
     */
    abstract protected function _set($key, $value, $ttl);
    
    /**
     * Add value to the storage by key, return false if value exists
     * 
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     * 
     * @return bool
     */
    abstract protected function _add($key, $value, $ttl);
    
    /**
     * Get valuee from the storage and increase it's count by 1
     * 
     * @param string $key
     * 
     * @return int
     */
    abstract protected function _inc($key);
    
    /**
     * Delete value from storage
     * 
     * @param string $key
     * 
     * @return mixed
     */
    abstract protected function _delete($key);
    
    /**
     * Initiate pools, generate prefixes for data
     *
     * @param array $settings
     */
    public function __construct($settings)
    {
        $this->_prefix = (isset($settings['prefix']) ? (string)$settings['prefix'] : '');

        $pools = $this->_pools;
            
        $poolIndex = $this->getPoolIndex();
        if($poolIndex === false)
        {
            $poolIndex = 0;
            $this->_storePoolId( $poolIndex );
        }
        $poolIndex = (int)$poolIndex;

        $this->_currentPool = $pools[$poolIndex];
        $this->_poolIndex    = $poolIndex;
    }
    
    /**
     * @return int
     */
    public function getPoolIndex() 
    {
        return $this->_get($this->getPoolIndexId());
    }
    
    /**
     * Store current pool
     * 
     * @param int $newPoolId
     */
    protected function _storePoolId( $newPoolId ) 
    {
        $this->_set($this->getPoolIndexId(), $newPoolId, Aitoc_Aitloadmon_Adapter_Abstract::$TTL);        
    }

    /**
     * Get unique index/handler where data will be stored
     * 
     * @return string
     */
    protected function _getHandle() 
    {
        if(is_null($this->_handle)) {
            $dataIndex = $this->_getPrefix() . Aitoc_Aitloadmon_Adapter_Abstract::$DATA_NUM_KEY;
            if(!$index = $this->_get($dataIndex))
            {
                $this->_add($dataIndex, 1, Aitoc_Aitloadmon_Adapter_Abstract::$TTL);
                $index = 1;
            }
            else
            {
                $index = $this->_inc($dataIndex);
            }

            $this->_handle = $this->_dataPrefix . $index;
        }
        return $this->_handle;
    }

    /**
     * Remove connection to the handle, if required
     * 
     * @param mixed $handle
     */
    protected function _closeHandle($handle) 
    {
        //nothing by default
    }
    
    /**
     * Process data, receive handle where to save it and execute save
     *
     * @param array $params
     */
    public function save($params) 
    {
        if($this->_storeParams) {
            $params = $this->_processParams($params);
        }
        $handler = $this->_getHandle();
        $result = $this->_save($handler, $params);
        $this->_dataStored = $this->_dataStored || $result;
        $this->_closeHandle($handler);
    }

    /**
     * Save data to storage, can be overriden, if required
     * 
     * @param mixed $handler
     * @param array $params
     * 
     * @return bool
     */
    protected function _save( $handler, $params ) 
    {
        if(!$this->isDataStored()) {
            $return = $this->_add($handler, $params, Aitoc_Aitloadmon_Adapter_Abstract::$TTL);
        } else {
            $return = $this->_set($handler, $params, Aitoc_Aitloadmon_Adapter_Abstract::$TTL);
        }
        return $return;
    }
    
    /**
     * Update pools and gets the data from the storage
     * 
     * @return array
     */
    public function getData()
    {
        $this->_cyclePools();
        $data = $this->_getData();        
        return $data;
    }
    
    /**
     * Extract data from the storage
     * @return array
     */    
    protected function _getData()
    {
        $data = array();
        $dataIndex = $this->_getPrefix() . Aitoc_Aitloadmon_Adapter_Abstract::$DATA_NUM_KEY;
        if($index = $this->_get($dataIndex))
        {
            $this->_set($dataIndex, 1, Aitoc_Aitloadmon_Adapter_Abstract::$TTL);
            for($i=1;$i<=$index;$i++)
            {
                if($element = $this->_get($this->_dataPrefix . $i))
                {
                    $data[$element['id']] = $element['d'];
                    $this->_delete($this->_dataPrefix . $i);
                }
            }

        }
        return $data;
    }
    
    /**
     * @return string
     */
    public function getPoolIndexId() 
    {
        return $this->_prefix.self::$CURR_POOL_INDEX;
    }
    
    /**
     * @return int
     */
    protected function _getNextPoolIndex()
    {
        if((count($this->_pools)-1) == $this->_poolIndex)
        {
            return 0;
        }
        else
        {
            return ($this->_poolIndex+1);
        }
    }

    protected function _cyclePools()
    {
        $this->_storePoolId( $this->_getNextPoolIndex() );
    }
    
    /**
     * @return string
     */
    protected function _getPrefix()
    {
        return $this->_prefix.$this->_getCurrentPool();
    }
    
    /**
     * @return string
     */
    protected function _getCurrentPool()
    {
       return $this->_currentPool;
    }
    
    /**
     * Merge previously saved params with current one.
     * 
     * @param array $params
     * 
     * @return array
     */
    protected function _processParams($params) 
    {
        $this->_storedParams = array_merge($this->_storedParams, $params['d']);
        $params['d'] = $this->_storedParams;
        return $params;
    }
    
    /**
     * @return bool
     */
    public function isDataStored() 
    {
        return $this->_dataStored;
    }

    /**
     * @return int
     */
    public function getRowsLimit()
    {
        return $this->_rowsLimit;
    }
    
}
