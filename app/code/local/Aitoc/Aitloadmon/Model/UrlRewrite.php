<?php
/**
 * @copyright  Copyright (c) 2013 AITOC, Inc. 
 * @license License agreement could be found at the root folder of this package
 */
class Aitoc_Aitloadmon_Model_UrlRewrite extends Mage_Core_Model_Url_Rewrite
{
    /**
     * Standard model constructor
     */
    protected function _construct()
    {
        $this->_init('aitloadmon/urlRewrite');
    }
    
    public function loadByRequestPath($path)
    {
        $this->setId(null);
        $this->_getResource()->loadByRequestPath($this, $path);
        $this->_afterLoad();
        $this->setOrigData();
        $this->_hasDataChanges = false;
        return $this;
    }    
}