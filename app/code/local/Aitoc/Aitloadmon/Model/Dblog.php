<?php
/**
 * @copyright  Copyright (c) 2013 AITOC, Inc. 
 * @license License agreement could be found at the root folder of this package
 */
class Aitoc_Aitloadmon_Model_Dblog extends Mage_Core_Model_Abstract
{
    /**
     * Standard model constructor
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('aitloadmon/dblog');
    }
}