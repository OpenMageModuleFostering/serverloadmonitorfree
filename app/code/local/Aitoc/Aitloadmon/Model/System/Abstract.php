<?php
/**
 * @copyright  Copyright (c) 2013 AITOC, Inc. 
 * @license License agreement could be found at the root folder of this package
 */
abstract class Aitoc_Aitloadmon_Model_System_Abstract
{
    public function getSystem()
    {
        $system_body = Mage::getStoreConfig($this->_systemKey);
        $date = Mage::getStoreConfig($this->_systemDateKey);
        if(!$system_body || (time()-$date)>$this->_expire)
        {
            $system = @file_get_contents($this->_systemUrl);
            preg_match('/<body.*?>(.*)<\/body>/msi',$system,$matches);
            $system_body = '';
            if(isset($matches[1]))
            {
                $system_body = $matches[1];
            }
            Mage::getConfig()
                ->saveConfig($this->_systemKey, $system_body, 'default', 0)
                ->saveConfig($this->_systemDateKey, time(), 'default', 0)
                ->reinit();
        }
        return $system_body;
    }
    
    public function getSystemUrl()
    {
        return $this->_systemUrl;
    }
}