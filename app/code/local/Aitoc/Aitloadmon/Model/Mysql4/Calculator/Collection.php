<?php
/**
 * @copyright  Copyright (c) 2013 AITOC, Inc. 
 * @license License agreement could be found at the root folder of this package
 */ 
class Aitoc_Aitloadmon_Model_Mysql4_Calculator_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * Standard constructor
     */
    public function _construct()
    {
        $this->_init('aitloadmon/calculator');
    }
}