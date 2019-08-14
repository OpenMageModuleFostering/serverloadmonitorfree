<?php
 /**
 * @copyright  Copyright (c) 2013 AITOC, Inc. 
 * @license License agreement could be found at the root folder of this package
 */
class Aitoc_Aitloadmon_Model_Aitloadmon extends Mage_Core_Model_Abstract
{
    const LOAD_LEVEL_GREEN  = 1;
    const LOAD_LEVEL_YELLOW = 2;
    const LOAD_LEVEL_RED    = 3;
    const LOAD_LEVEL_BLACK  = 4;

    /**
     * Standard model constructor
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('aitloadmon/aitloadmon');
    }

    /**
     * Gets a data collection bounded by 2 dates
     *
     * @param $startDate
     * @param $endDate
     * @return Aitoc_Aitloadmon_Model_Mysql4_Aitloadmon_Collection
     */
    public function getCollectionByDates($startDate, $endDate)
    {

        $dateModel = Mage::getModel('core/date');
        return $this->getCollection()->filterByDates($dateModel->gmtDate('Y-m-d H:i:s', $startDate), $dateModel->gmtDate('Y-m-d H:i:s', $endDate));
    }

}