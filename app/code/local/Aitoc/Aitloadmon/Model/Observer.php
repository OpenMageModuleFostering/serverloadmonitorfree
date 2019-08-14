<?php
/**
 * @copyright  Copyright (c) 2013 AITOC, Inc. 
 * @license License agreement could be found at the root folder of this package
 */
class Aitoc_Aitloadmon_Model_Observer
{
    protected $_systemKey = 'system/aitloadmon/sent_info_date';
    protected $_expire = 604799;

    /**
     * Initiates the data processing job
     */
    public function processData()
    {
        Mage::getModel('aitloadmon/process')->processData();
    }

    /**
     * Initiates the data compressing job
     */
    public function compressData()
    {
        Mage::getModel('aitloadmon/aitloadmon')->getResource()->compress();
    }

     /**
     * Sends the info to Server Calculator by cron
     */
    public function sendToAitoc()
    {
        $date = Mage::getStoreConfig($this->_systemKey);
        $expire = $this->_expire;
        if(!$date)
        {
            Mage::getConfig()
                ->saveConfig($this->_systemKey, time(), 'default', 0)
                ->reinit();  
        }
        elseif((time() - $date) >= $expire)
        {
            Mage::getModel('aitloadmon/calculator')->sendCalcInfo();
            Mage::getConfig()
                ->saveConfig($this->_systemKey, time(), 'default', 0)
                ->reinit();
        }
    }
}