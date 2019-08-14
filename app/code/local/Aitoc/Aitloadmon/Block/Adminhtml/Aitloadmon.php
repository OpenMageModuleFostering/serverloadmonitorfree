<?php
 /**
 * @copyright  Copyright (c) 2013 AITOC, Inc. 
 * @license License agreement could be found at the root folder of this package
 */
class Aitoc_Aitloadmon_Block_Adminhtml_Aitloadmon extends Mage_Adminhtml_Block_Template
{

    protected $_collection;
    protected $_compareCollection;

    /**
     * Returns a collection of data for main graphics
     *
     * @return Aitoc_Aitloadmon_Model_Mysql4_Aitloadmon_Collection
     */
    public function getCollection()
    {
        if(!isset($this->_collection))
        {
            $this->_collection = Mage::getModel('aitloadmon/aitloadmon')->getCollectionByDates($this->getData('start_date'),$this->getData('end_date'));
        }
        return $this->_collection;
    }

    /**
     * Returns a collection of data for compare graphics
     *
     * @return Aitoc_Aitloadmon_Model_Mysql4_Aitloadmon_Collection
     */
    public function getCompareCollection()
    {
        if(!isset($this->_compareCollection))
        {
            $this->_compareCollection = Mage::getModel('aitloadmon/aitloadmon')->getCollectionByDates($this->getData('start_date_compare'),$this->getData('end_date_compare'));
        }
        return $this->_compareCollection;
    }

    /**
     * Checks if there is enough data in collection to build graphics (more then 2 dots)
     *
     * @param Aitoc_Aitloadmon_Model_Mysql4_Aitloadmon_Collection $collection
     * @return bool
     */
    public function isEnoughDataToBuildGraph($collection)
    {
        $data = array();
        foreach($collection as $item)
        {
            $data[$item->getMeasureTime()] = 1;
            if(count($data)>2)
            {
                return true;
            }
        }
        return false;
    }

}