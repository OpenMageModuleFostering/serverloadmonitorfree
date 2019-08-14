<?php
/**
 * @copyright  Copyright (c) 2013 AITOC, Inc. 
 * @license License agreement could be found at the root folder of this package
 */
class Aitoc_Aitloadmon_Model_LevelSource
{

    /**
     * Returns load levels as a simple array
     *
     * @return array
     */
    public function getLoadArray()
    {
        return array(
            Aitoc_Aitloadmon_Model_Aitloadmon::LOAD_LEVEL_GREEN     =>  'green',
            Aitoc_Aitloadmon_Model_Aitloadmon::LOAD_LEVEL_YELLOW    =>  'yellow',
            Aitoc_Aitloadmon_Model_Aitloadmon::LOAD_LEVEL_RED       =>  'red',
            Aitoc_Aitloadmon_Model_Aitloadmon::LOAD_LEVEL_BLACK     =>  'black'
        );
    }


    /**
     * Returns load levels as an array for settings
     *
     * @return array
     */
    public function toOptionArray()
    {
        $array = array(
            array('value' => 0, 'label'=>Mage::helper('aitloadmon')->__('No level')),
        );

        $levels = $this->getLoadArray();

        foreach($levels as $key=>$value)
        {
            $array[] = array('value' => $key, 'label'=>Mage::helper('aitloadmon')->__(ucfirst($value)));
        }

        return $array;
    }

    /**
     * Returns load levels as an array for settings
     *
     * @return array
     */
    public function toArray()
    {
        $array = array(
            0 => Mage::helper('aitloadmon')->__('No level'),
        );

        $levels = $this->getLoadArray();

        foreach($levels as $key=>$value)
        {
            $array[$key] = Mage::helper('aitloadmon')->__(ucfirst($value));
        }

        return $array;
    }
}