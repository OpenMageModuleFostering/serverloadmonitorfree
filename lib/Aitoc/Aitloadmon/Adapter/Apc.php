<?php
/**
 * @copyright  Copyright (c) 2013 AITOC, Inc. 
 * @license License agreement could be found at the root folder of this package
 */
class Aitoc_Aitloadmon_Adapter_Apc
{
    private static $DATA_KEY        = 'aitloadmon_data';
    private static $DATA_NUM_KEY    = 'aitloadmon_data_key';

    /**
     * Saves the data into storage
     *
     * @param array $params
     */
    public function save($params)
    {
        if(!$index = apc_fetch(self::$DATA_NUM_KEY))
        {
            apc_add(self::$DATA_NUM_KEY, 1);
            $index = 1;
        }

        while(!apc_add(self::$DATA_KEY.$index,$params))
        {
            $index++;
        }
        apc_delete(self::$DATA_NUM_KEY);
        apc_add(self::$DATA_NUM_KEY, $index);
    }


    /**
     * Gets the data from the storage
     *
     * @return array
     */
    public function getData()
    {
        $data = array();
        if($index = apc_fetch(self::$DATA_NUM_KEY))
        {
            for($i=1;$i<=$index;$i++)
            {
                if($element = apc_fetch(self::$DATA_KEY.$i))
                {
                    $data = array_merge_recursive($data,$element);
                    apc_delete(self::$DATA_KEY.$i);
                }
            }
            apc_delete(self::$DATA_NUM_KEY);
            apc_add(self::$DATA_NUM_KEY, $i);
        }
        return $data;
    }
}