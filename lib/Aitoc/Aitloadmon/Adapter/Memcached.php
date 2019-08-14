<?php
/**
 * @copyright  Copyright (c) 2013 AITOC, Inc. 
 * @license License agreement could be found at the root folder of this package
 */
class Aitoc_Aitloadmon_Adapter_Memcached
{
    private static $DATA_KEY = 'aitloadmon_data';
    private static $DATA_NUM_KEY = 'aitloadmon_data_key';

    private $_cache;

    /**
     * Initiates the connection to memcache
     *
     * @param array $settings
     */
    public function __construct($settings)
    {
        $this->_cache = new Memcache;
        $this->_cache->connect($settings['host'], $settings['port']);
    }

    /**
     * Saves the data into storage
     *
     * @param array $params
     */
    public function save($params)
    {
        if(!$index = $this->_cache->get(self::$DATA_NUM_KEY))
        {
            $this->_cache->set(self::$DATA_NUM_KEY, 1);
            $index = 1;
        }

        while(!$this->_cache->add(self::$DATA_KEY.$index,$params))
        {
            $index++;
        }
        $this->_cache->set(self::$DATA_NUM_KEY, $index);
    }

    /**
     * Gets the data from the storage
     *
     * @return array
     */
    public function getData()
    {
        $data = array();
        if($index = $this->_cache->get(self::$DATA_NUM_KEY))
        {
            for($i=1;$i<=$index;$i++)
            {
                if($element = $this->_cache->get(self::$DATA_KEY.$i))
                {
                    $data = array_merge_recursive($data,$element);
                    $this->_cache->delete(self::$DATA_KEY.$i);
                }
            }
            $this->_cache->set(self::$DATA_NUM_KEY, 1);
        }
        return $data;
    }
}