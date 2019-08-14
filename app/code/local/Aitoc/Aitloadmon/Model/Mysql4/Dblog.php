<?php
/**
 * @copyright  Copyright (c) 2013 AITOC, Inc. 
 * @license License agreement could be found at the root folder of this package
 */
class Aitoc_Aitloadmon_Model_Mysql4_Dblog extends Mage_Core_Model_Mysql4_Abstract
{

    /**
     * Standard model constructor
     */
    public function _construct()
    {
        $this->_init('aitloadmon/dblog', 'url_id');
    }

}