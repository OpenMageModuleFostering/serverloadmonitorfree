<?php
 /**
 * @copyright  Copyright (c) 2013 AITOC, Inc. 
 * @license License agreement could be found at the root folder of this package
 */
class Aitoc_Aitloadmon_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Returns an array of page groups we are dividing the page views
     *
     * @return array
     */
    public function getGroupsArray()
    {
        return array(
            'other'         => 1,
            'cms'           => 2,
            'checkout'      => 3,
            'catalogsearch' => 4,
            'catalog'       => 5,
        );
    }

    /**
     * Gets current load level
     *
     * @return int
     */
    public function getLoadLevel()
    {
        return Aitoc_Aitloadmon_Collect::getLoadLevel();
    }


    /**
     * Gets current page group
     *
     * @return int|bool
     */
    public function getGroupIdOfCurrentPage()
    {
        $module = Mage::app()->getRequest()->getModuleName();
        $groupIds = Mage::helper('aitloadmon')->getGroupsArray();
        if($module == 'admin')
        {
            return false;
        }
        elseif(isset($groupIds[$module]))
        {
            return $groupIds[$module];
        }
        else
        {
            return 1;
        }
    }

    /**
     * Sets current load level
     * @param int $loadLevel
     */
    public function setLoadLevel($loadLevel)
    {
        Aitoc_Aitloadmon_Collect::setLoadLevel($loadLevel);
    }

}