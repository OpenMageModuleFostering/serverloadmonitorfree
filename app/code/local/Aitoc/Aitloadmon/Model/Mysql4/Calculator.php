<?php
 /**
 * @copyright  Copyright (c) 2013 AITOC, Inc. 
 * @license License agreement could be found at the root folder of this package
 */
class Aitoc_Aitloadmon_Model_Mysql4_Calculator extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Standard model constructor
     */
    public function _construct()
    {   
        $this->_init('aitloadmon/calculator', 'id');
    }

    /**
     * Deletes everything from the table
     */
    public function deleteAll()
    {
        $adapter = $this->_getWriteAdapter();
        $sql = 'DELETE FROM '.$this->getTable('aitloadmon/calculator');
        $adapter->query($sql);
    }
}