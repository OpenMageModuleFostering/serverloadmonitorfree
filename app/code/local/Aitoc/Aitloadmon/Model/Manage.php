<?php
 /**
 * @copyright  Copyright (c) 2013 AITOC, Inc. 
 * @license License agreement could be found at the root folder of this package
 */
class Aitoc_Aitloadmon_Model_Manage extends Mage_Core_Controller_Varien_Front
{
    private $_settingsKey = 'system/aitloadmon/manage';

    /**
     * Gets the setting of the module
     *
     * @return mixed
     */
    public function getSettings()
    {
        return unserialize(Mage::getStoreConfig($this->_settingsKey));
    }

    /**
     * Sets the settings of the module
     *
     * @param $settings
     */
    public function setSettings($settings)
    {
        $config = Mage::getConfig();
        $config->saveConfig($this->_settingsKey, serialize($settings), 'default', 0)->reinit();
    }

}