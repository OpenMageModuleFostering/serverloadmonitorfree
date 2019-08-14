<?php
/**
 * @copyright  Copyright (c) 2013 AITOC, Inc. 
 * @license License agreement could be found at the root folder of this package
 */
class Aitoc_Aitloadmon_Model_CompressSource
{

    /**
     * Returns compress types as a simple array
     *
     * @return array
     */
    public function getCompressTypesArray()
    {
        return array(
            1 => Mage::helper('aitloadmon')->__('hour'),
            2 => Mage::helper('aitloadmon')->__('day'),
            3 => Mage::helper('aitloadmon')->__('month'),
        );
    }


    /**
     * Returns compress types as an array for settings
     *
     * @return array
     */
    public function toOptionArray()
    {
        $levels = $this->getCompressTypesArray();

        foreach($levels as $key=>$value)
        {
            $array[] = array('value' => $key, 'label'=>Mage::helper('aitloadmon')->__(ucfirst($value)));
        }

        return $array;
    }

    /**
     * Returns compress types as an array for settings
     *
     * @return array
     */
    public function toArray()
    {
        $levels = $this->getCompressTypesArray();

        foreach($levels as $key=>$value)
        {
            $array[$key] = Mage::helper('aitloadmon')->__(ucfirst($value));
        }
        return $array;
    }
}